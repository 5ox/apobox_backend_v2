<?php
App::uses('Hash', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');

/**
 * Class: UspsRate
 *
 */
class UspsRate {

	/**
	 * The USPS API user id
	 *
	 * @var string
	 */
	protected $userId = null;

	/**
	 * The USPS API version to use
	 *
	 * @var string
	 */
	protected $apiVersion = 'RateV4';

	/**
	 * The USPS URL to submit API requests to
	 *
	 * @var string
	 */
	protected $postUrl = 'https://production.shippingapis.com/ShippingApi.dll';

	/**
	 * The key to search response arrays for to detect errors
	 *
	 * @var string
	 */
	protected $errorKey = 'Error';

	/**
	 * The key containing error descriptions
	 *
	 * @var string
	 */
	protected $errorDescription = 'Description';

	/**
	 * The array representation of the XML request template
	 *
	 * @var array
	 */
	protected $requestTemplate = array(
		'RateV4Request' => array(
			'@USERID' => '',
			'Revision' => 2,
			'Package' => array(),
		)
	);

	/**
	 * Keys that must be present for a API request to proceed
	 *
	 * @var array
	 */
	protected $requiredKeys = array(
		'Service',
		'ZipOrigination',
		'ZipDestination',
		'Pounds',
		'Ounces',
		'Container',
		'Size',
		'Width',
		'Length',
		'Height',
	);

	/**
	 * The path to extract from the response
	 *
	 * @var string
	 */
	protected $responseRatePath = 'RateV4Response.Package.Postage';

	/**
	 * Instantiate a new instance of the shipping API library using the provided credentials.
	 * $config must provide at least [userId], with [apiVersion]  and [postUrl] optional.
	 *
	 * @param array $config API connection userId and apiVersion.
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct($config = array()) {
		$config = $config + array(
			'userId' => $this->userId,
			'apiVersion' => $this->apiVersion,
			'postUrl' => $this->postUrl,
		);

		if (!$config['userId']) {
			throw new BadMethodCallException('Missing required [userId] config key.');
		}

		$this->userId = $config['userId'];
		$this->apiVersion = $config['apiVersion'];
		$this->postUrl = $config['postUrl'];
	}

	/**
	 * Public method to make the API rate request.
	 *
	 * @param array $data The data to make the request with
	 * @return array $result The API response as an array
	 */
	public function getRates($data) {
		$data = $this->prepareRequest($data);
		$response = $this->makeRequest($data);
		return $response;
	}

	/**
	 * Calculates the girth of a package.
	 *
	 * @param mixed $height The height
	 * @param mixed $width The width
	 * @return float Total girth
	 */
	public function calculateGirth($height, $width) {
		$result = ceil(($height * 2) + ($width * 2));
		return $result;
	}

	/**
	 * Calculates the size of a package based on it's measurements.
	 *
	 * @param mixed $height The height
	 * @param mixed $length The length
	 * @param mixed $width The width
	 * @return string $size Either REGULAR or LARGE
	 */
	public function calculateSize($height, $length, $width) {
		$size = 'Regular';
		if (ceil((float)$height > 12) || ceil((float)$length) > 12 || ceil((float)$width) > 12) {
			$size = 'Large';
		}
		return $size;
	}

	/**
	 * Filters out unwanted rates from the API response. The filter format is
	 * `Descriptive Label => CLASSID`. Example array:
	 *
	 * 'Priority Mail 2-Day' => '1',
	 * 'Standard Post' => '4'
	 *
	 * @param array $rates The rate array from the API response
	 * @param array $classes The classes
	 * @return array $rates The filtered or unfiltered rates
	 */
	public function filterRates(array $rates, $classes = array()) {
		$rates = $this->extractRatesArray($rates);
		if ($classes) {
			$filtered = array();
			foreach ($rates as $rate) {
				if (in_array($rate['@CLASSID'], $classes)) {
					$filtered[] = $rate;
				}
			}
			return $filtered;
		}
		return $rates;
	}

	/**
	 * Removes the +4 part of a ZIP code if present
	 *
	 * @param string $zip The destination ZIP code
	 * @return string The ZIP code with +4 removed or the original ZIP code
	 */
	public function prepareZip($zip) {
		if (preg_match('/^\d{5}.+$/', $zip)) {
			return substr($zip, 0, 5);
		}
		return $zip;
	}

	/**
	 * Extract the rates from the Usps response. Also wrap single rate result
	 * in an array for consistent processing.
	 *
	 * @param array $response The Usps response.
	 * @return array $rates The rate response
	 */
	protected function extractRatesArray(array $response) {
		$rates = Hash::get($response, $this->responseRatePath);
		return empty($rates[0]) ? [$rates] : $rates;
	}

	/**
	 * Converts an array to XML and removes parts not needed.
	 *
	 * @param array $data The array to convert to XML
	 * @return string $result The array converted to xml
	 */
	protected function prepareRequest(array $data) {
		$data = $this->buildDataFromTemplate($data);
		$xmlObject = Xml::fromArray($data, array(
			'format' => 'tags'
		));
		$result = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xmlObject->asXML());
		return trim($result);
	}

	/**
	 * Checks to make sure all required request keys are present, and builds
	 * the array in preparation for transformation into XML.
	 *
	 * @param array $data The data
	 * @return array $template The XML request data as an array
	 * @throws BadMethodCallException
	 */
	protected function buildDataFromTemplate(array $data) {
		foreach ($this->requiredKeys as $key) {
			if (!array_key_exists($key, $data)) {
				throw new BadMethodCallException('Required keys for an API request are missing.');
			}
		}
		$template = $this->requestTemplate;
		$template['RateV4Request']['@USERID'] = $this->userId;
		$template['RateV4Request']['Package'] = $data;
		$template['RateV4Request']['Package']['@ID'] = '0';
		return $template;
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
			'API' => $this->apiVersion,
			'XML' => $data,
		);

		try {
			$response = $HttpSocket->post($this->postUrl, $postData);
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
		return $this->checkResponse($this->errorKey, $result);
	}

	/**
	 * Performs a multi-dimensional array key search for $this->errorKey and
	 * throws an exception with the value of the corresponding $this->errorDescription
	 * key if found or the unmodified $response if no error key exists.
	 *
	 * @param string $errorKey The errorKey to search for
	 * @param array $response The response data
	 * @return The $response The unmodifed response input
	 * @throws BadRequestException
	 */
	protected function checkResponse($errorKey, array $response) {
		$result = array_key_exists($errorKey, $response);
		if ($result) {
			throw new BadRequestException($response[$errorKey][$this->errorDescription]);
		}
		foreach ($response as $k => $v) {
			if (is_array($v)) {
				$result = $this->checkResponse($errorKey, $v);
			}
		}
		return $response;
	}
}
