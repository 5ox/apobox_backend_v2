<?php
App::uses('FedexCommon', 'Fedex.Lib');

/**
 * TestFedexCommon - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestFedexCommon extends FedexCommon {

	public function prepareRequest(array $defaults, array $data) {
		return parent::prepareRequest($defaults, $data);
	}

	public function setClientAuth(array $data) {
		return parent::setClientAuth($data);
	}

	public function initSoapClient($wsdl, array $options = ['trace' => 1]) {
		return parent::initSoapClient($wsdl, $options);
	}

	public function processError($response) {
		return parent::processError($response);
	}

}

/**
 * FedexCommon Test Case
 *
 */
class FedexCommonTest extends CakeTestCase {

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
		$this->FedexCommon = new TestFedexCommon($this->defaultConfig);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FedexCommon);
		unset($this->defaultConfig);
		parent::tearDown();
	}

	/**
	 * Confirm that exceptions are thrown on bad constructor args.
	 *
	 * @dataProvider provideConstructorArgs
	 */
	public function testConstructorArgsMissing($args, $msg) {
		$this->setExpectedException('BadMethodCallException', $msg);
		$willFail = new FedexCommon($args);
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
	 * Confirm that supplied $data will overwrite supplied $defaults and that
	 * client auth set in the constructor can be added to the resulting array.
	 *
	 * @return void
	 */
	public function testPrepareRequest() {
		$default = [
			'foo' => 'bar',
			'one' => 'two',
		];
		$data = [
			'alpha' => 'beta',
			'one' => 'seven',
		];
		$result = $this->FedexCommon->prepareRequest($default, $data);
		$this->assertArrayHasKey('WebAuthenticationDetail', $result);
		$this->assertArrayHasKey('ClientDetail', $result);
		$this->assertArrayHasKey('UserCredential', $result['WebAuthenticationDetail']);
		$this->assertSame($default['foo'], $result['foo']);
		$this->assertNotSame($default['one'], $result['one']);
		$this->assertSame($data['one'], $result['one']);
	}

	/**
	 * Confirm that the auth data set in the constructor can be added to a supplied
	 * data array and that the values match.
	 *
	 * @return void
	 */
	public function testSetClientAuth() {
		$data = [
			'foo' => 'bar',
		];
		$result = $this->FedexCommon->setClientAuth($data);
		$this->assertArrayHasKey('WebAuthenticationDetail', $result);
		$this->assertArrayHasKey('ClientDetail', $result);
		$this->assertArrayHasKey('UserCredential', $result['WebAuthenticationDetail']);
		$this->assertSame(
			$result['WebAuthenticationDetail']['UserCredential']['Key'],
			$this->defaultConfig['apiKey']
		);
		$this->assertSame(
			$result['ClientDetail']['AccountNumber'],
			$this->defaultConfig['apiAccount']
		);
		$this->assertSame($data['foo'], $result['foo']);
	}

	/**
	 * Confirm an instance of class SoapClient is created by initSoapClient()
	 *
	 * @return void
	 */
	public function testInitSoapClient() {
		$wsdl = APP . 'Plugin' . DS . 'Fedex' . DS . 'Test' . DS . 'wsdl' . DS . 'TestRateSample.wsdl';
		$this->assertInstanceOf('SoapClient', $this->FedexCommon->initSoapClient($wsdl));
	}

	/**
	 * Confirm the expected exception (and message) is thrown when response
	 * `Notifications` is an array of notification objects.
	 *
	 * @return void
	 */
	public function testProcessErrorNotificationArrayObject() {
		$msg = 'This is a RESPONSE notification error message';

		$response = new stdClass();
		$notification = new stdClass();
		$notification->Message = $msg;
		$response->Notifications = [$notification];

		$this->setExpectedException('BadRequestException', $msg);

		$result = $this->FedexCommon->processError($response);
	}

	/**
	 * Confirm the expected exception (and message) is thrown when response
	 * `Notifications` is not an array.
	 *
	 * @return void
	 */
	public function testProcessErrorNotificationString() {
		$msg = 'The request could not be completed.';

		$response = new stdClass();
		$response->Notifications = 'foo';

		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->FedexCommon->processError($response);
	}

	/**
	 * Confirm the expected exception (and message) is thrown when response
	 * `Notifications` is an array but the message is a string and not an
	 * object.
	 *
	 * @return void
	 */
	public function testProcessErrorNotificationArrayNotObject() {
		$msg = 'Rate request could not be completed.';

		$response = new stdClass();
		$notification = $msg;
		$response->Notifications = [$notification];

		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->FedexCommon->processError($response);
	}

	/**
	 * Confirm the expected exception (and message) is thrown when response
	 * `Notifications` is an object.
	 *
	 * @return void
	 */
	public function testProcessErrorNotificationObjectNotArray() {
		$msg = 'This is a RESPONSE notification error message';

		$response = new stdClass();
		$notification = new stdClass();
		$notification->Message = $msg;
		$response->Notifications = $notification;

		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->FedexCommon->processError($response);
	}
}
