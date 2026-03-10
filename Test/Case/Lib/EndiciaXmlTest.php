<?php
App::uses('EndiciaXml', 'Lib');

/**
 * TestEndiciaXml - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestEndiciaXml extends EndiciaXml {
	public $layout = 'C:\Users\Public\Documents\Endicia\DAZzle\APO FPO Small 6x4.lyt';
	public function buildDataFromTemplate($order) {
		return parent::buildDataFromTemplate($order);
	}
	public function convertToXml($data) {
		return parent::convertToXml($data);
	}
	public function getFilename($orderId) {
		return parent::getFilename($orderId);
	}
	public function setMailClass($mailClass) {
		return parent::setMailClass($mailClass);
	}
}

/**
 * EndiciaXml Test Case
 *
 */
class EndiciaXmlTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->EndiciaXml = new TestEndiciaXml();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->EndiciaXml);
		parent::tearDown();
	}

	/**
	 * Confirm that exceptions are thrown if missing config variable.
	 *
	 * @return void
	 */
	public function testConstructorMissingConfig() {
		Configure::delete('ShippingApis.Endicia.accountNumber');
		$msg = 'Missing required [ShippingApis.Endicia.accountNumber] config key.';
		$this->setExpectedException('BadMethodCallException', $msg);
		$willFail = new EndiciaXml();
	}

	/**
	 * Confirm that the data array is prepared correctly with keys/vals set as
	 * expected.
	 *
	 * @return void
	 */
	public function testBuildDataFromTemplate() {
		$order = array(
			'Order' => array(
				'orders_id' => '12345678',
				'mail_class' => 'PRIORITY',
				'package_type' => 'RECTPARCEL',
				'width' => 2,
				'length' => 4,
				'depth' => 6,
				'BalloonRate' => null,
				'NonMachinable' => 'True',
				'OversizeRate' => null,
				'delivery_name' => 'Joe Tester',
				'delivery_company' => '',
				'delivery_street_address' => '123 Test Rd',
				'delivery_suburb' => '',
				'delivery_city' => 'Testville',
				'delivery_state' => 'AA',
				'delivery_postcode' => '12345',
				'delivery_country' => 'United States',
				'insurance_coverage' => '50.00',
				'customers_email_address' => 'test@loadsys.com',
				'weight_oz' => '16.5'
			),
			'Customer' => array(
				'billing_id' => 'BT12345',
			),
		);
		$result = $this->EndiciaXml->buildDataFromTemplate($order);
		$this->assertEquals($this->EndiciaXml->layout, $result['DAZzle']['@Layout']);
		$this->assertEquals($order['Order']['mail_class'], $result['DAZzle']['Package']['MailClass']);
		$this->assertEquals('False', $result['DAZzle']['Package']['BalloonRate']);
		$this->assertEquals('True', $result['DAZzle']['Package']['NonMachinable']);
		$this->assertEquals($order['Customer']['billing_id'], $result['DAZzle']['Package']['ReferenceID']);
		$this->assertEquals($order['Order']['weight_oz'], $result['DAZzle']['Package']['CustomsWeight1']);
		$this->assertEquals($order['Order']['insurance_coverage'], $result['DAZzle']['Package']['CustomsValue1']);
	}

	/**
	 * Confirm that an array can be converted to XML.
	 *
	 * @return void
	 */
	public function testConvertToXml() {
		$data = array(
			'Test' => array(
				'@Version' => 1,
				'@Testing' => 'True',
				'Foo' => 'Bar',
			),
		);
		$expected = '<?xml version="1.0" encoding="UTF-8"?>
<Test Version="1" Testing="True"><Foo>Bar</Foo></Test>';
		$result = $this->EndiciaXml->convertToXml($data);
		$this->assertEquals($expected, trim($result));
	}

	/**
	 * Confirm that the filename returned matches the expected pattern.
	 *
	 * @return void
	 */
	public function testGetFileName() {
		$result = $this->EndiciaXml->getFileName('12345');
		$this->assertRegExp('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])_12345.xml$/', $result);
	}

	/**
	 * Confirm that createXml() returns `xml` and `filename` keys and that the
	 * `filename` matches the expected format.
	 *
	 * @return void
	 */
	public function testCreateXml() {
		$order = array(
			'Order' => array(
				'orders_id' => '12345678',
				'mail_class' => 'PRIORITY',
				'package_type' => 'RECTPARCEL',
				'width' => 2,
				'length' => 4,
				'depth' => 6,
				'BalloonRate' => null,
				'NonMachinable' => 'True',
				'OversizeRate' => null,
				'delivery_name' => 'Joe Tester',
				'delivery_company' => '',
				'delivery_street_address' => '123 Test Rd',
				'delivery_suburb' => '',
				'delivery_city' => 'Testville',
				'delivery_state' => 'AA',
				'delivery_postcode' => '12345',
				'delivery_country' => 'United States',
				'insurance_coverage' => '50.00',
				'customers_email_address' => 'test@loadsys.com',
				'weight_oz' => '16.5'
			),
			'Customer' => array(
				'billing_id' => 'BT12345',
			),
		);
		$result = $this->EndiciaXml->createXml($order);
		$this->assertArrayHasKey('xml', $result);
		$this->assertArrayHasKey('filename', $result);
		$this->assertRegExp('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])_12345678.xml$/', $result['filename']);
	}

	/**
	 * Confirm that the correct mail class is returned based on supplied input.
	 *
	 * @dataProvider provideSetMailClass
	 * @return void
	 */
	public function testSetMailClass($mailClass, $expected) {
		$result = $this->EndiciaXml->setMailClass($mailClass);
		$this->assertEquals($expected, $result);
	}

	public function provideSetMailClass() {
		return array(
			array('PRIORITY', 'PRIORITY'),
			array('PARCEL', 'PARCELSELECT'),
			array('PARCELSELECT', 'PARCELSELECT'),
			array('test', 'test'),
			array(null, null),
		);
	}
}
