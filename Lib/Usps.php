<?php
App::uses('UspsRate', 'Usps.Lib');

/**
 * Class: Usps
 *
 */
class Usps {

	/**
	 * Default origin ZIP code for USPS rate lookups
	 *
	 * @var string
	 */
	protected $_originZip;

	/**
	 * Constructor
	 *
	 * - Set default origin zip code from configuration.
	 */
	public function __construct() {
		$this->_originZip = Configure::read('ShippingApis.originZip');
	}

	/**
	 * Prepares a request array of required fields and queries the USPS API
	 * to find all available rates based on the order package data.
	 *
	 * @param array $order An order
	 * @return array $rates The available rates for the package
	 */
	public function getRates($order) {
		$UspsRate = $this->initUspsRate(array('userId' => Configure::read('ShippingApis.Usps.userId')));
		$size = $UspsRate->calculateSize(
			$order['Order']['depth'],
			$order['Order']['length'],
			$order['Order']['width']
		);

		$weight = $this->calculateWeight($order['Order']['weight_oz']);
		$container = $this->calculateContainer($order['Order']['package_type'], $size);
		$destinationZip = $UspsRate->prepareZip($order['Order']['delivery_postcode']);

		$data = array(
			'Service' => 'All',
			'ZipOrigination' => $this->_originZip,
			'ZipDestination' => $destinationZip,
			'Pounds' => $weight['pounds'],
			'Ounces' => $weight['ounces'],
			'Container' => $container,
			'Size' => $size,
			'Width' => $order['Order']['width'],
			'Length' => $order['Order']['length'],
			'Height' => $order['Order']['depth'],
			'Machinable' => 'false',
		);

		$result = $UspsRate->getRates($data);
		$rates = $UspsRate->filterRates($result, Configure::read('ShippingApis.Usps.rateClasses'));
		return $rates;
	}

	/**
	 * Calculates the weight of a package in pounds and ounces.
	 *
	 * @param string $ounces Weight in ounces
	 * @return array Weight in pounds and ounces
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
		return $result;
	}

	/**
	 * Finds the container size of a package or an empty string if not applicable.
	 *
	 * @param string $package The package type
	 * @param string $size The package size
	 * @return string $container The calculated container or an empty string
	 */
	protected function calculateContainer($package, $size) {
		if ($size == 'Large') {
			$container = $package == 'FLATRATEENVELOPE' ? 'Variable Flat Rate Envelope' : 'Rectangular';
		} else {
			$container = '';
		}
		return $container;
	}

	/**
	 * Initialize an instance of the Usps class.
	 *
	 * @param array $config The config
	 * @return object $Usps
	 */
	protected function initUspsRate($config = array()) {
		return new UspsRate($config);
	}
}
