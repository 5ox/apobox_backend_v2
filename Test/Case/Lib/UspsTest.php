<?php
App::uses('Usps', 'Lib');

/**
 * TestUsps - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestUsps extends Usps {

	public function calculateWeight($ounces) {
		return parent::calculateWeight($ounces);
	}
	public function calculateContainer($package, $size) {
		return parent::calculateContainer($package, $size);
	}
	public function initUspsRate($config = array()) {
		return parent::initUspsRate($config);
	}
}

/**
 * Usps Test Case
 *
 */
class UspsTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Usps = new TestUsps();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Usps);
		parent::tearDown();
	}

	/**
	 * Confirm that ounces are correctly converted to pounds and ounces
	 *
	 * @dataProvider provideCalculateWeight
	 * @return void
	 */
	public function testCalculateWeight($ounces, $expected) {
		$result = $this->Usps->calculateWeight($ounces);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateWeight() {
		return array(
			array(2, array('pounds' => 0, 'ounces' => 2)),
			array(15.5, array('pounds' => 0, 'ounces' => 16)),
			array(23, array('pounds' => 1, 'ounces' => 7)),
			array(147.3, array('pounds' => 9, 'ounces' => 4)),
			array(0, array('pounds' => 0, 'ounces' => 0)),
			array(null, array('pounds' => 0, 'ounces' => 0)),
			array('foo', array('pounds' => 0, 'ounces' => 0)),
			array(2015, array('pounds' => 125, 'ounces' => 15)),
		);
	}

	/**
	 * Confirm that ounces are correctly converted to pounds and ounces
	 *
	 * @dataProvider provideCalculateContainer
	 * @return void
	 */
	public function testCalculateContainer($package, $size, $expected) {
		$result = $this->Usps->calculateContainer($package, $size);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateContainer() {
		return array(
			array('FLATRATEENVELOPE', 'Large', 'Variable Flat Rate Envelope'),
			array('FLATRATEENVELOPE', 'Regular', ''),
			array('RECTPARCEL', 'Large', 'Rectangular'),
			array('RECTPARCEL', 'Regular', ''),
			array('foo', 'Regular', ''),
			array('bar', 'Large', 'Rectangular'),
		);
	}

	/**
	 * Confirm that all expected methods are called when using the Usps backend.
	 *
	 * @return void
	 */
	public function testGetUspsRates() {
		$userId = '1234567';
		Configure::write('ShippingApis.Usps.userId', $userId);
		Configure::write('ShippingApis.Rates.backend', 'Usps');
		$order = array('Order' => array(
			'depth' => 2,
			'width' => 4,
			'length' => 8,
			'weight_oz' => 23,
			'package_type' => 'RECTPARCEL',
			'delivery_postcode' => '28712',
		));

		$this->UspsRate = $this->getMock('UspsRate',
			array(
				'calculateGirth',
				'calculateSize',
				'getRates',
				'filterRates',
				'prepareZip',
			),
			array(),
			'',
			false
		);

		$rates =  array (
			'Service' => 'All',
			'ZipOrigination' => '46563',
			'ZipDestination' => '28712',
			'Pounds' => 1.0,
			'Ounces' => 7.0,
			'Container' => '',
			'Size' => 'Regular',
			'Width' => 4,
			'Length' => 8,
			'Height' => 2,
			'Machinable' => 'false',
		);

		$this->Usps = $this->getMock('Usps', array(
			'initUspsRate'
		));
		$this->Usps->expects($this->once())
			->method('initUspsRate')
			->with()
			->will($this->returnValue($this->UspsRate));
		$this->UspsRate->expects($this->once())
			->method('calculateSize')
			->with($order['Order']['depth'], $order['Order']['length'], $order['Order']['width'])
			->will($this->returnValue('Regular'));
		$this->UspsRate->expects($this->once())
			->method('prepareZip')
			->with($order['Order']['delivery_postcode'])
			->will($this->returnValue('28712'));
		$this->UspsRate->expects($this->once())
			->method('getRates')
			->with($rates)
			->will($this->returnValue(array('result')));
		$this->UspsRate->expects($this->once())
			->method('filterRates')
			->with(array('result'))
			->will($this->returnValue(array('result')));

		$result = $this->Usps->getRates($order);
		$this->assertEquals(array('result'), $result);
	}

	/**
	 * Confirm an instance of class UspsRate is created by initUspsRate()
	 *
	 * @return void
	 */
	public function testInitUspsRate() {
		$config = array(
			'userId' => 'foo',
			'apiVersion' => 'bar',
			'postUrl' => 'here',
		);
		$this->assertInstanceOf('UspsRate', $this->Usps->initUspsRate($config));
	}
}
