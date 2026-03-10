<?php
App::uses('FedexCommon', 'Fedex.Lib');
App::uses('Hash', 'Utility');
App::uses('Inflector', 'Utility');

/**
 * Class: FedexRate
 *
 */
class FedexRate extends FedexCommon {

	/**
	 * The API version to use for this RateService endpoint. A corresponding
	 * wsdl file must exist at the same version.
	 *
	 * @var int
	 */
	protected $apiVersion = 18;

	/**
	 * The endpoint and wsdl file path fragment, appended to $this->wsdlPath
	 * set in the FedexCommon lib.
	 *
	 * @var string
	 */
	protected $wsdlFile = 'RateService' . DS . '%sRateService_v%d.wsdl';

	/**
	 * Error to return if a meaningful message cannot be found.
	 *
	 * @var string
	 */
	protected $defaultError = 'Rate request could not be completed.';

	/**
	 * Calls the parent (FedexCommon) constructor to make sure required API auth
	 * values are set, and then checks to make sure the required wsdl file exists.
	 *
	 * @param array $config API connection credentials (4 factor) for parent
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct(array $config = []) {
		parent::__construct($config);
		$prefix = (Configure::read('debug') > 0) ? 'test_' : '';
		$wsdlFile = sprintf($this->wsdlFile, $prefix, $this->apiVersion);
		$this->wsdl = $this->wsdlPath . $wsdlFile;

		if (!is_file($this->wsdl)) {
			throw new BadMethodCallException('Missing required wsdl definition file.');
		}
	}

	/**
	 * Does final preparation of the request data and uses Soap client to make
	 * the rate request. If successful, the response is processed and returned as
	 * an array. On failure, an exception is thrown with a corresponding error
	 * message if possible.
	 *
	 * @param array $data The request data
	 * @return array The processed rate data
	 * @throws BadRequestException
	 */
	public function rateRequest($data) {
		$defaults = [
			'TransactionDetail' => [
				'CustomerTransactionId' => 'Client rate request',
			],
			'Version' => [
				'ServiceId' => 'crs',
				'Major' => $this->apiVersion,
				'Intermediate' => '0',
				'Minor' => '0'
			],
			'ReturnTransitAndCommit' => true,
			'RequestedShipment' => [
				'ShippingChargesPayment' => [
					'Payor' => [
						'ResponsibleParty' => [
							'AccountNumber' => $this->apiAccount,
						]
					]
				],
			],
		];

		$request = $this->prepareRequest($defaults, $data);
		$client = $this->initSoapClient($this->wsdl);

		try {
			$response = $client->getRates($request);
			if ($response->HighestSeverity == 'SUCCESS') {
				return $this->processResponse($response);
			} else {
				return $this->processError($response);
			}
		} catch (SoapFault $e) {
			throw new BadRequestException($e->getMessage());
		}
	}

	/**
	 * Extracts the required rate data from the response object and places it in
	 * an array.
	 *
	 * @param object $response A successful response from the SoapClient getRates() method
	 * @return array Extracted values from the response
	 */
	protected function processResponse($response) {
		$rateData = [[
			'@CLASSID' => 'FedEx',
			'MailService' => Inflector::humanize($response->RateReplyDetails->ServiceType),
			'Rate' => $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount,
		]];
		return $rateData;
	}
}
