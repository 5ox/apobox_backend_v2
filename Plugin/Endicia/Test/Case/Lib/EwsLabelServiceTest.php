<?php
App::uses('EwsLabelService', 'Endicia.Lib');
App::uses('Xml', 'Utility');

/**
 * TestEwsLabelService - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestEwsLabelService extends EwsLabelService {

	public $apiCredentialKeys = array(
		'requesterId' => 'RequesterID',
		'accountId' => 'AccountID',
		'password' => 'PassPhrase',
	);

	public function prepareRequest(array $data) {
		return parent::prepareRequest($data);
	}
	public function setCredentials($apiCredentialKeys, $data) {
		return parent::setCredentials($apiCredentialKeys, $data);
	}
	public function makeRequest($data) {
		return parent::makeRequest($data);
	}
	public function processResponse($data) {
		return parent::processResponse($data);
	}
	public function checkResponse(array $data) {
		return parent::checkResponse($data);
	}
	public function decode($data, $type = 'base64') {
		return parent::decode($data, $type);
	}
}

/**
 * Usps Test Case
 *
 */
class EwsLabelServiceTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->defaultConfig = array(
			'requesterId' => '7zzz',
			'accountId' => '1234567',
			'password' => 'foobar',
		);
		$this->defaultData = array(
			'api' => array(
				'requestKey' => 'labelRequestXML',
				'requestEndpoint' => 'GetPostageLabelXML',
			),
			'LabelRequest' => array(
				'@Test' => 'YES',
				'@LabelType' => 'Default',
				'@LabelSize' => '6x4',
				'@ImageFormat' => 'PDF',
				'RequesterID' => 'API',
				'AccountID' => 'API',
				'PassPhrase' => 'API',
				'PartnerTransactionID' => '123456789',
				'MailClass' => 'Priority',
				'PackageType' => 'RECTPARCEL',
				'ReferenceID' => 'BT4615',
			),
		);
		$this->EwsLabelService = new TestEwsLabelService($this->defaultConfig);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->EwsLabelService);
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
		$willFail = new EwsLabelService($args);
	}

	public function provideConstructorArgs() {
		return array(
			array(
				array('requesterId' => ''),
				'Missing required [requesterId] config key.',
			),
			array(
				array('foo' => 'bar'),
				'Missing required [requesterId] config key.',
			),
			array(
				array(),
				'Missing required [requesterId] config key.',
			),
			array(
				array('requesterId' => 'foo', 'accountId' => ''),
				'Missing required [accountId] config key.',
			),
			array(
				array('requesterId' => 'foo'),
				'Missing required [accountId] config key.',
			),
			array(
				array('requesterId' => 'foo', 'accountId' => 'bar', 'password' => ''),
				'Missing required [password] config key.',
			),
			array(
				array('requesterId' => 'foo', 'accountId' => 'bar'),
				'Missing required [password] config key.',
			),
		);
	}

	/**
	 * Confirm that the XML fragment built from an array matches the expected
	 * XML and that the api settings are preserved in an array.
	 *
	 * @return void
	 */
	public function testPrepareRequest() {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<LabelRequest Test="YES" LabelType="Default" LabelSize="6x4" ImageFormat="PDF"><RequesterID>7zzz</RequesterID><AccountID>1234567</AccountID><PassPhrase>foobar</PassPhrase><PartnerTransactionID>123456789</PartnerTransactionID><MailClass>Priority</MailClass><PackageType>RECTPARCEL</PackageType><ReferenceID>BT4615</ReferenceID></LabelRequest>';
		$result = $this->EwsLabelService->prepareRequest($this->defaultData);
		$this->assertEquals($this->defaultData['api'], $result['api']);
		$this->assertEquals($xml, $result['xml']);
	}

	/**
	 * Confirm that the API credentials are injected correctly into an unnested
	 * request array.
	 *
	 * @return void
	 */
	public function testSetCredentialsNotNested() {
		$result = $this->EwsLabelService->setCredentials(
			$this->EwsLabelService->apiCredentialKeys,
			$this->defaultData['LabelRequest']
		);
		$this->assertEquals($this->defaultConfig['requesterId'], $result['RequesterID']);
		$this->assertEquals($this->defaultConfig['accountId'], $result['AccountID']);
		$this->assertEquals($this->defaultConfig['password'], $result['PassPhrase']);
	}

	/**
	 * Confirm that the API credentials are injected correctly into a nested
	 * request array.
	 *
	 * @return void
	 */
	public function testSetCredentialsNested() {
		$data = array(
			'AccountStatusRequest' => array(
				'@ResponseVersion' => '1',
				'RequesterID' => 'API',
				'RequestID' => 'testStatus',
				'CertifiedIntermediary' => array(
					'AccountID' => '',
					'PassPhrase' => 'anything',
				),
			),
		);
		$result = $this->EwsLabelService->setCredentials(
			$this->EwsLabelService->apiCredentialKeys,
			$data
		);
		$this->assertEquals($this->defaultConfig['requesterId'], $result['AccountStatusRequest']['RequesterID']);
		$this->assertEquals(
			$this->defaultConfig['accountId'],
			$result['AccountStatusRequest']['CertifiedIntermediary']['AccountID']
		);
		$this->assertEquals(
			$this->defaultConfig['password'],
			$result['AccountStatusRequest']['CertifiedIntermediary']['PassPhrase']
		);
	}

	/**
	 * Confirm that a mocked request with invalid data results in a thrown
	 * exception when attempting to process the response.
	 *
	 * @return void
	 */
	public function testMakeRequestProceedsToProcess() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array('initHttpSocket'),
			array($this->defaultConfig)
		);
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('post', 'body')
		);
		$query = null;
		$msg = 'The API response is not valid.';

		$this->EwsLabelService->expects($this->once())
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

		$data = array(
			'api' => array(
				'requestKey' => 'foo',
				'requestEndpoint' => 'bar',
			),
			'xml' => array()
		);
		$this->setExpectedException('BadRequestException', $msg);
		$result = $this->EwsLabelService->makeRequest($data);
	}

	/**
	 * Confirm the correct exception is thrown if the HttpSocket::post request
	 * fails.
	 *
	 * @return void
	 */
	public function testMakeRequestFails() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array('initHttpSocket'),
			array($this->defaultConfig)
		);
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('post', 'body')
		);
		$msg = 'Missing or invalid response from the API server.';

		$this->EwsLabelService->expects($this->once())
			->method('initHttpSocket')
			->with()
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('post')
			->with()
			->will($this->throwException(new Exception));

		$data = array(
			'api' => array(
				'requestKey' => 'foo',
				'requestEndpoint' => 'bar',
			),
			'xml' => array()
		);
		$this->setExpectedException('NotFoundException', $msg);
		$result = $this->EwsLabelService->makeRequest($data);
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
		$result = $this->EwsLabelService->processResponse($response);
	}

	/**
	 * Confirm that valid XML causes processResponse to call checkResponse
	 *
	 * @return void
	 */
	public function testProcessResponseValidXml() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array('checkResponse'),
			array($this->defaultConfig)
		);
		$response = '<?xml version="1.0" encoding="UTF-8"?><ChangePassPhraseRequest TokenRequested="false"><RequesterID>123</RequesterID><RequestID>test</RequestID>></ChangePassPhraseRequest>';

		$this->EwsLabelService->expects($this->once())
			->method('checkResponse')
			->with()
			->will($this->returnValue($response));
		$result = $this->EwsLabelService->processResponse($response);
		$this->assertEquals($response, $result);
	}

	/**
	 * Confirm that an exception is thrown with the expected error message if
	 * submitted $data is missing any required keys.
	 *
	 * @dataProvider provideApiRequestInvalidData
	 * @return void
	 */
	public function testApiRequestInvalidData($data, $msg) {
		$this->setExpectedException('BadMethodCallException', $msg);
		$result = $this->EwsLabelService->apiRequest($data);
	}

	public function provideApiRequestInvalidData() {
		$msg1 = 'Missing required [api] or [api method] data keys.';
		$msg2 = 'Missing required [api.requestKey] or [api.requestEndpoint] data keys.';
		return array(
			array(
				array(),
				$msg1
			),
			array(
				array(
					'api' => array('foo' => 'bar'),
				),
				$msg1
			),
			array(
				array(
					'anything' => array('foo' => 'bar'),
				),
				$msg1
			),
			array(
				array(
					'api' => array('requestKey' => 'foo', 'requestEndpoint' => 'bar'),
				),
				$msg1
			),
			array(
				array(
					'api' => array('foo' => 'bar'),
					'apiMethod' => array('any' => 'thing'),
				),
				$msg2
			),
			array(
				array(
					'api' => array('requestKey' => 'foo', 'invalid' => 'bar'),
					'apiMethod' => array('any' => 'thing'),
				),
				$msg2
			),
			array(
				array(
					'api' => array('invalid' => 'foo', 'requestEndpoint' => 'bar'),
					'apiMethod' => array('any' => 'thing'),
				),
				$msg2
			),
		);
	}

	/**
	 * Confirm apiRequest calls it's expected methods when supplied with valid data.
	 *
	 * @return void
	 */
	public function testApiRequest() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array(
				'prepareRequest',
				'makeRequest',
			),
			array($this->defaultConfig)
		);

		$data = array(
			'PostageRatesResponse' => array(
				'Status' => '0',
				'PostagePrice' => '',
			),
		);

		$this->EwsLabelService->expects($this->once())
			->method('prepareRequest')
			->with()
			->will($this->returnValue($data));
		$this->EwsLabelService->expects($this->once())
			->method('makeRequest')
			->with()
			->will($this->returnValue($data));
		$result = $this->EwsLabelService->apiRequest($this->defaultData);
		$this->assertEquals($data, $result);
	}

	/**
	 * Confirm that size is calculated correctly when supplied with valid data.
	 *
	 * @dataProvider provideCalculateSize
	 * @return void
	 */
	public function testCalculateSize($height, $length, $width, $expected) {
		$result = $this->EwsLabelService->calculateSize($height, $length, $width);
		$this->assertEquals($expected, $result);
	}

	public function provideCalculateSize() {
		return array(
			array(2, 4, 12, 'Parcel'),
			array(200, 40, 480, 'LargeParcel'),
			array(1, 2, 1, 'Parcel'),
			array(13, 1, 1, 'LargeParcel'),
			array(null, null, null, 'Parcel'),
			array(null, null, 13, 'LargeParcel'),
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
		$result = $this->EwsLabelService->filterRates($rates, $classes);
		$this->assertEquals($expected, $result);
	}

	public function provideFilterRates() {
		$rates = array(
			'PostageRatesResponse' => array(
				'Status' => '0',
				'PostagePrice' => array(
					(int) 0 => array(
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
					(int) 1 => array(
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
					(int) 2 => array(
						'@TotalAmount' => '3.07',
						'MailClass' => 'LibraryMail',
						'Postage' => array(
							'@TotalAmount' => '3.07',
							'MailService' => 'Library Mail',
							'Zone' => '5',
							'IntraBMC' => 'false',
							'Pricing' => 'Retail'
						),
						'Fees' => array(
							'@TotalAmount' => '0',
							'CertificateOfMailing' => '0',
							'CertifiedMail' => '0',
							'CollectOnDelivery' => '0',
						),
						'DdpRate' => '0'
					),
				)
			)
		);
		return array(
			array(
				$rates,
				array('PriorityExpress', 'LibraryMail'),
				array(
					$rates['PostageRatesResponse']['PostagePrice'][1],
					$rates['PostageRatesResponse']['PostagePrice'][2],
				),
			),
			array(
				$rates,
				array(
					'Priority',
					'PriorityExpress',
					'ParcelSelect',
					'MediaMail',
					'LibraryMail',
					'FooBar',
				),
				array(
					$rates['PostageRatesResponse']['PostagePrice'][0],
					$rates['PostageRatesResponse']['PostagePrice'][1],
					$rates['PostageRatesResponse']['PostagePrice'][2],
				),
			),
			array(
				$rates,
				null,
				array(
					$rates['PostageRatesResponse']['PostagePrice'][0],
					$rates['PostageRatesResponse']['PostagePrice'][1],
					$rates['PostageRatesResponse']['PostagePrice'][2],
				),
			),
		);
	}

	/**
	 * Confirm that the correct error exception is thrown depending on what the
	 * error code is or if it's missing.
	 *
	 * @dataProvider provideCheckResponseWithErrors
	 * @return void
	 */
	public function testCheckResponseWithErrors($response, $expectedMsg) {
		$this->setExpectedException('BadRequestException', $expectedMsg);
		$result = $this->EwsLabelService->checkResponse($response);
	}

	public function provideCheckResponseWithErrors() {
		return array(
			array(
				array('PostageRatesResponse' => array(
					'Status' => '60502',
					'ErrorMessage' => 'Error 1',
				)),
				'Error 1',
			),
			array(
				array('PostageRatesResponse' => array(
					'Status' => 'foo',
					'ErrorMessage' => 'Error 2',
				)),
				'Error 2',
			),
			array(
				array('PostageRatesResponse' => array(
					'Status' => '',
					'ErrorMessage' => 'Error 3',
				)),
				'Error 3',
			),
			array(
				array('PostageRatesResponse' => array(
					'Status' => array('data'),
					'ErrorMessage' => 'Error 4',
				)),
				'Error 4',
			),
			array(
				array('PostageRatesResponse' => array(
					'ErrorMessage' => 'Error 5',
				)),
				'Error 5',
			),
			array(
				array(),
				'Unknown API Error',
			),
		);
	}

	/**
	 * Confirm that if the response status code is `0` checkResponse returns
	 * supplied data unmodified.
	 *
	 * @return void
	 */
	public function testCheckResponse() {
		$response = array('PostageRatesResponse' => array(
			'Status' => '0',
			'Foo' => 'Bar',
		));
		$result = $this->EwsLabelService->checkResponse($response);
		$this->assertEquals($response, $result);
	}

	/**
	 * Confirm that label data can be extracted and processed with all expected
	 * methods called, and that the result is the full path to the file.
	 *
	 * @return void
	 */
	public function testSaveLabelImageNotNested() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array(
				'decode',
				'writeFile',
			),
			array($this->defaultConfig)
		);
		$this->EwsLabelService->expects($this->once())
			->method('decode')
			->with()
			->will($this->returnValue(true));
		$this->EwsLabelService->expects($this->once())
			->method('writeFile')
			->with()
			->will($this->returnValue(true));
		$response = array(
			'LabelRequestResponse' => array(
				'TrackingNumber' => '1234567',
				'Base64LabelImage' => 'foobar',
			),
		);
		$result = $this->EwsLabelService->saveLabelImage($response, '/var/www/tmp/tests');
		$this->assertEquals('1234567.pdf', $result);
	}

	/**
	 * Confirm that label data can be extracted and processed when nested with
	 * all expected methods called, and that the result is the full path to the file.
	 *
	 * @return void
	 */
	public function testSaveLabelImageNested() {
		$this->EwsLabelService = $this->getMock('TestEwsLabelService',
			array(
				'decode',
				'writeFile',
			),
			array($this->defaultConfig)
		);
		$this->EwsLabelService->expects($this->once())
			->method('decode')
			->with()
			->will($this->returnValue(true));
		$this->EwsLabelService->expects($this->once())
			->method('writeFile')
			->with()
			->will($this->returnValue(true));
		$response = array(
			'LabelRequestResponse' => array(
				'TrackingNumber' => '7654321',
				'Label' => array(
					'Image' => array(
						'@' => 'foobar',
					),
				)
			),
		);
		$result = $this->EwsLabelService->saveLabelImage($response, '/var/www/tmp/tests');
		$this->assertEquals('7654321.pdf', $result);
	}

	/**
	 * Confirm the method returns false if $path is missing.
	 *
	 * @return void
	 */
	public function testSaveLabelInvalidPath() {
		$msg = 'The label path is not writable.';
		$this->setExpectedException('BadMethodCallException', $msg);
		$result = $this->EwsLabelService->saveLabelImage(null, null);
	}

	/**
	 * Confirm that decode properly base64_decodes a string with no $type set.
	 *
	 * @return void
	 */
	public function testDecodeWithValidType() {
		$data = 'foobar';
		$expected = base64_decode($data, true);
		$result = $this->EwsLabelService->decode($data);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm that decode returns an unmodified string if no supported decode
	 * $type was supplied.
	 *
	 * @return void
	 */
	public function testDecodeWithInvalidType() {
		$data = 'foobar';
		$expected = $data;
		$result = $this->EwsLabelService->decode($data, 'foo');
		$this->assertEquals($expected, $result);
	}
}
