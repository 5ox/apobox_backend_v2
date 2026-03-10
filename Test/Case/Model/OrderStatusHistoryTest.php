<?php
App::uses('OrderStatusHistory', 'Model');

/**
 * OrderStatusHistory Test Case
 *
 */
class OrderStatusHistoryTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order_status_history',
		'app.order',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->OrderStatusHistory = ClassRegistry::init('OrderStatusHistory');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->OrderStatusHistory);

		parent::tearDown();
	}

	/**
	 * Confirm that beforeValidate formats the 'date_added' field if it exists
	 * to the proper format for the db.
	 *
	 * @return	void
	 */
	public function testBeforeValidateFormatDate() {
		$data = array();
		$this->assertEmpty($data);

		$this->OrderStatusHistory->set($data);
		$orderStatusHistory = $this->OrderStatusHistory->beforeValidate();

		$result = $this->OrderStatusHistory->data;
		$this->assertArrayHasKey('date_added', $result['OrderStatusHistory']);
		$this->assertRegexp(
			'/^\d\d\d\d-(\d)?\d-(\d)?\d \d\d:\d\d:\d\d$/',
			$result['OrderStatusHistory']['date_added']
		);
	}

	/**
	 * Confirm that findCurrentStatusForOrderId() returns expected results with
	 * or without an $id passed to it.
	 *
	 * @dataProvider provideFindCurrentStatusForOrder
	 *
	 * @return	void
	 */
	public function testFindCurrentStatusForOrderId($id, $pass, $hasResults) {
		$passId = ($pass ? $id : null);
		if (!$pass) {
			$this->OrderStatusHistory->id = $id;
		}
		$result = $this->OrderStatusHistory->findCurrentStatusForOrderId($passId);
		if ($hasResults) {
			$this->assertArrayHasKey('OrderStatusHistory', $result);
			$this->assertArrayHasKey('orders_id', $result['OrderStatusHistory']);
			$this->assertEquals($id, $result['OrderStatusHistory']['orders_id']);
		} else {
			$this->assertEmpty($result);
		}
	}

	public function provideFindCurrentStatusForOrder() {
		return array(
			array(1, false, true),
			array(1, true, true),
			array(2, true, false),
		);
	}

	/**
	 * Confirm that findCurrentStatusForOrderId() correctly merges passed
	 * options with it's default options
	 *
	 * @return	void
	 */
	public function testFindCurrentStatusForOrderIdWithOptions() {
		$id = 1;
		$options = array(
			'fields' => array(
				'OrderStatusHistory.orders_id',
			),
		);

		$result = $this->OrderStatusHistory->findCurrentStatusForOrderId($id, $options);
		$this->assertArrayHasKey('OrderStatusHistory', $result);
		$this->assertArrayHasKey('orders_id', $result['OrderStatusHistory']);
		$this->assertEquals($id, $result['OrderStatusHistory']['orders_id']);
		$this->assertArrayNotHasKey('customer_notified', $result['OrderStatusHistory']);
	}

	/**
	 * Confirm that findStatusesForOrderId() returns expected results with
	 * or without an $id passed to it.
	 *
	 * @dataProvider provideFindStatusesForOrder
	 *
	 * @return	void
	 */
	public function testFindStatusesForOrderId($id, $pass, $hasResults) {
		$passId = ($pass ? $id : null);
		if (!$pass) {
			$this->OrderStatusHistory->id = $id;
		}
		$result = $this->OrderStatusHistory->findStatusesForOrderId($passId);
		if ($hasResults) {
			$this->assertArrayHasKey('OrderStatusHistory', $result[0]);
			$this->assertArrayHasKey('orders_id', $result[1]['OrderStatusHistory']);
			$this->assertEquals($id, $result[0]['OrderStatusHistory']['orders_id']);
		} else {
			$this->assertEmpty($result);
		}
	}

	public function provideFindStatusesForOrder() {
		return array(
			array(1, false, true),
			array(1, true, true),
			array(2, true, false),
		);
	}

	/**
	 * Confirm that findStatusesForOrderId() correctly merges passed
	 * options with it's default options
	 *
	 * @return	void
	 */
	public function testFindStatusForOrderIdWithOptions() {
		$id = 1;
		$options = array(
			'fields' => array(
				'OrderStatusHistory.orders_id',
			),
		);

		$result = $this->OrderStatusHistory->findStatusesForOrderId($id, $options);
		$this->assertArrayHasKey('OrderStatusHistory', $result[0]);
		$this->assertArrayHasKey('orders_id', $result[1]['OrderStatusHistory']);
		$this->assertEquals($id, $result[0]['OrderStatusHistory']['orders_id']);
		$this->assertArrayNotHasKey('customer_notified', $result[0]['OrderStatusHistory']);
	}

	/**
	 * Confirm the method calls `record` with the expected arguments.
	 *
	 * @return void
	 */
	public function testRecordChargeFailed() {
		$order = ['Order' => ['orders_id' => 12345]];
		$expected = ['Order' => [
			'orders_id' => 12345,
			'status_history_comments' => 'charge failed, email sent',
			'notify_customer' => 1,
			'orders_status' => 2,
		]];
		$Model = $this->getMockForModel('OrderStatusHistory', ['record']);

		$Model->expects($this->once())
			->method('record')
			->with($this->identicalTo($expected))
			->will($this->returnValue(true));

		$result = $Model->recordChargeFailed($order);

		$this->assertTrue($result);
	}

	/**
	 * Confirm the method calls `save` with the expected arguments.
	 *
	 * @return void
	 */
	public function testRecord() {
		$ordersId = 12345;
		$statusId = 1;
		$comment = 'foo';
		$order = ['Order' => [
			'orders_id' => $ordersId,
			'orders_status' => $statusId,
			'status_history_comments' => $comment,
			'notify_customer' => 1,
		]];
		$expected = ['OrderStatusHistory' => [
			'orders_id' => $ordersId,
			'orders_status_id' => $statusId,
			'comments' => $comment,
			'customer_notified' => true,
		]];
		$Model = $this->getMockForModel('OrderStatusHistory', ['create', 'save']);

		$Model->expects($this->once())
			->method('create');
		$Model->expects($this->once())
			->method('save')
			->with($this->identicalTo($expected))
			->will($this->returnValue(true));

		$result = $Model->record($order);

		$this->assertTrue($result);
	}
}
