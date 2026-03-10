<?php
App::uses('EwsLabelService', 'Endicia.Lib');
App::uses('Hash', 'Utility');

/**
 * Class: Endicia
 *
 */
class Endicia {

	/**
	 * Default origin ZIP code for USPS rate lookups
	 *
	 * @var string
	 */
	protected $_originZip = '46563';

	/**
	 * The destination to write label images to
	 *
	 * @var string
	 */
	protected $_labelPath = APP . WEBROOT_DIR . DS . 'files';

	/**
	 * Prepares a request array of required fields and queries the Endicia API
	 * to find all available rates based on the order package data.
	 *
	 * @param array $order An order
	 * @return array $rates The available rates for the package
	 */
	public function getRates($order) {
		$EwsLabelService = $this->initEwsLabelService(Configure::read('ShippingApis.Endicia.credentials'));
		$size = $EwsLabelService->calculateSize(
			$order['Order']['depth'],
			$order['Order']['length'],
			$order['Order']['width']
		);

		$data = array(
			'api' => array(
				'requestKey' => 'postageRatesRequestXML',
				'requestEndpoint' => 'CalculatePostageRatesXML',
			),
			'PostageRatesRequest' => array(
				'RequesterID' => 'API',
				'CertifiedIntermediary' => array(
					'AccountID' => 'API',
					'PassPhrase' => 'API',
				),
				'MailClass' => 'Domestic', // @TODO determine this
				'WeightOz' => $order['Order']['weight_oz'],
				'MailPieceShape' => $size, // optional
				'FromPostalCode' => $this->_originZip,
				'FromZIP4' => '1039', // optional
				'ToPostalCode' => $order['Order']['delivery_postcode'],
				// optional
				'MailpieceDimensions' => array(
					'Length' => $order['Order']['length'],
					'Width' => $order['Order']['width'],
					'Height' => $order['Order']['depth'],
				),
			),
		);

		$result = $EwsLabelService->apiRequest($data);
		$rates = $EwsLabelService->filterRates($result, Configure::read('ShippingApis.Endicia.rateClasses'));
		return $this->formatRates($rates);
	}

	/**
	 * Formats Endicia API rates and sorts by rate amount.
	 *
	 * @param array $rates The rates from the API
	 * @return array The rates formatted for the view
	 */
	public function formatRates($rates) {
		$formatted = Hash::map($rates, '{n}', function($data) {
			return array(
				'MailClass' => $data['MailClass'],
				'MailService' => $data['Postage']['MailService'],
				'Rate' => $data['@TotalAmount'],
			);
		});
		return Hash::sort($formatted, '{n}.Rate', 'desc');
	}

