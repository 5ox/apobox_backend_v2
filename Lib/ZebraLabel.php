<?php
use Zebra\Client;
use Zebra\Zpl\Builder;
use Zebra\Zpl\Image;

/**
 * Class: ZebraLabel
 *
 */
class ZebraLabel {

	/**
	 * The client to print to
	 *
	 * @var mixed
	 */
	protected $client = null;

	/**
	 * The full path with trailing slash to directory where $font is.
	 *
	 * @var string
	 */
	protected $fontPath = WWW_ROOT . 'fonts/';

	/**
	 * The font to use for the label image.
	 *
	 * @var string
	 */
	protected $font = 'DejaVuSans.ttf';

	/**
	 * Instantiate a new instance of the ZebraLabel library. $config must provide
	 * at least [method] key. If [method] is 'network', then a [client] key must
	 * also be provided.
	 *
	 * @param array $config Optional configuration data
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct($config = []) {
		if (!isset($config['method']) || !$config['method'] || $config['method'] == null) {
			throw new BadMethodCallException('Missing required [method] config key.');
		}
		if ($config['method'] == 'network') {
			if (!isset($config['client']) || !$config['client'] || $config['client'] == null) {
				throw new BadMethodCallException('Missing required [client] config key.');
			}
			$this->client = $config['client'];
		}
		$this->method = $config['method'];
	}

	/**
	 * Converts the supplied $data to a png, and then, depending on configuration,
	 * converts the png to a ZPL print job.
	 *
	 * @param string $data The label data
	 * @param bool $asImage If true, the label will be returned as a png
	 * @return mixed The label, in raw or png, or bool for network printing
	 * @throws BadMethodCallException
	 */
	public function printLabel($data, $asImage = false) {
		if (!isset($data['header']) || !isset($data['body']) || !isset($data['footer'])) {
			throw new BadMethodCallException('Missing one or more required data keys');
		}
		$image = $this->generatePngImage($data);
		if ($asImage) {
			return $image;
		}
		$zpl = $this->generateLabel($image);
		if ($this->method == 'network') {
			$client = $this->initClient($this->client);
			return $client->send($zpl);
		}

		return (string)$zpl;
	}

	/**
	 * Converts a png image into a ZPL print job
	 *
	 * @param string $image The data png image data
	 * @return object $zpl the ZPL print job
	 */
	protected function generateLabel($image) {
		$img = $this->initImage($image);
		$zpl = $this->initBuilder();
		$zpl->fo(50, 50);
		$zpl->gf($img);
		$zpl->fs();
		return $zpl;
	}

	/**
	 * Creates a label shaped image containing supplied $data.
	 *
	 * @param string $data The label data
	 * @return string $label The label captured by the output buffer
	 */
	protected function generatePngImage($data) {
		$im = imagecreatetruecolor(813, 1217);

		$white = imagecolorallocate($im, 255, 255, 255);
		$black = imagecolorallocate($im, 0, 0, 0);
		$font = $this->fontPath . $this->font;

		imagefilledrectangle($im, 0, 0, 812, 1216, $white);

		foreach ($data as $section => $content) {
			switch($section) {
				case 'body':
					$y = 80;
					break;
				case 'footer':
					$y = 470;
					break;
				default:
					$y = 60;
			}
			imagettftext($im, $content['size'], 0, 30, $y, $black, $font, $content['content']);
		}
		imageline($im, 30, 500, 780, 500, $black);

		ob_start();
		imagepng($im);
		$label = ob_get_contents();
		ob_end_clean();
		imagedestroy($im);

		return $label;
	}

	/**
	 * Initialize an instance of Zebra\Zpl\Image
	 *
	 * @param string $data The image data
	 * @return object $Image
	 */
	protected function initImage($data) {
		return new Image($data);
	}

	/**
	 * Initialize an instance of Zebra\Zpl\Builder
	 *
	 * @return object $Builder
	 */
	protected function initBuilder() {
		return new Builder();
	}

	/**
	 * Initialize an instance of Zebra\Client
	 *
	 * @param string $client The client to print to
	 * @return object $Client
	 */
	protected function initClient($client) {
		return new Client($client);
	}
}
