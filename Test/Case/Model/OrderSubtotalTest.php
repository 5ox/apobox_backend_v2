<?php
App::uses('OrderSubtotal', 'Model');

/**
 * OrderSubtotal Test Case
 *
 */
class OrderSubtotalTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = [
		'app.order_total',
	];

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->OrderSubtotal = ClassRegistry::init('OrderSubtotal');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->OrderSubtotal);

		parent::tearDown();
	}

	/**
	 * Confirm that updateTotal calculates a total correctly.
	 *
	 * @return	void
	 */
	public function testCalculateTotal() {
		$this->OrderSubtotal->id = 1;
		$orderId = 1;
		$expectation = 113.600;
		$result = $this->OrderSubtotal->calculateTotal($orderId);

		$this->assertEquals($expectation, $result);
	}

	/**
	 * Confirm that updateTotal updates a total correctly.
	 *
	 * @return	void
	 */
	public function testUpdateTotal() {
		$this->OrderSubtotal->id = 2;
		$orderId = 2;
		$expectation = 21.75;
		$result = $this->OrderSubtotal->updateTotal($orderId);

		$this->assertEquals($expectation, $result);
	}
}
