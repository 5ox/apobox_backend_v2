<?php
App::uses('OrderData', 'Model');

/**
 * OrderData Test Case
 *
 */
class OrderDataTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order_data',
		'app.order',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->OrderData = ClassRegistry::init('OrderData');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->OrderData);

		parent::tearDown();
	}

	/**
	 * Confirm that an OrderData record can be saved.
	 *
	 * @return void
	 */
	public function testSaveOrderData() {
		$orderId = 2;
		$before = $this->OrderData->findAllByOrdersId($orderId);
		$this->assertEquals(1, count($before));

		$result = $this->OrderData->saveOrderData($orderId, 'fedex-zpl', 'raw zpl data');
		$this->assertTrue($result);
		$after = $this->OrderData->findAllByOrdersId($orderId);
		$this->assertEquals(count($before) + 1, count($after));
	}

	/**
	 * Confirm that the expected record data can be fetched by order ID and
	 * data type.
	 *
	 * @return void
	 */
	public function testFetchOrderData() {
		$orderId = 2;
		$expected = 'raw zpl data';
		$result = $this->OrderData->fetchOrderData($orderId, 'fedex-zpl');
		$this->assertSame($expected, $result);
	}

	/**
	 * Confirm that null is returned if an invalid or not found data type is
	 * requested.
	 *
	 * @return void
	 */
	public function testFetchOrderDataNoRecord() {
		$orderId = 2;
		$result = $this->OrderData->fetchOrderData($orderId, 'foo');
		$this->assertSame(null, $result);
	}

	/**
	 * Confirm that bool true is returned if a valid order ID and data type are
	 * requested.
	 *
	 * @return void
	 */
	public function testCheckOrderData() {
		$orderId = 2;
		$result = $this->OrderData->checkOrderData($orderId, 'fedex-zpl');
		$this->assertTrue($result);
	}

	/**
	 * Confirm that bool false is returned if an invalid or not found data type is
	 * requested.
	 *
	 * @return void
	 */
	public function testCheckOrderDataNoRecord() {
		$orderId = 'foo';
		$result = $this->OrderData->checkOrderData($orderId, 'fedex-zpl');
		$this->assertFalse($result);
	}

	/**
	 * Confirm that an OrderData record can be removed by order ID and data type.
	 *
	 * @return void
	 */
	public function testClearOrderData() {
		$orderId = 2;
		$before = $this->OrderData->findAllByOrdersId($orderId);
		$this->assertEquals(1, count($before));

		$result = $this->OrderData->clearOrderData($orderId, 'fedex-zpl');
		$this->assertTrue($result);
		$after = $this->OrderData->findAllByOrdersId($orderId);
		$this->assertEquals(count($before) - 1, count($after));
	}
}
