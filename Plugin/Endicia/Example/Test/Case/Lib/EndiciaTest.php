<?php
App::uses('Endicia', 'Lib');

/**
 * TestEndicia - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestEndicia extends Endicia {

	public function calculateMailClass($mailClass) {
		return parent::calculateMailClass($mailClass);
	}
	public function calculateMailShape($order) {
		return parent::calculateMailShape($order);
	}
}

/**
 * Endicia Test Case
 *
 */
class EndiciaTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Endicia = new TestEndicia();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Endicia);
		parent::tearDown();
	}

	/**
	 * Confirm that all expected methods are called when using the Endicia backend.
	 *
	 * @return void
	 */
	public function testGetRates() {
		$userId = '1234567';
		$credentials =  array(
			'requesterId' => '7654321',
			'accountId' => '1234567',
			'password' => 'secret',
		);
		Configure::write('ShippingApis.Rates.backend', 'Endicia');
		Configure::write('ShippingApis.Endicia.credentials', $credentials);

		$order = array('Order' => array(
			'depth' => 2,
			'width' => 4,
			'length' => 8,
			'weight_oz' => 23,
			'package_type' => 'RECTPARCEL',
			'delivery_postcode' => '28712',
		));

		$this->EwsLabelService = $this->getMock('EwsLabelService',
			array(
				'apiRequest',
				'filterRates',
			),
			array(),
			'',
			false
		);

		$rates = array(
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
				'MailClass' => 'Domestic',
				'WeightOz' => '23',
				'MailPieceShape' => 'Parcel',
				'FromPostalCode' => '46563',
				'ToPostalCode' => '28712',
				'MailpieceDimensions' => array(
					'Length' => '8',
					'Width' => '4',
					'Height' => '2',
				),
				'FromZIP4' => '1039',
			),
		);

		$this->Endicia = $this->getMock('Endicia', array(
			'initEwsLabelService',
			'formatRates',
		));
		$this->Endicia->expects($this->once())
			->method('initEwsLabelService')
			->with()
			->will($this->returnValue($this->EwsLabelService));
		$this->EwsLabelService->expects($this->once())
			->method('apiRequest')
			->with($rates)
			->will($this->returnValue(array('result')));
		$this->EwsLabelService->expects($this->once())
			->method('filterRates')
			->with(array('result'))
			->will($this->returnValue(array('result')));
		$this->Endicia->expects($this->once())
			->method('formatRates')
			->with(array('result'))
			->will($this->returnValue(array('result')));

		$result = $this->Endicia->getRates($order);
		$this->assertEquals(array('result'), $result);
	}

	/**
	 * Confirm that rates are formatted and ordered as expected.
	 *
	 * @return void
	 */
	public function testFormatRates() {
		$rates = array(
			0 => array(
				'@TotalAmount' => '27.43',
				'MailClass' => 'PriorityExpress',
				'Postage' => array(
					'@TotalAmount' => '27.43',
					'MailService' => 'Priority Mail Express',
					'Zone' => '5',
					'IntraBMC' => 'false',
					'Pricing' => 'CommercialBase'
				),
				'Fees' => array(
					'@TotalAmount' => '0',
					'CertificateOfMailing' => '0',
					'CertifiedMail' => '0',
					'CollectOnDelivery' => '0',
				),
				'DdpRate' => '0'
			),
			1 => array(
				'@TotalAmount' => '7.55',
				'MailClass' => 'Priority',
				'Postage' => array(
					'@TotalAmount' => '7.55',
					'MailService' => 'Priority Mail',
					'Zone' => '5',
					'IntraBMC' => 'false',
					'Pricing' => 'CommercialBase'
				),
				'Fees' => array(
					'@TotalAmount' => '0',
					'CertificateOfMailing' => '0',
					'CertifiedMail' => '0',
					'CollectOnDelivery' => '0',
				),
				'DdpRate' => '0'
			),
		);

		$expected = array(
			0 => array(
				'MailService' => 'Priority Mail Express',
				'Rate' => '27.43',
				'MailClass' => 'PriorityExpress',
			),
			1 => array(
				'MailService' => 'Priority Mail',
				'Rate' => '7.55',
				'MailClass' => 'Priority',
			),
		);

		$result = $this->Endicia->formatRates($rates);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm that the expected mail class is returned.
	 *
	 * @dataProvider provideCalculateMailClass
	 * @return void
	 */
	public function testCalculateMailClass($mailClass, $expected) {
		$result = $this->Endicia->calculateMailClass($mailClass);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateMailClass() {
		return array(
			array('PRIORITY', 'Priority'),
			array('PARCEL', 'ParcelSelect'),
			array('foo', 'Priority'),
			array('', 'Priority'),
		);
	}

	/**
	 * Confirm that the correct mail shape is returned when package type
	 * is RECTPARCEL.
	 *
	 * @return void
	 */
	public function testCalculateMailShapeRectParcel() {
		$this->EwsLabelService = $this->getMock('EwsLabelService',
			array(
				'calculateSize',
			),
			array(),
			'',
			false
		);
		$order = array('Order' => array(
			'package_type' => 'RECTPARCEL',
			'depth' => 6,
			'length' => 8,
			'width' => 4,
		));
		$this->Endicia = $this->getMock('TestEndicia', array(
			'initEwsLabelService',
		));
		$this->Endicia->expects($this->once())
			->method('initEwsLabelService')
			->with()
			->will($this->returnValue($this->EwsLabelService));
		$this->EwsLabelService->expects($this->once())
			->method('calculateSize')
			->with()
			->will($this->returnValue('Parcel'));
		$result = $this->Endicia->calculateMailShape($order);
		$this->assertEquals('Parcel', $result);
	}

	/**
	 * Confirm that the correct mail shape is returned when package type
	 * is FLATRATEENVELOPE.
	 *
	 * @return void
	 */
	public function testCalculateMailShapeFlatRateEnvelope() {
		$order = array('Order' => array(
			'package_type' => 'FLATRATEENVELOPE',
		));
		$result = $this->Endicia->calculateMailShape($order);
		$this->assertEquals('FlatRateEnvelope', $result);
	}

	/**
	 * Confirm that the correct mail shape is returned when package type
	 * is unknown or incorrectly set.
	 *
	 * @return void
	 */
	public function testCalculateMailShapeUnknown() {
		$order = array('Order' => array(
			'package_type' => 'foo',
		));
		$result = $this->Endicia->calculateMailShape($order);
		$this->assertEquals('Parcel', $result);
	}

	/**
	 * testGetEndiciaLabel
	 *
	 * @return void
	 */
	public function testGetLabel() {
		$this->markTestIncomplete('Test not yet implemented.');
	}
}
