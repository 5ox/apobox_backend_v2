<?php
App::uses('FedexRate', 'Fedex.Lib');
App::uses('FedexLabel', 'Fedex.Lib');
App::uses('Hash', 'Utility');

/**
 * Class: Fedex
 *
 */
class Fedex {

	/**
	 * Sets required keys and values for an API rate request and uses FedexRate
	 * to make the request and process the response.
	 *
	 * @param array $order A order data array
	 * @return array The rate and mail service level data
	 */
	public function getRate($order) {
		$data = [
			'RequestedShipment' => []
		];
		$data = $this->addCommonData($data, $order);
		$rate = $this->initFedex('Rate');
		return $rate->rateRequest($data);
	}

	/**
	 * Adds the `Shipper`, `Recipient` and package data to the request array
	 *
	 * @param array $data The request data array to be built
	 * @param mixed $order An order data array
	 * @return array The merged order data and common data
	 */
	protected function addCommonData($data, $order) {
		$common = [
			'RequestedShipment' => [
				'RequestedPackageLineItems' => [
					'SequenceNumber' => 1,
					'GroupPackageCount' => 1,
					'Weight' => [
						'Units' => 'LB',
						'Value' => $this->calculateWeight($order['Order']['weight_oz']),
					],
					'Dimensions' => [
						'Units' => 'IN',
						'Length' => $order['Order']['length'],
						'Width' => $order['Order']['width'],
						'Height' => $order['Order']['depth'],
					],
				],
				'Shipper' => Configure::read('ShippingApis.Fedex.shipper'),
				'Recipient' => [
					'Contact' => [
						'PersonName' => $order['Order']['delivery_name'],
						'CompanyName' => $order['Order']['delivery_company'],
						'PhoneNumber' => !empty($order['Order']['customers_telephone']) ? $order['Order']['customers_telephone'] : Configure::read('ShippingApis.Fedex.shipper.Contact.PhoneNumber'),
					],
					'Address' => [
						'StreetLines' => [
							$order['Order']['delivery_street_address'],
							$order['Order']['delivery_suburb'],
						],
						'City' => $order['Order']['delivery_city'],
						'StateOrProvinceCode' => $order['Order']['delivery_state'],
						'PostalCode' => $order['Order']['delivery_postcode'],
						'CountryCode' => $this->getCountryCode($order['Order']['delivery_country']),
					],
				],
				'DropoffType' => 'REGULAR_PICKUP',
				'ShipTimestamp' => date('c'),
				'ServiceType' => 'FEDEX_GROUND',
				'PackagingType' => 'YOUR_PACKAGING',
				'PackageCount' => '1',
				'ShippingChargesPayment' => [
					'PaymentType' => 'SENDER',
					'Payor' => [
						'ResponsibleParty' => [
							'CountryCode' => 'US'
						]
					]
				],
			],
		];
		return Hash::merge($common, $data);
	}

	/**
	 * Converts a country name to a two character code. 'United States' => 'US'
	 *
	 * @param string $country A country name from Country.countries_name
	 * @return mixed A two character country code if found or null if not
	 */
	protected function getCountryCode($country) {
		$country = Classregistry::init('Country')->findByCountriesName($country);
		return Hash::get($country, 'Country.countries_iso_code_2');
	}

	/**
	 * Calculates the weight of a package in pounds and ounces.
	 *
	 * @param string $ounces Weight in ounces
	 * @return string Weight in pounds and ounces
	 */
	protected function calculateWeight($ounces) {
		$result = array();
		if ($ounces < 16) {
			$result['pounds'] = 0;
			$result['ounces'] = ceil($ounces);
		} else {
			$pounds = $ounces / 16;
			$ounces = fmod($pounds, 1) * 16;
			$result['pounds'] = floor($pounds);
			$result['ounces'] = ceil($ounces);
		}
		return $result['pounds'] . '.' . $result['ounces'];
	}

	/**
	 * Returns a new FedexRate or FedexLabel object initialized with the required auth
	 * configuration.
	 *
	 * @param string $type Either `Rate` or `Label`
	 * @return object A new FedexRate or FedexLabel object
	 */
	protected function initFedex($type) {
		$class = 'Fedex' . $type;
		return new $class(Configure::read('ShippingApis.Fedex.auth'));
	}

	/**
	 * Sets required keys and values for an API label request and uses FedexLabel
	 * to make the request and process the response. Default values can be overridden
	 * by adding to the $data array.
	 *
	 * @param array $order An order data array
	 * @return mixed bool result of writing the label file or raw zpl data
	 */
	public function printLabel($order) {
		$data = [
			'RequestedShipment' => [
				'LabelSpecification' => [
					'ImageType' => Configure::read('ShippingApis.Fedex.label.type'),
				],
			],
		];
		$data = $this->addCommonData($data, $order);
		$label = $this->initFedex('Label');
		return $label->labelRequest($data);
	}
}
