<?php
App::uses('File', 'Utility');
App::uses('Hash', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');

/**
 * Class: EwsLabelService
 *
 */
class EwsLabelService {

	/**
	 * Requester ID (also called Partner ID), 4 character string assigned by
	 * Endicia.
	 *
	 * @var string
	 */
	protected $requesterId = null;

	/**
	 * Account ID for the Endicia account.
	 *
	 * @var string
	 */
	protected $accountId = null;

	/**
	 * Password for the API account.
	 *
	 * @var string
	 */
	protected $password = null;

	/**
	 * Determines API mode - either 'live' or 'test'
	 *
	 * @var string
	 */
	protected $mode = null;

	/**
	 * The TEST/SANDBOX web service URL.
	 *
	 * @var string
	 */
	protected $sandboxUrl = 'https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx';

	/**
	 * The LIVE/PRODUCTION web service URL.
	 *
	 * @var string
	 */
	protected $productionUrl = 'https://labelserver.endicia.com/LabelService/EwsLabelService.asmx';

	/**
	 * Either $sandboxUrl or $productionUrl, determined by $mode.
	 *
	 * @var mixed
	 */
	protected $postUrl = null;

	/**
	 * Request $data keys to populate with constuctor API credentials.
	 * classVar => API var
	 *
	 * @var array
	 */
	protected $apiCredentialKeys = array(
		'requesterId' => 'RequesterID',
		'accountId' => 'AccountID',
		'password' => 'PassPhrase',
	);

	/**
	 * Array keys that must exist in passed $data for an API call to proceed.
	 *
	 * @var array
	 */
	protected $apiRequiredDataKeys = array(
		'requestKey',
		'requestEndpoint',
	);

	/**
	 * The path to extract from the response when requesting rates
	 *
	 * @var string
	 */
	protected $responseRatePath = 'PostageRatesResponse.PostagePrice';

	/**
	 * Instantiate a new instance of the Endicia API library using the provided credentials.
	 * $config must provide at least [requesterId], [accountId] and [password].
	 *
	 * @param array $config API credentials and settings
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct($config = array()) {
		$allowedModes = array('live', 'test');
		$config = $config + array(
			'requesterId' => $this->requesterId,
			'accountId' => $this->accountId,
			'password' => $this->password,
			'mode' => 'test',
		);

		if (!$config['requesterId']) {
			throw new BadMethodCallException('Missing required [requesterId] config key.');
		}
		if (!$config['accountId']) {
			throw new BadMethodCallException('Missing required [accountId] config key.');
		}
		if (!$config['password']) {
			throw new BadMethodCallException('Missing required [password] config key.');
		}

		$this->requesterId = $config['requesterId'];
		$this->accountId = $config['accountId'];
		$this->password = $config['password'];
		$this->mode = (in_array($config['mode'], $allowedModes) ? $config['mode'] : 'test');
		$this->postUrl = ($this->mode == 'test' ? $this->sandboxUrl : $this->productionUrl);
	}

	/**
	 * Public method to make the API request. Passed $data is validated for
	 * required keys and exceptions are thrown if any are missing.
	 *
	 * @param array $data The data to make the request with
	 * @return array $result The API response as an array
	 * @throws BadMethodCallException
	 */
	public function apiRequest($data) {
		if (!isset($data['api']) || count(array_keys($data)) < 2) {
			throw new BadMethodCallException(
				'Missing required [api] or [api method] data keys.'
			);
		}
		if (array_keys($data['api']) !== $this->apiRequiredDataKeys) {
			throw new BadMethodCallException(
				'Missing required [api.requestKey] or [api.requestEndpoint] data keys.'
			);
		}

		$data = $this->prepareRequest($data);
		$response = $this->makeRequest($data);
		return $response;
	}

	/**
	 * Extracts and decodes a label image and saves it to a file.
	 *
	 * @param array $response A LabelRequestResponse array
	 * @param string $path The path to save the label to
	 * @param string $ext The extension to use for the label file
	 * @return string Filename of the label file if written
	 * @throws BadMethodCallException
	 */
	public function saveLabelImage($response, $path = null, $ext = 'pdf') {
		if ($path && is_writable($path)) {
			$label = Hash::get(
				$response,
				'LabelRequestResponse.Base64LabelImage',
				Hash::get($response, 'LabelRequestResponse.Label.Image.@')
			);
			if ($label && $label = $this->decode($label)) {
				$tracking = Hash::get($response, 'LabelRequestResponse.TrackingNumber');
				if ($this->writeFile($path . DS . $tracking . '.' . $ext, $label, true, 0644)) {
					return $tracking . '.' . $ext;
				}
			} else {
				throw new BadMethodCallException('The label image is missing or could not be decoded.');
			}
		}
		throw new BadMethodCallException('The label path is not writable.');
	}

	/**
	 * Calculates the size of a package based on it's measurements.
	 *
	 * @param mixed $height The height
	 * @param mixed $length The length
	 * @param mixed $width The width
	 * @return string $size Either Parcel or LargeParcel
	 */
	public function calculateSize($height, $length, $width) {
		$size = 'Parcel';
		if (ceil((float)$height > 12) || ceil((float)$length) > 12 || ceil((float)$width) > 12) {
			$size = 'LargeParcel';
		}
		return $size;
	}

	/**
	 * Filters out unwanted rates from the API response. The filter format is
	 * an array of mail classes to display. Example array:
	 *
	 * 'Priority',
	 * 'PriorityExpress',
	 *
	 * @param array $rates The rate array from the API response
	 * @param array $classes The classes
	 * @return array $rates The filtered or unfiltered rates
	 */
	public function filterRates(array $rates, $classes = array()) {
		$rates = Hash::get($rates, $this->responseRatePath);
		if ($classes) {
			$filtered = array();
			foreach ($rates as $rate) {
				if (in_array($rate['MailClass'], $classes)) {
					$filtered[] = $rate;
				}
			}
			return $filtered;
		}
		return $rates;
	}

	/**
	 * Writes a file to disk.
	 *
	 * @param string $path The path to save the file to
	 * @param string $data The file data to write
	 * @param bool $create True to create a new file
	 * @param int $mode The file permission mode
	 * @return bool True if the file was written, False on failure.
	 */
	protected function writeFile($path, $data, $create = false, $mode = 755) {
		$file = new File($path, $create, $mode, $data);
		return $file->write($data);
	}

	/**
	 * Converts an array to XML
	 *
	 * @param array $data The array to convert to XML
	 * @return string $result The array converted to xml
	 */
	protected function prepareRequest(array $data) {
		$result['api'] = $data['api'];
		unset($data['api']);

		$data = $this->setCredentials($this->apiCredentialKeys, $data);
		$xmlObject = Xml::fromArray($data, array(
			'format' => 'tags'
		));
		$result['xml'] = trim($xmlObject->asXML());

		return $result;
	}

	/**
	 * Replaces empty/placeholder credentials (multi-dimmensionally) with real
	 * values set in the constructor.
	 *
	 * @param array $apiCredentialKeys The apiCredentialKeys
	 * @param array $data Input data
	 * @return array The original $data array with credential values replaced
	 */
	protected function setCredentials($apiCredentialKeys, $data) {
		$flat = Hash::flatten($data);
		foreach ($flat as $k => $v) {
			foreach ($apiCredentialKeys as $local => $remote) {
				if (strpos($k, $remote) !== false) {
					$flat[$k] = $this->{$local};
				}
			}
		}
		return Hash::expand($flat);
	}

	/**
	 * Makes a POST request to the API with formatted XML.
	 *
	 * @param string $data The XML to use for the request
	 * @return array $result The processed response object body as an array.
	 * @throws NotFoundException
	 */
	protected function makeRequest($data) {
		$HttpSocket = $this->initHttpSocket();
		$postData = array(
			$data['api']['requestKey'] => $data['xml'],
		);

		try {
			$response = $HttpSocket->post($this->postUrl . '/' . $data['api']['requestEndpoint'], $postData);
		} catch (Exception $e) {
			throw new NotFoundException('Missing or invalid response from the API server.');
		}
		return $this->processResponse($response->body());
	}

	/**
	 * Initialize an HttpSocket
	 *
	 * @return object An HttpSocket instance
	 */
	protected function initHttpSocket() {
		return new HttpSocket();
	}

	/**
	 * Converts the HttpSocket response body XML to an array.
	 *
	 * @param string $response An HttpSocket response object.
	 * @return array $result The response object body as an array.
	 * @throws BadRequestException
	 */
	protected function processResponse($response) {
		try {
			$result = Xml::toArray(Xml::build($response));
		} catch (Exception $e) {
			throw new BadRequestException('The API response is not valid.');
		}
		return $this->checkResponse($result);
	}

	/**
	 * Checks the responses `Status` key and throw an exception if an error
	 * was returned from the API. If no error, the data is unmodified.
	 *
	 * @param array $response The response data
	 * @return The $response The unmodifed response input
	 * @throws BadRequestException
	 */
	protected function checkResponse(array $response) {
		$status = Hash::extract($response, '{s}.Status');
		if (!isset($status[0]) || $status[0] !== '0') {
			$error = Hash::extract($response, '{s}.ErrorMessage');
			$error = isset($error[0]) ? $error[0] : 'Unknown API Error';
			throw new BadRequestException($error);
		}
		return $response;
	}

	/**
	 * A wrapper for base64_decode with the option to add additional decoding
	 * methods in the future.
	 *
	 * @param string $data The data to decode
	 * @param string $type The type of decoding to perform
	 * @return The decoded string or the unmodified string
	 */
	protected function decode($data, $type = 'base64') {
		if ($type == 'base64') {
			return base64_decode($data, true);
		}
		return $data;
	}
}
