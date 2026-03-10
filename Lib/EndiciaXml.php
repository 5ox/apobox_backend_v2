<?php
App::uses('Configure', 'Core');
App::uses('Xml', 'Utility');

/**
 * Class: EndiciaXml
 *
 */
class EndiciaXml {

	/**
	 * layout
	 *
	 * @var string
	 */
	protected $layout = 'C:\Users\Public\Documents\Endicia\DAZzle\APO FPO Small 6x4.lyt';

	/**
	 * start
	 *
	 * @var string
	 */
	protected $start = 'Printing';

	/**
	 * prompt
	 *
	 * @var string
	 */
	protected $prompt = 'No';

	/**
	 * autoClose
	 *
	 * @var string
	 */
	protected $autoClose = 'Yes';

	/**
	 * test
	 *
	 * @var string
	 */
	protected $test = 'No';

	/**
	 * partnerId
	 *
	 * @var string
	 */
	protected $partnerId = 'ppro';

	/**
	 * customsSigner
	 *
	 * @var string
	 */
	protected $customsSigner = '';

	/**
	 * outputFile
	 *
	 * @var string
	 */
	protected $outputFile = 'C:\Users\apobox\AppData\Roaming\Endicia\Professional\Profiles\Profile 001\Post-Back Records\\';

	/**
	 * accountNumber
	 *
	 * @var mixed
	 */
	protected $accountNumber = null;

	/**
	 * Instantiate a new instance of the library. If the configure variable
	 * `ShippingApis.Endicia.accountNumber` is not set an exception is thrown.
	 *
	 * @return void
	 * @throws BadMethodCallException
	 */
	public function __construct() {
		$accountNumber = Configure::read('ShippingApis.Endicia.accountNumber');
		if (!$accountNumber) {
			throw new BadMethodCallException('Missing required [ShippingApis.Endicia.accountNumber] config key.');
		}
		$this->accountNumber = $accountNumber;
		$this->customsSigner = Configure::read('ShippingApis.Endicia.customsSigner');
	}

	/**
	 * Transforms an order record into a DAZzle XML file.
	 *
	 * @param mixed $order The order
	 * @return array The XML and the filename
	 */
	public function createXml($order) {
		$template = $this->buildDataFromTemplate($order);
		$xml = $this->convertToXml($template);
		return array(
			'xml' => $xml,
			'filename' => $this->getFilename($order['Order']['orders_id'])
		);
	}

