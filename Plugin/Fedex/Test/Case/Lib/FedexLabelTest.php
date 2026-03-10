<?php
App::uses('FedexCommon', 'Fedex.Lib');
App::uses('FedexLabel', 'Fedex.Lib');

/**
 * TestFedexLabel - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestFedexLabel extends FedexLabel {

	public function processResponse($response) {
		return parent::processResponse($response);
	}

	public function writeFile($file, $contents) {
		return parent::writeFile($file, $contents);
	}

}

/**
 * TestFedexLabelWsdl - Class to overwrite protected properties with public ones.
 */
class TestFedexLabelWsdl extends FedexLabel {

	public $wsdlFile = null;

}

/**
 * FedexLabel Test Case
 *
 */
class FedexLabelTest extends CakeTestCase {

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
		$this->FedexLabel = new TestFedexLabel($this->defaultConfig);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FedexLabel);
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
		$willFail = new FedexLabel($args);
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
	 * Confirm an instance of class FedexLabel can be created
	 *
	 * @return void
	 */
	public function testConstructorSuccess() {
		$this->assertInstanceOf('FedexLabel', new FedexLabel($this->defaultConfig));
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
		$willFail = new TestFedexLabelWsdl($this->defaultConfig);
	}

	/**
	 * Confirm that on a successful label request the expected methods are called
	 * and the response matches expected output.
	 *
	 * @return void
	 */
	public function testLabelRequestSuccess() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestLabelSample.wsdl';
		$this->FedexLabelWs = $this->getMockFromWsdl($wsdl, 'FedexLabelWs');
		$this->FedexLabel = $this->getMockBuilder('FedexLabel')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient', 'processResponse'])
			->getMock();

		$methodResponse = 'A zpl label string';
		$response = new stdClass();
		$response->HighestSeverity = 'SUCCESS';
		$this->FedexLabel->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexLabelWs));
		$this->FedexLabel->expects($this->once())
			->method('processResponse')
			->will($this->returnValue($methodResponse));
		$this->FedexLabelWs->expects($this->any())
			->method('processShipment')
			->will($this->returnValue($response));

		$result = $this->FedexLabel->labelRequest([]);
		$this->assertSame($methodResponse, $result);
	}

	/**
	 * Confirm that on a failure label request the expected methods are called
	 * and the error response matches the expected output.
	 *
	 * @return void
	 */
	public function testLabelRequestError() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestLabelSample.wsdl';
		$this->FedexLabelWs = $this->getMockFromWsdl($wsdl, 'FedexLabelWs');
		$this->FedexLabel = $this->getMockBuilder('FedexLabel')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient', 'processError'])
			->getMock();

		$methodResponse = 'An exception thrown by processError()';
		$response = new stdClass();
		$response->HighestSeverity = 'anything except SUCCESS';
		$this->FedexLabel->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexLabelWs));
		$this->FedexLabel->expects($this->once())
			->method('processError')
			->will($this->returnValue($methodResponse));
		$this->FedexLabelWs->expects($this->any())
			->method('processShipment')
			->will($this->returnValue($response));

		$result = $this->FedexLabel->labelRequest([]);
		$this->assertSame($methodResponse, $result);
	}

	/**
	 * Confirm that when the SoapClient::processShipment() method throws a SoapFault
	 * exception it is caught and throws a BadRequestException in turn with the
	 * expected error message.
	 *
	 * @return void
	 */
	public function testLabelRequestException() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestLabelSample.wsdl';
		$this->FedexLabelWs = $this->getMockFromWsdl($wsdl, 'FedexLabelWs');
		$this->FedexLabel = $this->getMockBuilder('FedexLabel')
			->disableOriginalConstructor()
			->setMethods(['initSoapClient'])
			->getMock();

		$methodResponse = 'An exception thrown by SoapClient::processShipment()';
		$this->FedexLabel->expects($this->once())
			->method('initSoapClient')
			->will($this->returnValue($this->FedexLabelWs));
		$this->FedexLabelWs->expects($this->any())
			->method('processShipment')
			->will($this->throwException(new SoapFault('0', $methodResponse)));

		$this->setExpectedException('BadRequestException', $methodResponse);
		$result = $this->FedexLabel->labelRequest([]);
	}

	/**
	 * Confirm that processResponse() can return the expected zpl data when the
	 * API ImageType is set to `ZPLII`.
	 *
	 * @return void
	 */
	public function testProcessResponseZpl() {
		Configure::write('ShippingApis.Fedex.label.type', 'ZPLII');
		$imageType = 'ZPLII';
		$expected = 'zpl data';

		$response = new stdClass();
		$response->CompletedShipmentDetail = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->ImageType = $imageType;
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image = $expected;

		$result = $this->FedexLabel->processResponse($response);
		$this->assertSame($expected, $result);
	}

	/**
	 * Confirm that processResponse() can write an image label file to disk when the
	 * API ImageType is set to `PNG` or `PDF`.
	 *
	 * @return void
	 */
	public function testProcessResponseFile() {
		Configure::write('ShippingApis.Fedex.label.type', 'PNG');
		$imageType = 'PNG';

		$this->FedexLabel = $this->getMockBuilder('TestFedexLabel')
			->disableOriginalConstructor()
			->setMethods(['writeFile'])
			->getMock();
		$this->FedexLabel->expects($this->once())
			->method('writeFile')
			->will($this->returnValue(true));

		$response = new stdClass();
		$response->CompletedShipmentDetail = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->ImageType = $imageType;
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image = 'anything';

		$result = $this->FedexLabel->processResponse($response);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that processResponse() can return the expected zpl data when the
	 * API ImageType is set to `ZPLII`.
	 *
	 * @return void
	 */
	public function testProcessResponseInvalidType() {
		Configure::write('ShippingApis.Fedex.label.type', 'ZPLII');
		$imageType = 'foo';

		$response = new stdClass();
		$response->CompletedShipmentDetail = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label = new stdClass();
		$response->CompletedShipmentDetail->CompletedPackageDetails->Label->ImageType = $imageType;

		$result = $this->FedexLabel->processResponse($response);
		$this->assertFalse($result);
	}
}
