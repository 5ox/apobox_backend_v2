<?php
App::uses('Controller', 'Controller');
App::uses('View', 'Core');
App::uses('ReportHelper', 'View/Helper');

/**
 * Class: ReportHelperTest
 *
 * @see CakeTestCase
 */
class ReportHelperTest extends CakeTestCase {

	/**
	 * ReportHelper
	 *
	 * @var mixed
	 */
	public $ReportHelper = null;

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$Controller = new Controller();
		$this->View = $View = new View($Controller);
		$this->ReportHelper = new ReportHelper($View);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ReportHelper);
		parent::tearDown();
	}

	/**
	 * Confirm that formatPrice() correctly formats the supplied numbers
	 * correctly as currency.
	 *
	 * @dataProvider provideFormatPrice
	 * @return void
	 */
	public function testFormatPrice($price, $expected) {
		$result = $this->ReportHelper->formatPrice($price);
		$this->assertEquals($expected, $result);
	}

	public function provideFormatPrice() {
		return array(
			array('12.99', '$12.99'),
			array('12.999999', '$13.00'),
			array('12', '$12.00'),
			array('12345.69', '$12,345.69'),
			array('foo', '$0.00'),
			array(null, '$0.00'),
		);
	}
}