	/**
	 * Creates an array using order data in preparation for conversion to XML.
	 *
	 * @param array $order An order record
	 * @return array $data The structured array
	 */
	protected function buildDataFromTemplate($order) {
		$data = array(
			'DAZzle' => array(
				'@Layout' => $this->layout,
				'@Start' => $this->start,
				'@Prompt' => $this->prompt,
				'@AutoClose' => $this->autoClose,
				'@Test' => $this->test,
				'@PartnerID' => $this->partnerId,
				'@OutputFile' => $this->outputFile . $this->getFilename($order['Order']['orders_id']),
				'Package' => array(
					'AccountNumber' => $this->accountNumber,
					'MailClass' => $this->setMailClass($order['Order']['mail_class']),
					'PackageType' => $order['Order']['package_type'],
					'Width' => $order['Order']['width'],
					'Length' => $order['Order']['length'],
					'Depth' => $order['Order']['depth'],
					'WeightOz' => $order['Order']['weight_oz'],
					'DateAdvance' => 0,
					'BalloonRate' => !$order['Order']['BalloonRate'] ? 'False' : $order['Order']['BalloonRate'],
					'NonMachinable' => !$order['Order']['NonMachinable'] ? 'False' : $order['Order']['NonMachinable'],
					'OversizeRate' => !$order['Order']['OversizeRate'] ? 'False' : $order['Order']['OversizeRate'],
					'PriorityMailExpressDeliveryOptions' => array(
						'@SignatureRequired' => 'False',
						'@SaturdayDelivery' => 'False',
						'@SundayDelivery' => 'False',
						'@HolidayDelivery' => 'False',
						'@PriorityMailExpress1030' => 'False',
					),
					'Stealth' => 'True',
					'ReplyPostage' => 'False',
					'PrintScanBasedReturnLabel' => 'False',
					'Services' => array(
						'@USPSTracking' => 'OFF',
						'@SignatureConfirmation' => 'OFF',
						'@RegisteredMail' => 'OFF',
						'@CertifiedMail' => 'OFF',
						'@ReturnReceipt' => 'OFF',
						'@AdultSignatureRequired' => 'OFF',
						'@AdultSignatureRestricted' => 'OFF',
						'@RestrictedDelivery' => 'OFF',
						'@COD' => 'OFF',
						'@InsuredMail' => 'OFF',
					),
					'ReferenceID' => $order['Customer']['billing_id'],
					'ToName' => $order['Order']['delivery_name'],
					'ToCompany' => $order['Order']['delivery_company'],
					'ToAddress1' => $order['Order']['delivery_street_address'],
					'ToAddress2' => $order['Order']['delivery_suburb'],
					'ToCity' => $order['Order']['delivery_city'],
					'ToState' => $order['Order']['delivery_state'],
					'ToPostalCode' => $order['Order']['delivery_postcode'],
					'ToCountry' => $order['Order']['delivery_country'],
					'ToEmail' => $order['Order']['customers_email_address'],

					'Value' => $this->orderValue($order),

					//'CustomsFormType' => 'Form2976',
					'ContentsType' => 'Merchandise',
					'AesItnExemption' => '',
					'LicenseNo' => '',
					'CertificateNo' => '',
					'InvoiceNo' => '',
					'SendersCustomsReference' => '',
					'ImportersCustomsReference' => '',
					'Comments' => '',

					'CustomsQuantity1' => '1',
					'CustomsDescription1' => $this->customsDescription($order),
					'CustomsWeight1' => $order['Order']['weight_oz'],
					'CustomsValue1' => $this->orderValue($order),
					'CustomsCountry1' => 'US',

					'CustomsSendersCopy' => 'False',
					'CustomsCertify' => 'True',
					'CustomsSigner' => $this->customsSigner,

					'International' => array(
						'@IfNonDeliverable' => '',
						'@Address1' => '',
						'@Address2' => '',
						'@Address3' => '',
						'@Address4' => '',
					),
					'RubberStamp1' => '',
					'RubberStamp2' => '',
					'RubberStamp3' => '',
					'RubberStamp4' => '',
					'RubberStamp5' => '',
					'RubberStamp6' => '',
					'RubberStamp7' => '',
					'RubberStamp8' => '',
					'RubberStamp9' => '',
					'RubberStamp10' => '',
					'RubberStamp90' => '0.00',
				),
			),
		);
		return $data;
	}

	/**
	 * Returns a customs description from the order or the default.
	 *
	 * @param array $order An order record
	 * @return string
	 */
	protected function customsDescription($order) {
		return !empty($order['Order']['customs_description'])
			? $order['Order']['customs_description']
			: Configure::read('Orders.defaultCustomsDescription');
	}

	/**
	 * Return a minimum value for the order if the order value is 0.
	 *
	 * @param array $order An order record
	 * @return string
	 */
	protected function orderValue($order) {
		return $order['Order']['insurance_coverage'] === '0.00'
			? Configure::read('Orders.minimumLabelValue')
			: $order['Order']['insurance_coverage'];
	}

	/**
	 * Converts an array to XML
	 *
	 * @param array $data The data to convert to XML
	 * @return string The XML
	 */
	protected function convertToXml($data) {
		$xmlObject = Xml::fromArray($data, array(
			'format' => 'tags'
		));
		return $xmlObject->asXml();
	}

	/**
	 * Sets the filename used in `OutputFile` and download.
	 *
	 * @param string $orderId The order id
	 * @return string The filename
	 */
	protected function getFileName($orderId) {
		return date('Y-m-d') . '_' . $orderId . '.xml';
	}

	/**
	 * Sets `PARCEL` from an order record's `mail_class` to `PARCELSELECT'
	 * for Dazzle.
	 *
	 * @param string $mailClass The `mail_class` from an order record
	 * @return string The modified mail class if `PARCEL` or unmodified
	 */
	protected function setMailClass($mailClass) {
		if ($mailClass == 'PARCEL') {
			$mailClass = 'PARCELSELECT';
		}
		return $mailClass;
	}
}
