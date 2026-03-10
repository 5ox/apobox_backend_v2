<?php
App::uses('UspsRate', 'Usps.Lib');
App::uses('Xml', 'Utility');

/**
 * TestUspsRate - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestUspsRate extends UspsRate {

	public $userId = null;
	public $postUrl = 'https://production.shippingapis.com/ShippingApi.dll';
	public $errorKey = 'Error';

	public function prepareAndProcessRateRequest($data) {
		return parent::prepareAndProcessRateRequest($data);
	}
	public function prepareRequest(array $data) {
		return parent::prepareRequest($data);
	}
	public function buildDataFromTemplate(array $data) {
		return parent::buildDataFromTemplate($data);
	}
	public function makeRequest($data) {
		return parent::makeRequest($data);
	}
	public function processResponse($data) {
		return parent::processResponse($data);
	}
	public function checkResponse($errorKey, array $data) {
		return parent::checkResponse($errorKey, $data);
	}
	public function extractRatesArray(array $data) {
		return parent::extractRatesArray($data);
	}
	public function initHttpSocket() {
		return parent::initHttpSocket();
	}
}

/**
 * UspsRate Test Case
 *
 */
class UspsRateTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->defaultConfig = [
			'userId' => '1234567',
		];
		$this->defaultData = array(
			'Service' => 'Priority',
			'ZipOrigination' => '91361',
			'ZipDestination' => '28712',
			'Pounds' => '1',
			'Ounces' => '8',
			'Container' => 'NONRECTANGULAR',
			'Size' => 'LARGE',
			'Width' => '15',
			'Length' => '30',
			'Height' => '15',
			'Girth' => '55',
		);
		$this->UspsRate = new TestUspsRate($this->defaultConfig);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UspsRate);
		unset($this->defaultConfig);
		unset($this->defaultData);
		parent::tearDown();
	}


	/**
	 * Confirm that exceptions are thrown on bad constructor args.
	 *
	 * @dataProvider provideConstructorArgs
	 */
	public function testConstructorArgsMissing($args, $msg) {
		$this->setExpectedException('BadMethodCallException', $msg);
		$willFail = new UspsRate($args);
	}

	public function provideConstructorArgs() {
		return array(
			array(
				array('userId' => ''),
				'Missing required [userId] config key.',
			),
			array(
				array('foo' => 'bar'),
				'Missing required [userId] config key.',
			),
			array(
				array(),
				'Missing required [userId] config key.',
			),
		);
	}

	/**
	 * Confirm that the array to be converted to XML is built correctly and
	 * has all required keys.
	 *
	 * @return void
	 */
	public function testBuildDataFromTemplateValidData() {
		$expectedData = $this->defaultData;
		$expectedData['@ID'] = 0;
		$expected = array(
			'RateV4Request' => array(
				'@USERID' => '1234567',
				'Revision' => (int) 2,
				'Package' => $expectedData,
			)
		);

		$result = $this->UspsRate->buildDataFromTemplate($this->defaultData);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm that an exception is thrown if a required key is missing.
	 *
	 * @return void
	 */
	public function testBuildDataFromTemplateMissingKey() {
		$data = $this->defaultData;
		unset($data['Service']);
		$msg = 'Required keys for an API request are missing.';
		$this->setExpectedException('BadMethodCallException', $msg);
		$result = $this->UspsRate->buildDataFromTemplate($data);
	}

	/**
	 * Confirm that the XML fragment build from an array matches the expected value.
	 *
	 * @return void
	 */
	public function testPrepareRequest() {
		$expected = '<RateV4Request USERID="1234567"><Revision>2</Revision><Package ID="0"><Service>Priority</Service><ZipOrigination>91361</ZipOrigination><ZipDestination>28712</ZipDestination><Pounds>1</Pounds><Ounces>8</Ounces><Container>NONRECTANGULAR</Container><Size>LARGE</Size><Width>15</Width><Length>30</Length><Height>15</Height><Girth>55</Girth></Package></RateV4Request>';
		$result = $this->UspsRate->prepareRequest($this->defaultData);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm that a mocked request with invalid data results in a thrown
	 * exception when attempting to process the response.
	 *
	 * @return void
	 */
	public function testMakeRequesProceedsToProcess() {
		$this->UspsRate = $this->getMock('TestUspsRate',
			array('initHttpSocket'),
			array($this->defaultConfig)
		);
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('post', 'body')
		);
		$query = null;
		$msg = 'The API response is not valid.';

		$this->UspsRate->expects($this->once())
			->method('initHttpSocket')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('post')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('body')
			->with()
			->will($this->returnValue($this->HttpSocket));

		$data = array();
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->UspsRate->makeRequest($data);
	}

	/**
	 * Confirm the correct exception is thrown if the HttpSocket::post request
	 * fails.
	 *
	 * @return void
	 */
	public function testMakeRequestFails() {
		$this->UspsRate = $this->getMock('TestUspsRate',
			array('initHttpSocket'),
			array($this->defaultConfig)
		);
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('post', 'body')
		);
		$msg = 'Missing or invalid response from the API server.';

		$this->UspsRate->expects($this->once())
			->method('initHttpSocket')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('post')
			->with()
			->will($this->throwException(new Exception));

		$data = array();
		$this->setExpectedException('NotFoundException', $msg);
		$result = $this->UspsRate->makeRequest($data);
	}

	/**
	 * Confirm that invalid XML throws the correct exception.
	 *
	 * @return void
	 */
	public function testProcessResponseInvalidXml() {
		$response = 'foo';
		$msg = 'The API response is not valid.';
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->UspsRate->processResponse(trim($response));
	}

	/**
	 * Confirm that valid XML is processed and calls checkResponse().
	 *
	 * @return void
	 */
	public function testProcessResponseValidXml() {
		$response = '<?xml version="1.0" encoding="UTF-8"?>
<Error><Number>80040B1A</Number><Description>Authorization failure. Perhaps username and/or password is incorrect.</Description><Source>USPSCOM::DoAuth</Source></Error>';
		$msg = 'Authorization failure. Perhaps username and/or password is incorrect.';
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->UspsRate->processResponse(trim($response));
	}

	/**
	 * Confirm that a nested error response is caught and an exception is thrown.
	 *
	 * @return void
	 */
	public function testCheckResponseWithNestedError() {
		$response = array(
			'RateV4Response' => array(
				'Package' => array(
					'@ID' => '0',
					'Error' => array(
						'Number' => '-2147219429',
						'Source' => 'RateEngineV4;RateV4.ProcessRequest',
						'Description' => 'Invalid Value for  GIRTH',
						'HelpFile' => '',
						'HelpContext' => ''
					)
				)
			)
		);

		$msg = 'Invalid Value for  GIRTH';
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->UspsRate->checkResponse($this->UspsRate->errorKey, $response);
	}

	/**
	 * Confirm that a deep nested error response is caught and an exception is thrown.
	 *
	 * @return void
	 */
	public function testCheckResponseWithDeeperNestedError() {
		$response = array(
			'RateV4Response' => array(
				'Extra' => array(
					'Package' => array(
						'@ID' => '0',
						'Error' => array(
							'Number' => '-2147219429',
							'Source' => 'RateEngineV4;RateV4.ProcessRequest',
							'Description' => 'Invalid Value for  GIRTH',
							'HelpFile' => '',
							'HelpContext' => ''
						)
					)
				)
			)
		);

		$msg = 'Invalid Value for  GIRTH';
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->UspsRate->checkResponse($this->UspsRate->errorKey, $response);
	}

	/**
	 * Confirm that the response is returned unchaged if no error exists.
	 *
	 * @return void
	 */
	public function testCheckResponseWithNoError() {
		$response = array(
			'RateV4Response' => array(
				'Extra' => array(
					'Package' => array(
						'@ID' => '0',
					)
				)
			)
		);
		$result = $this->UspsRate->checkResponse($this->UspsRate->errorKey, $response);
		$this->assertEquals($response, $result);
	}

	/**
	 * Confirm getRates returns the expected response with valid data.
	 *
	 * @return void
	 */
	public function testGetRates() {
		$this->UspsRate = $this->getMock('TestUspsRate',
			array('initHttpSocket'),
			array($this->defaultConfig)
		);
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('post', 'body')
		);

		$response = array(
			'RateV4Response' => array(
				'Extra' => array(
					'Package' => array(
						'@ID' => '0',
					)
				)
			)
		);

		$this->UspsRate->expects($this->once())
			->method('initHttpSocket')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('post')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('body')
			->with()
			->will($this->returnValue($response));

		$result = $this->UspsRate->getRates($this->defaultData);
		$this->assertEquals($response, $result);
	}

	/**
	 * Confirm that girth is calculated correctly when supplied with valid data.
	 *
	 * @dataProvider provideCalculateGirth
	 * @return void
	 */
	public function testCalculateGirth($height, $width, $expected) {
		$result = $this->UspsRate->calculateGirth($height, $width);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateGirth() {
		return array(
			array(2, 4, 12),
			array(200, 40, 480),
			array(17, 14.5, 63),
			array('foo', 4, 8),
			array(null, null, 0),
		);
	}

	/**
	 * Confirm that size is calculated correctly when supplied with valid data.
	 *
	 * @dataProvider provideCalculateSize
	 * @return void
	 */
	public function testCalculateSize($height, $length, $width, $expected) {
		$result = $this->UspsRate->calculateSize($height, $length, $width);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateSize() {
		return array(
			array(2, 4, 12, 'Regular'),
			array(200, 40, 480, 'Large'),
			array(1, 2, 1, 'Regular'),
			array(13, 1, 1, 'Large'),
			array(null, null, null, 'Regular'),
			array(null, null, 13, 'Large'),
		);
	}

	/**
	 * Confirm that rates can be filtered (or not) when filterRates is supplied
	 * with a properly formatted filtering array.
	 *
	 * @dataProvider provideFilterRates
	 * @return void
	 */
	public function testFilterRates($rates, $classes, $expected) {
		$result = $this->UspsRate->filterRates($rates, $classes);
		$this->assertEquals($expected, $result);
	}

	public function provideFilterRates() {
		$rates = array(
			'RateV4Response' => array(
				'Package' => array(
					'@ID' => '0',
					'ZipOrigination' => '46563',
					'ZipDestination' => '12345',
					'Pounds' => '1',
					'Ounces' => '23',
					'Size' => 'LARGE',
					'Machinable' => 'FALSE',
					'Zone' => '5',
					'Postage' => array(
						(int) 0 => array(
							'@CLASSID' => '3',
							'MailService' => 'Rate class 3',
							'Rate' => '40.50'
						),
						(int) 1 => array(
							'@CLASSID' => '2',
							'MailService' => 'Rate class 2',
							'Rate' => '40.50'
						),
						(int) 2 => array(
							'@CLASSID' => '1',
							'MailService' => 'Rate class 1',
							'Rate' => '10.80'
						),
						(int) 3 => array(
							'@CLASSID' => '4',
							'MailService' => 'Rate class 4',
							'Rate' => '10.17'
						),
						(int) 4 => array(
							'@CLASSID' => '6',
							'MailService' => 'Rate class 6',
							'Rate' => '3.72'
						),
						(int) 5 => array(
							'@CLASSID' => '7',
							'MailService' => 'Rate class 7',
							'Rate' => '3.55'
						)
					)
				)
			)
		);

		return array(
			array(
				$rates,
				array('Rate class 6' => '6', 'Rate class 4' => '4'),
				array(
					$rates['RateV4Response']['Package']['Postage'][3],
					$rates['RateV4Response']['Package']['Postage'][4],
				),
			),
			array(
				$rates,
				array('Key value does not matter' => '2', 'Rate class 7' => '7'),
				array(
					$rates['RateV4Response']['Package']['Postage'][1],
					$rates['RateV4Response']['Package']['Postage'][5],
				),
			),
			array(
				$rates,
				array(
					'Rate class 1' => '1',
					'Rate class 2' => '2',
					'Rate class 3' => '3',
					'Rate class 4' => '4',
					'THERE IS NO #5' => '5',
					'Rate class 6' => '6',
					'Rate class 7' => '7',
				),
				array(
					$rates['RateV4Response']['Package']['Postage'][0],
					$rates['RateV4Response']['Package']['Postage'][1],
					$rates['RateV4Response']['Package']['Postage'][2],
					$rates['RateV4Response']['Package']['Postage'][3],
					$rates['RateV4Response']['Package']['Postage'][4],
					$rates['RateV4Response']['Package']['Postage'][5],
				),
			),
			array(
				$rates,
				null,
				array(
					$rates['RateV4Response']['Package']['Postage'][0],
					$rates['RateV4Response']['Package']['Postage'][1],
					$rates['RateV4Response']['Package']['Postage'][2],
					$rates['RateV4Response']['Package']['Postage'][3],
					$rates['RateV4Response']['Package']['Postage'][4],
					$rates['RateV4Response']['Package']['Postage'][5],
				),
			),
		);
	}

	/**
	 * Confirm that rates are properly extracted from the API response. This
	 * accounts for single rate responses.
	 *
	 * @dataProvider provideExtractRatesArray
	 * @return void
	 */
	public function testExtractRatesArray($rates) {
		$result = $this->UspsRate->extractRatesArray($rates);
		$this->assertTrue(!empty($result[0]), 'Rates should be an array of rate arrays.');
		$this->assertArrayHasKey('@CLASSID', $result[0], 'Rates arrays should contain rate data.');
	}

	public function provideExtractRatesArray() {
		return [
			[['RateV4Response' => ['Package' => ['Postage' => [
				'@CLASSID' => '3',
				'MailService' => 'Rate class 3',
				'Rate' => '40.50'
			]]]]],

			[['RateV4Response' => ['Package' => ['Postage' => [
				(int) 0 => [
					'@CLASSID' => '3',
					'MailService' => 'Rate class 3',
					'Rate' => '40.50'
				],
				(int) 1 => [
					'@CLASSID' => '2',
					'MailService' => 'Rate class 2',
					'Rate' => '40.50'
				],
				(int) 2 => [
					'@CLASSID' => '1',
					'MailService' => 'Rate class 1',
					'Rate' => '10.80'
				],
				(int) 3 => [
					'@CLASSID' => '4',
					'MailService' => 'Rate class 4',
					'Rate' => '10.17'
				],
				(int) 4 => [
					'@CLASSID' => '6',
					'MailService' => 'Rate class 6',
					'Rate' => '3.72'
				],
				(int) 5 => [
					'@CLASSID' => '7',
					'MailService' => 'Rate class 7',
					'Rate' => '3.55'
				]
			]]]]],
		];
	}

	/**
	 * Confirm that a ZIP code with +4 is properly prepared to work with the
	 * rate API.
	 *
	 * @dataProvider providePrepareZip
	 * @return void
	 */
	public function testPrepareZip($zip, $expected) {
		$result = $this->UspsRate->prepareZip($zip);
		$this->assertEquals($expected, $result);
	}

	public function providePrepareZip() {
		return array(
			array('12345', '12345'),
			array('12345-6789', '12345'),
			array('12345-67891234', '12345'),
			array('123450-6789', '12345'),
			array('123456', '12345'),
			array('123456789', '12345'),
			array('1234', '1234'),
			array('foo', 'foo'),
		);
	}

	/**
	 * Confirm an instance of class HttpSocket is created by initHttpSocket()
	 *
	 * @return void
	 */
	public function testInitHttpSocket() {
		$this->assertInstanceOf('HttpSocket', $this->UspsRate->initHttpSocket());
	}
}
