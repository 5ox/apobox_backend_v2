<?php
App::uses('FedexCommon', 'Fedex.Lib');
App::uses('FedexRate', 'Fedex.Lib');

/**
 * TestFedexRate - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestFedexRate extends FedexRate {

	public function processResponse($response) {
		return parent::processResponse($response);
	}

}

/**
 * TestFedexRateWsdl - Class to overwrite protected properties with public ones.
 */
class TestFedexRateWsdl extends FedexRate {

	public $wsdlFile = null;

}

/**
 * FedexRate Test Case
 *
 */
class FedexRateTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->defaultConfig = [
			'apiKey' => 'foo',
			'apiPassword' => 'bar',
			'apiAccount' => '1234567',
			'apiMeter' => '7654321',
		];
		$this->FedexRate = new TestFedexRate($this->defaultConfig);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FedexRate);
		unset($this->defaultConfig);
		parent::tearDown();
	}

	/**
	 * Confirm that exceptions are thrown on bad constructor args for the parent class
	 *
	 * @dataProvider provideConstructorArgs
	 */
	public function tesConstructorParrentArgsMissing($args, $msg) {
		$this->setExpectedException('BadMethodCallException', $msg);
		$willFail = new FedexRate($args);
	}

	public function provideConstructorArgs() {
		return [
			[
				['apiKey' => ''],
				'Missing required [apiKey] config key.',
			],
			[
				['apiKey' => 'foo', 'apiPassword' => ''],
				'Missing required [apiPassword] config key.',
			],
			[
				['apiKey' => 'foo', 'apiPassword' => 'bar', 'apiAccount' => ''],
				'Missing required [apiAccount] config key.',
			],
			[
				['apiKey' => 'foo', 'apiPassword' => 'bar', 'apiAccount' => '1234', 'apiMeter' => ''],
				'Missing required [apiMeter] config key.',
			],
		];
	}

	/**
	 * Confirm an instance of class FedexRate can be created
	 *
	 * @return void
	 */
	public function testConstructorSuccess() {
		$this->assertInstanceOf('FedexRate', new FedexRate($this->defaultConfig));
	}

	/**
	 * Confirm the contructor will throw the expected exception if the required
	 * wsdl file can't be found.
	 *
	 * @return void
	 */
	public function testConstructorInvalidWsdl() {
		$msg = 'Missing required wsdl definition file.';
		$this->setExpectedException('BadMethodCallException', $msg);
		$willFail = new TestFedexRateWsdl($this->defaultConfig);
	}

	/**
	 * Confirm that on a successful rate request the expected methods are called
	 * and the response matches expected output.
	 *
	 * @return void
	 */
	public function testRateRequestSuccess() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestRateSample.wsdl';
		$this->FedexRateWs = $this->getMockFromWsdl($wsdl, 'FedexRateWs');
		$this->FedexRate = $this->getMockBuilder('FedexRate')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient', 'processResponse'])
			->getMock();

		$methodResponse = 'A rate response array';
		$response = new stdClass();
		$response->HighestSeverity = 'SUCCESS';
		$this->FedexRate->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexRateWs));
		$this->FedexRate->expects($this->once())
			->method('processResponse')
			->will($this->returnValue($methodResponse));
		$this->FedexRateWs->expects($this->any())
			->method('getRates')
			->will($this->returnValue($response));

		$result = $this->FedexRate->rateRequest([]);
		$this->assertSame($methodResponse, $result);
	}

	/**
	 * Confirm that on a failure rate request the expected methods are called
	 * and the error response matches the expected output.
	 *
	 * @return void
	 */
	public function testRateRequestError() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestRateSample.wsdl';
		$this->FedexRateWs = $this->getMockFromWsdl($wsdl, 'FedexRateWs');
		$this->FedexRate = $this->getMockBuilder('FedexRate')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient', 'processError'])
			->getMock();

		$methodResponse = 'An exception thrown by processError()';
		$response = new stdClass();
		$response->HighestSeverity = 'anything except SUCCESS';
		$this->FedexRate->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexRateWs));
		$this->FedexRate->expects($this->once())
			->method('processError')
			->will($this->returnValue($methodResponse));
		$this->FedexRateWs->expects($this->any())
			->method('getRates')
			->will($this->returnValue($response));

		$result = $this->FedexRate->rateRequest([]);
		$this->assertSame($methodResponse, $result);
	}

	/**
	 * Confirm that when the SoapClient::getRates() method throws a SoapFault
	 * exception it is caught and throws a BadRequestException in turn with the
	 * expected error message.
	 *
	 * @return void
	 */
	public function testRateRequestException() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestRateSample.wsdl';
		$this->FedexRateWs = $this->getMockFromWsdl($wsdl, 'FedexRateWs');
		$this->FedexRate = $this->getMockBuilder('FedexRate')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient'])
			->getMock();

		$methodResponse = 'An exception thrown by SoapClient::getRates()';
		$this->FedexRate->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexRateWs));
		$this->FedexRateWs->expects($this->any())
			->method('getRates')
			->will($this->throwException(new SoapFault('0', $methodResponse)));

		$this->setExpectedException('BadRequestException', $methodResponse);
		$result = $this->FedexRate->rateRequest([]);
	}

	/**
	 * Confirm that processResponse() can extract the expected values from the
	 * response object and return the expected array.
	 *
	 * @return void
	 */
	public function testProcessResponse() {
		$amount = '42.17';
		$expected = [[
			'@CLASSID' => 'FedEx',
			'MailService' => 'FEDEX GROUND',
			'Rate' => $amount,
		]];

		$response = new stdClass();
		$response->RateReplyDetails = new stdClass();
		$response->RateReplyDetails->ServiceType = 'FEDEX_GROUND';

		$response->RateReplyDetails->RatedShipmentDetails = new stdClass();
		$response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail = new stdClass();
		$response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge = new stdClass();
		$response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount = $amount;

		$result = $this->FedexRate->processResponse($response);
		$this->assertSame($expected, $result);
	}

}
