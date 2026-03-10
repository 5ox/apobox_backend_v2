<?php
App::uses('FedexCommon', 'Fedex.Lib');
App::uses('Hash', 'Utility');

/**
 * Class: FedexLabel
 *
 */
class FedexLabel extends FedexCommon {

	/**
	 * The API version to use for this ShipService endpoint. A corresponding
	 * wsdl file must exist at the same version.
	 *
	 * @var int
	 */
	protected $apiVersion = 17;

	/**
	 * The endpoint and wsdl file path fragment, appended to $this->wsdlPath
	 * set in the FedexCommon lib.
	 *
	 * @var string
	 */
	protected $wsdlFile = 'ShipService' . DS . '%sShipService_v%d.wsdl';

	/**
	 * Error to return if a meaningful message cannot be found.
	 *
	 * @var string
	 */
	protected $defaultError = 'Label request could not be completed.';

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
	 * the label request. If successful, the response is processed and returned as
	 * a bool (when writing a file) or string (zpl data). On failure, an exception
	 * is thrown with a corresponding error message if possible.
	 *
	 * @param array $data The request data to prepare and use for the label request
	 * @return mixed bool result of writing the label file or raw zpl data
	 * @throws BadRequestException
	 */
	public function labelRequest($data) {
		$defaults = [
			'TransactionDetail' => [
				'CustomerTransactionId' => 'Client label request',
			],
			'Version' => [
				'ServiceId' => 'ship',
				'Major' => $this->apiVersion,
				'Intermediate' => '0',
				'Minor' => '0'
			],
			'RequestedShipment' => [
				'ShippingChargesPayment' => [
					'Payor' => [
						'ResponsibleParty' => [
							'AccountNumber' => $this->apiAccount,
						]
					]
				],
				'LabelSpecification' => [
					'LabelFormatType' => 'COMMON2D',
					'ImageType' => 'ZPLII',
					'LabelStockType' => 'STOCK_4X6',
				],
			],
		];

		$request = $this->prepareRequest($defaults, $data);
		$client = $this->initSoapClient($this->wsdl);

		try {
			$response = $client->processShipment($request);
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
	 * Extracts the label data and either writes it to a file (png or pdf) or
	 * returns the label data (zpl).
	 *
	 * @param object $response A successful response from the SoapClient processShipment() method
	 * @return mixed If label type is a file (png or pdf) the result of file_put_contents.
	 * If label type is ZPL, the raw ZPL string. If no valid type bool false is returned.
	 */
	protected function processResponse($response) {
		$label = $response->CompletedShipmentDetail->CompletedPackageDetails->Label;
		if ($label->ImageType == 'PNG' || $label->ImageType == 'PDF') {
			$fileName = 'fedex-' . date('m-d-Y_his') . '.' . strtolower($label->ImageType);
			return $this->writeFile(TMP . $fileName, $label->Parts->Image);
		}
		if ($label->ImageType == 'ZPLII') {
			return $label->Parts->Image;
		}
		return false;
	}

	/**
	 * Writes a file to disk
	 *
	 * @param mixed $file The full path and filename to write
	 * @param mixed $contents The contents to put in the file
	 * @return bool True if the file if written or false on failure
	 * @codeCoverageIgnore Don't test PHP's file_put_contents() function
	 */
	protected function writeFile($file, $contents) {
		return (bool)file_put_contents($file, $contents);
	}
}
