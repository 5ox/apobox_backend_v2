<?php
App::uses('Hash', 'Utility');

/**
 * This class contains common or shared properties and methods used by other
 * FedEx client libraries.
 */
class FedexCommon {

	/**
	 * FedEx API key
	 *
	 * @var mixed
	 */
	protected $apiKey = null;

	/**
	 * FedEx API password
	 *
	 * @var mixed
	 */
	protected $apiPassword = null;

	/**
	 * FedEx API account number
	 *
	 * @var mixed
	 */
	protected $apiAccount = null;

	/**
	 * FedEx API meter number
	 *
	 * @var mixed
	 */
	protected $apiMeter = null;

	/**
	 * Full path to the base directory containing wsdl definition files
	 *
	 * @var string
	 */
	protected $wsdlPath = APP . 'Plugin' . DS . 'Fedex' . DS . 'wsdl' . DS;

	/**
	 * Full path and file for a specific wsdl file
	 *
	 * @var mixed
	 */
	protected $wsdl = null;

	/**
	 * Default error messages set in child classes
	 *
	 * @var mixed
	 */
	protected $defaultError = 'The request could not be completed.';

	/**
	 * Instantiate a new instance of the common class using the provided credentials.
	 * $config must provide [apiKey], [apiPassword], [apiAccount], and [apiMeter]
	 *
	 * @param array $config API connection credentials (4 factor)
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct(array $config = []) {
		$config = $config + [
			'apiKey' => $this->apiKey,
			'apiPassword' => $this->apiPassword,
			'apiAccount' => $this->apiAccount,
			'apiMeter' => $this->apiMeter,
		];

		if (!$config['apiKey']) {
			throw new BadMethodCallException('Missing required [apiKey] config key.');
		}
		if (!$config['apiPassword']) {
			throw new BadMethodCallException('Missing required [apiPassword] config key.');
		}
		if (!$config['apiAccount']) {
			throw new BadMethodCallException('Missing required [apiAccount] config key.');
		}
		if (!$config['apiMeter']) {
			throw new BadMethodCallException('Missing required [apiMeter] config key.');
		}

		$this->apiKey = $config['apiKey'];
		$this->apiPassword = $config['apiPassword'];
		$this->apiAccount = $config['apiAccount'];
		$this->apiMeter = $config['apiMeter'];
	}

	/**
	 * Merges the two supplied arrays and then adds API auth data. Any existing
	 * auth keys in the supplied $defaults or $data are overwritten.
	 *
	 * @param array $defaults Default data to merge
	 * @param array $data The data to merge with the default data
	 * @return array The merged data combined with required API auth settings
	 */
	protected function prepareRequest(array $defaults, array $data) {
		$data = Hash::merge($defaults, $data);
		$data = $this->setClientAuth($data);
		return $data;
	}

	/**
	 * Adds the required API auth data to the supplied $data array. Any existing
	 * values are overwritten.
	 *
	 * @param array $data The request data to combine with API auth data
	 * @return array The combined auth and request data
	 */
	protected function setClientAuth(array $data) {
		$authData = [
			'WebAuthenticationDetail' => [
				'UserCredential' => [
					'Key' => $this->apiKey,
					'Password' => $this->apiPassword,
				],
			],
			'ClientDetail' => [
				'AccountNumber' => $this->apiAccount,
				'MeterNumber' => $this->apiMeter,
			],
		];
		return $authData + $data;
	}

	/**
	 * Disables the wsdl cache for the Soap client and returns a new SoapClient
	 * object.
	 *
	 * @param string $wsdl The full path to the wsdl file
	 * @param array $options Options to pass to the SoapClient constructor
	 * @return object A new SoapClient
	 */
	protected function initSoapClient($wsdl, array $options = ['trace' => 1]) {
		ini_set('soap.wsdl_cache_enabled', '0');
		return new SoapClient($wsdl, $options);
	}

	/**
	 * Attempts to extract a meaningful error message from the response and throws
	 * an exception with the message. If a message can't be found, the default error
	 * message is used.
	 *
	 * @param object $response The response SoapClient getRates() response
	 * @return void
	 * @throws BadRequestException
	 */
	protected function processError($response) {
		if (is_array($response->Notifications)) {
			$notification = Hash::get($response->Notifications, '0', $this->defaultError);
			if (is_object($notification)) {
				throw new BadRequestException($notification->Message);
			}
			throw new BadRequestException($notification);
		}
		if (is_object($response->Notifications)) {
			throw new BadRequestException($response->Notifications->Message);
		}
		throw new BadRequestException($this->defaultError);
	}
}
