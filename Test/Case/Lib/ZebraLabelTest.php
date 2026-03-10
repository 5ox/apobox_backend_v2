<?php
App::uses('ZebraLabel', 'Lib');

/**
 * TestZebraLabel - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestZebraLabel extends ZebraLabel {

	public function generatePngImage($data) {
		return parent::generatePngImage($data);
	}
	public function generateLabel($image) {
		return parent::generateLabel($image);
	}
	public function initImage($data) {
		return parent::initImage($data);
	}
	public function initBuilder() {
		return parent::initBuilder();
	}
	public function initClient($client) {
		return parent::initClient($client);
	}
}

/**
 * ZebraLabel Test Case
 *
 */
class ZebraLabelTest extends CakeTestCase {

	public $data = [
		'header' => [
			'size' => 10,
			'content' => 'lorem ipsum',
		],
		'body' => [
			'size' => 10,
			'content' => 'lorem ipsum',
		],
		'footer' => [
			'size' => 10,
			'content' => 'lorem ipsum',
		],
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = [
			'method' => 'serial',
		];
		$this->ZebraLabel = new TestZebraLabel($config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ZebraLabel);
		parent::tearDown();
	}

	/**
	 * Confirm an instance of class Zebra\Zpl\Image is created by initImage()
	 *
	 * @return void
	 */
	public function testInitImage() {
		$image = file_get_contents(WWW_ROOT . Configure::read('App.imageBaseUrl') . 'cake.icon.png');
		$this->assertInstanceOf('Zebra\Zpl\Image', $this->ZebraLabel->initImage($image));
	}

	/**
	 * Confirm an instance of class Zebra\Zpl\Builder is created by initBuilder()
	 *
	 * @return void
	 */
	public function testInitBuilder() {
		$this->assertInstanceOf('Zebra\Zpl\Builder', $this->ZebraLabel->initBuilder());
	}

	/**
	 * Confirm the expected exception is thrown if [method] config key is missing
	 *
	 * @return void
	 */
	public function testConstructorMissingMethodKey() {
		unset($this->ZebraLabel);
		$this->setExpectedException('BadMethodCallException', 'Missing required [method] config key.');
		$this->ZebraLabel = new TestZebraLabel();
	}

	/**
	 * Confirm that the expected exception is thrown if [method] config key is set to
	 * 'network' and [client] config key is missing.
	 *
	 * @return void
	 */
	public function testConstructorMissingClientKey() {
		unset($this->ZebraLabel);
		$config = [
			'method' => 'network',
		];
		$this->setExpectedException('BadMethodCallException', 'Missing required [client] config key.');
		$this->ZebraLabel = new TestZebraLabel($config);
	}

	/**
	 * Confirm that the expected exception is thrown if printLabel() is called with
	 * missing or invalid data.
	 *
	 * @return void
	 */
	public function testPrintLabelMissingDataKeys() {
		$data = [];
		$this->setExpectedException('BadMethodCallException', 'Missing one or more required data keys');
		$this->ZebraLabel->printLabel($data);
	}

	/**
	 * Confirm that printLabel() will return the mocked result of generatePngImage
	 * if the $print arguement is set to false.
	 *
	 * @return void
	 */
	public function testPrintLabelReturnImage() {
		$ZebraLabel = $this->getMockBuilder('TestZebraLabel')
			->setMethods(['generatePngImage'])
			->disableOriginalConstructor()
			->getMock();
		$ZebraLabel->expects($this->once())
			->method('generatePngImage')
			->will($this->returnValue('raw image data'));
		$result = $ZebraLabel->printLabel($this->data, true);
		$this->assertSame('raw image data', $result);
	}

	/**
	 * Confirm printLabel() calls the expected internal methods, does not
	 * throw an exception, and returns true when constructor method is not
	 * `network`.
	 *
	 * @return void
	 */
	public function testPrintLabelNotNetwork() {
		$ZebraLabel = $this->getMockBuilder('TestZebraLabel')
			->setMethods(['generatePngImage', 'generateLabel'])
			->setConstructorArgs([['method' => 'serial']])
			->getMock();
		$ZebraLabel->expects($this->once())
			->method('generatePngImage')
			->will($this->returnValue('raw image data'));
		$ZebraLabel->expects($this->once())
			->method('generateLabel')
			->will($this->returnValue('zpl data'));
		$result = $ZebraLabel->printLabel($this->data);
		$this->assertSame('zpl data', $result);
	}

	/**
	 * Confirm printLabel() calls the expected internal methods when using
	 * 'network' method, does not throw an exception, and returns true.
	 *
	 * @return void
	 */
	public function testPrintLabelNetwork() {
		$ZebraLabel = $this->getMockBuilder('TestZebraLabel')
			->setMethods(['generatePngImage', 'generateLabel', 'initClient'])
			->setConstructorArgs([['method' => 'network', 'client' => 'foo']])
			->getMock();
		$Client = $this->getMockBuilder('\Zebra\Client')
			->setMethods(['send'])
			->disableOriginalConstructor()
			->getMock();
		$ZebraLabel->expects($this->once())
			->method('generatePngImage')
			->will($this->returnValue('raw image data'));
		$ZebraLabel->expects($this->once())
			->method('generateLabel')
			->will($this->returnValue('zpl data'));
		$ZebraLabel->expects($this->once())
			->method('initClient')
			->will($this->returnValue($Client));
		$Client->expects($this->once())
			->method('send')
			->will($this->returnValue(true));
		$result = $ZebraLabel->printLabel($this->data);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that all expected internal methods are called and that generateLabel()
	 * returns an object.
	 *
	 * @return void
	 */
	public function testGenerateLabel() {
		$ZebraLabel = $this->getMockBuilder('TestZebraLabel')
			->setMethods(['initImage', 'initBuilder'])
			->disableOriginalConstructor()
			->getMock();
		$Image = $this->getMockBuilder('\Zebra\Zpl\Image')
			->disableOriginalConstructor()
			->getMock();
		$Builder = $this->getMockBuilder('\Zebra\Zpl\Builder')
			->disableOriginalConstructor()
			->setMethods(['fo', 'gf', 'fs'])
			->getMock();
		$ZebraLabel->expects($this->once())
			->method('initImage')
			->will($this->returnValue($Image));
		$ZebraLabel->expects($this->once())
			->method('initBuilder')
			->will($this->returnValue($Builder));
		$Builder->expects($this->once())
			->method('fo')
			->will($this->returnValue('one'));
		$Builder->expects($this->once())
			->method('gf')
			->will($this->returnValue('two'));
		$Builder->expects($this->once())
			->method('fs')
			->will($this->returnValue('three'));
		$result = $ZebraLabel->generateLabel('foo');
		$this->assertInternalType('object', $result);
	}

	/**
	 * Confirm that generatePngImage() will return non-printable characters
	 * that are hopefully an image.
	 *
	 * @return void
	 */
	public function testGeneratePngImage() {
		$result = $this->ZebraLabel->generatePngImage($this->data);
		$this->assertFalse(ctype_print($result));
	}

	/**
	 * Confirm initClient() throws an exception from the `Zebra` namespace.
	 *
	 * @return void
	 */
	public function testInitClient() {
		$this->setExpectedException('Zebra\CommunicationException');
		$result = $this->ZebraLabel->initClient('foo');
	}
}
