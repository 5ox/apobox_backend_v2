<?php
App::uses('OrderTotal', 'Model');

/**
 * OrderTotal Test Case
 *
 */
class OrderTotalTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order_total',
		'app.order',
		'app.customer',
		'app.address',
		'app.zone',
		'app.password_request',
		'app.order',
		'app.order_status',
		'app.order_status_history',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->OrderTotal = ClassRegistry::init('OrderTotal');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->OrderTotal);

		parent::tearDown();
	}

	/**
	 * Confirm that updateTotal calculates a total correctly.
	 *
	 * @return	void
	 */
	public function testCalculateTotal() {
		$this->OrderTotal->id = 1;
		$orderId = 1;
		$expectation = 113.600;
		$result = $this->OrderTotal->calculateTotal($orderId);

		$this->assertEquals($expectation, $result);
	}

	/**
	 * Confirm that updateTotal updates a total correctly.
	 *
	 * @return	void
	 */
	public function testUpdateTotal() {
		$this->OrderTotal->id = 2;
		$orderId = 2;
		$expectation = 21.75;
		$result = $this->OrderTotal->updateTotal($orderId);

		$this->assertEquals($expectation, $result);
	}

	/**
	 * Confirm that findAllChargesForOrderId returns the expected number of
	 * rows, and that the rows contain the expected array key.
	 *
	 * @return	void
	 */
	public function testFindAllChargesForOrderId() {
		$orderId = 1;
		$rowCount = 7;
		$result = $this->OrderTotal->findAllChargesForOrderId($orderId);
		$this->assertInternalType('array', $result);
		$this->assertArrayHasKey('OrderTotal', $result[0]);
		$this->assertEquals($rowCount, count($result));
	}

	/**
	 * Confirm that addFeeIfMissing() does not modify the charges when an `ot_fee`
	 * row exists.
	 *
	 * @return void
	 */
	public function testAddFeeIfMissingWhenFeeExists() {
		$charges = $this->OrderTotal->findAllChargesForOrderId(1);
		$result = $this->OrderTotal->addFeeIfMissing($charges);
		$this->assertEquals($charges, $result);
	}

	/**
	 * Confirm that addFeeIfMissing() adds the required fee key/val pairs to the
	 * charges array if `ot_fee` is missing. Note the negative fee is a result
	 * of the fixture data not being typical. It has an extra 23 shipping
	 * record that isn't accounted for in the total. Otherwise the fee would
	 * be 10.95 as expected.
	 *
	 * @return void
	 */
	public function testAddFeeIfMissingWhenFeeIsMissing() {
		$charges = $this->OrderTotal->findAllChargesForOrderId(1);
		$this->assertArrayHasKey(4, $charges);
		$this->assertEquals('41231231', $charges[4]['OrderTotal']['orders_total_id']);
		$this->assertEquals('1', $charges[4]['OrderTotal']['orders_id']);
		$this->assertEquals('$10.95', $charges[4]['OrderTotal']['text']);
		$this->assertEquals('ot_fee', $charges[4]['OrderTotal']['class']);

		unset($charges[4]);
		$this->assertArrayNotHasKey(4, $charges);

		$result = $this->OrderTotal->addFeeIfMissing($charges);
		$this->assertArrayHasKey(4, $result);
		$this->assertEquals('000000', $result[4]['OrderTotal']['orders_total_id']);
		$this->assertEquals('00000000', $result[4]['OrderTotal']['orders_id']);
		$this->assertEquals('$-12.05', $result[4]['OrderTotal']['text']);
		$this->assertEquals('ot_fee', $result[4]['OrderTotal']['class']);
	}

	/**
	 * Confirm that the proper fee is calculated. Note the negative fee is a
	 * result of the fixture data not being typical. It has an extra 23 shipping
	 * record that isn't accounted for in the total. Otherwise the fee would
	 * be 10.95 as expected.
	 *
	 * @return void
	 */
	public function testCalculateFee() {
		$charges = $this->OrderTotal->findAllChargesForOrderId(1);
		$this->assertSame(-12.05, $this->OrderTotal->calculateFee($charges));
	}
}