	/**
	 * Uses $order data to fetch a label from the Endicia API with on-the-fly
	 * postage calculated. The label is saved as a pdf.
	 *
	 * @param array $order The order
	 * @return mixed The URL for the label image or false on failure
	 */
	public function getLabel($order) {
		$data = array(
			'api' => array(
				'requestKey' => 'labelRequestXML',
				'requestEndpoint' => 'GetPostageLabelXML',
			),
			'LabelRequest' => array(
				'@Test' => 'YES',
				'@LabelType' => 'Domestic',
				'@LabelSubtype' => 'Integrated',
				'@LabelSize' => '4x6c',
				'@ImageFormat' => 'PDF',
				'RequesterID' => 'API',
				'AccountID' => 'API',
				'PassPhrase' => 'API',
				'PartnerTransactionID' => $order['Order']['orders_id'],
				'MailClass' => $this->calculateMailClass($order['Order']['mail_class']),
				'ReferenceID' => $order['Customer']['customers_id'],

				'Stealth' => 'false', // @TODO for testing only, can be removed or `false`
				// 'Pricing' => 'Retail', // @TODO in API but results in "Element not allowed: Pricing"

				'WeightOz' => $order['Order']['weight_oz'],
				'MailPieceShape' => $this->calculateMailShape($order),
				'MailpieceDimensions' => array(
					'Length' => $order['Order']['length'],
					'Width' => $order['Order']['width'],
					'Height' => $order['Order']['depth'],
				),

				// 'Services' => array(
				//  '@InsuredMail' => 'Off',
				//  '@SignatureConfirmation' => 'Off',
				//  // Etc.
				// ),

				'ToName' => $order['Order']['delivery_name'],
				'ToCompany' => $order['Order']['delivery_company'],
				'ToAddress1' => $order['Order']['delivery_street_address'],
				'ToCity' => $order['Order']['delivery_city'],
				'ToState' => $order['Order']['delivery_state'],
				'ToPostalCode' => $order['Order']['delivery_postcode'],
				'ToEmail' => $order['Order']['customers_email_address'],

				'FromName' => 'APO Box',
				'ReturnAddress1' => '1911 Western Ave',
				'FromCity' => 'Plymouth',
				'FromState' => 'IN',
				'FromPostalCode' => $this->_originZip,
				'FromZIP4' => '1039',

				'Value' => $order['Order']['insurance_coverage'],

				'IntegratedFormType' => 'Form2976',
				'CustomsCertify' => 'True',
				'CustomsSigner' => 'Melinda Hauptmann',
				'CustomsSendersCopy' => 'False',
				'CustomsInfo' => array(
					'ContentsType' => 'Merchandise', // Documents, Gift, Merchandise, Other
					'ContentsExplanation' => 'Personal care items intended for personal use', // Required if ContentsType is 'Other'
					'CustomsItems' => array(
						'CustomsItem' => array(
							array(
								'Description' => 'Personal care items intended for personal use',
								'Quantity' => '1',
								'Weight' => '10', // total must be less than `WeightOz`
								'Value' => '5',
							),
							// array(
							// 	'Description' => 'Another thing',
							// 	'Quantity' => '1',
							// 	'Weight' => '5', // total must be less than `WeightOz`
							// 	'Value' => '10',
							// ),
						),
					),
				),

				'ResponseOptions' => array(
					'@PostagePrice' => 'true',
				),
			),
		);

		$EwsLabelService = $this->initEwsLabelService(Configure::read('ShippingApis.Endicia.credentials'));
		$result = $EwsLabelService->apiRequest($data);
		$label = array(
			'rate' => Hash::get($result, 'LabelRequestResponse.FinalPostage'),
			'mail_class' => Hash::get($result, 'LabelRequestResponse.PostagePrice.MailClass'),
			'label' => $EwsLabelService->saveLabelImage($result, $this->_labelPath),
		);
		return $label;
	}

	/**
	 * Sets Endicia `MailClass` to whatever is specifed in the order record.
	 * If no match is found the default mail class is `Priority`.
	 *
	 * @param string $mailClass Order.mail_class
	 * @return string The MailClass in the correct format for the API
	 */
	protected function calculateMailClass($mailClass) {
		switch ($mailClass) {
			case 'PRIORITY':
				$class = 'Priority';
				break;
			case 'PARCEL':
				$class = 'ParcelSelect';
				break;
			default:
				$class = 'Priority';
		}
		return $class;
	}

	/**
	 * Sets Endicia `MailPieceShape`. If a RECTPARCEL the size is checked to
	 * determine if it's a `Parcel` or `LargeParcel`. If no mtach is found the
	 * default is `Parcel`.
	 *
	 * @param array $order The order
	 * @return void
	 */
	protected function calculateMailShape($order) {
		switch ($order['Order']['package_type']) {
			case 'RECTPARCEL':
				$EwsLabelService = $this->initEwsLabelService(
					Configure::read('ShippingApis.Endicia.credentials')
				);
				$mailShape = $EwsLabelService->calculateSize(
					$order['Order']['depth'],
					$order['Order']['length'],
					$order['Order']['width']
				);
				break;
			case 'FLATRATEENVELOPE':
				$mailShape = 'FlatRateEnvelope';
				break;
			default:
				$mailShape = 'Parcel';
		}
		return $mailShape;
	}

	/**
	 * Initialize an instance of the EwsLabelService class.
	 *
	 * @param array $config The config
	 * @return object $EwsLabelService
	 */
	protected function initEwsLabelService($config = array()) {
		return new EwsLabelService($config);
	}
}
