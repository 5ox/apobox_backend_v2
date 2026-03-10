<?php
App::uses('Fedex', 'Lib');

/**
 * TestFedex - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestFedex extends Fedex {

	public function getCountryCode($country) {
		return parent::getCountryCode($country);
	}

	public function calculateWeight($ounces) {
		return parent::calculateWeight($ounces);
	}

	public function initFedex($type) {
		return parent::initFedex($type);
	}

	public function addCommonData($data, $order) {
		return parent::addCommonData($data, $order);
	}
}

/**
 * Fedex Test Case
 *
 */
class FedexTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = [
		'app.country',
		'app.customer',
		'app.order',
		'app.order_status',
		'app.order_total',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Fedex = new TestFedex();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Fedex);
		parent::tearDown();
	}

	/**
	 * Confirm that the expected 2 character country code is returned based on
	 * input country.
	 *
	 * @dataProvider provideGetCountryCode
	 * @return void
	 */
	public function testGetCountryCode($country, $expected) {
		$result = $this->Fedex->getCountryCode($country);
		$this->assertSame($expected, $result);
	}

	public function provideGetCountryCode() {
		return [
			['United States', 'US'],
			['Argentina', 'AR'],
			['Antarctica', 'AQ'],
			['Costa Rica', 'CR'],
			['foo', null],
			[null, null],
		];
	}

	/**
	 * Confirm that ounces are correctly converted to a pounds and ounces string
	 *
	 * @dataProvider provideCalculateWeight
	 * @return void
	 */
	public function testCalculateWeight($ounces, $expected) {
		$result = $this->Fedex->calculateWeight($ounces);
		$this->assertSame($expected, $result);
	}

	public function provideCalculateWeight() {
		return [
			[15, '0.15'],
			[16, '1.0'],
			[17, '1.1'],
			[2, '0.2'],
			[48, '3.0'],
			[67, '4.3'],
			[0, '0.0'],
			['foo', '0.0'],
			[null, '0.0'],
			[false, '0.0'],
		];
	}

	/**
	 * Confirm an instance of class FedexRate is created by initFedex()
	 * with arg `Rate`.
	 *
	 * @return void
	 */
	public function testInitFedex() {
		$this->assertInstanceOf('FedexRate', $this->Fedex->initFedex('Rate'));
	}

	/**
	 * Confirm that all expected methods are called when using the Fedex rate backend.
	 *
	 * @return void
	 */
	public function testGetFedexRate() {
		$orderId = 1;
		$order = ClassRegistry::init('Order')->findOrderForCharge($orderId);

		$this->FedexRate = $this->getMockBuilder('FedexRate')
			->disableOriginalConstructor()
			->setMethods(['rateRequest'])
			->getMock();

		$this->Fedex = $this->getMock('Fedex', [
			'initFedex'
		]);
		$this->Fedex->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($this->FedexRate));
		$this->FedexRate->expects($this->once())
			->method('rateRequest')
			->will($this->returnValue(['rate results']));

		$result = $this->Fedex->getRate($order);
		$this->assertSame(['rate results'], $result);
	}

	/**
	 * Confirm that all expected methods are called when printing a Fedex label.
	 *
	 * @return void
	 */
	public function testPrintLabel() {
		$orderId = 1;
		$order = ClassRegistry::init('Order')->findOrderForCharge($orderId);

		$this->FedexLabel = $this->getMockBuilder('FedexLabel')
			->disableOriginalConstructor()
			->setMethods(['labelRequest'])
			->getMock();

		$this->Fedex = $this->getMock('Fedex', [
			'initFedex'
		]);
		$this->Fedex->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($this->FedexLabel));
		$this->FedexLabel->expects($this->once())
			->method('labelRequest')
			->will($this->returnValue(['label results']));

		$result = $this->Fedex->printLabel($order);
		$this->assertSame(['label results'], $result);
	}

	/**
	 * Confirm that when an order doesn't have a value for `customers_telephone`
	 * the default shipper's phone number is used.
	 *
	 * @return void
	 */
	public function testAddCommonDataAddsPhoneNumber() {
		$order['Order'] = [
			'weight_oz' => 12,
			'length' => 8,
			'width' => 6,
			'depth' => 4,
			'delivery_name' => 'Foo Bar',
			'delivery_company' => '',
			'customers_telephone' => '',
			'delivery_street_address' => 'address',
			'delivery_suburb' => 'suburb',
			'delivery_city' => 'city',
			'delivery_state' => 'state',
			'delivery_postcode' => 'postcode',
			'delivery_country' => 'country',
		];
		$data = [];
		$result = $this->Fedex->addCommonData($data, $order);
		$this->assertSame(
			Configure::read('ShippingApis.Fedex.shipper.Contact.PhoneNumber'),
			$result['RequestedShipment']['Recipient']['Contact']['PhoneNumber']
		);
	}
}
