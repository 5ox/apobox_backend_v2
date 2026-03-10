<?php
App::uses('Order', 'Model');

/**
 * TestOrder - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestOrder extends Order {
	public function _findAwaitingPayments($state, $query, $results = []) {
		return parent::_findAwaitingPayments($state, $query, $results);
	}
	public function packageSize($order) {
		return parent::packageSize($order);
	}
}

/**
 * Order Test Case
 *
 */
class OrderTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order',
		'app.order_status',
		'app.order_status_history',
		'app.customer',
		'app.order_total',
		'app.address',
		'app.country',
		'app.insurance',
		'app.customer_reminder',
		'app.custom_order',
		'app.order_data',
		'app.tracking',
		'app.queued_task',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->mockDatetime = '2015-06-05 12:34:56';
		$this->Order = ClassRegistry::init('Order');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Order);

		parent::tearDown();
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsWarehouse() {
		$id = 3;
		$expectedStatusId = '1';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = $id;
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsWarehouse();

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsWarehouse() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status']);
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsWarehouseInjectingOrderId() {
		$id = 3;
		$expectedStatusId = '1';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsWarehouse($id, array('orders_id' => $this->Order->id));

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsWarehouse() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsWarehouseRecordNotExists() {
		$id = 3;
		$this->Order = $this->getMockForModel('Order', array('exists'));
		$this->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));
		$this->Order->id = $id;

		$save = $this->Order->markAsWarehouse();

		$this->assertTrue($save === false, 'Should return false if record does not exist.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsAwaitingPayment() {
		$id = 1;
		$expectedStatusId = '2';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = $id;
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsAwaitingPayment();

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsAwaitingPayment() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status']);
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsAwaitingPaymentInjectingOrderId() {
		$id = 1;
		$expectedStatusId = '2';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsAwaitingPayment($id);

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsAwaitingPayment() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsAwaitingPaymentRecordNotExists() {
		$id = 1;
		$this->Order = $this->getMockForModel('Order', array('exists'));
		$this->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));
		$this->Order->id = $id;

		$save = $this->Order->markAsAwaitingPayment();

		$this->assertTrue($save === false, 'Should return false if record does not exist.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsShipped() {
		$id = 1;
		$expectedStatusId = '3';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = $id;
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsShipped();

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsShipped() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status']);
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsShippedInjectingOrderId() {
		$id = 1;
		$expectedStatusId = '3';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsShipped($id);

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsShipped() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsShippedRecordNotExists() {
		$id = 1;
		$this->Order = $this->getMockForModel('Order', array('exists'));
		$this->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));
		$this->Order->id = $id;

		$save = $this->Order->markAsShipped();

		$this->assertTrue($save === false, 'Should return false if record does not exist.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsManuallyPaid() {
		$id = 1;
		$expectedStatusId = '4';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = $id;
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsManuallyPaid();

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsManuallyPaid() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status']);
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsManuallyPaidInjectingOrderId() {
		$id = 1;
		$expectedStatusId = '4';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsManuallyPaid($id);

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsManuallyPaid() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsManuallyPaidRecordNotExists() {
		$id = 1;
		$this->Order = $this->getMockForModel('Order', array('exists'));
		$this->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));
		$this->Order->id = $id;

		$save = $this->Order->markAsManuallyPaid();

		$this->assertTrue($save === false, 'Should return false if record does not exist.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsReturned() {
		$id = 1;
		$expectedStatusId = '5';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = $id;
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsReturned();

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsReturned() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status']);
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsReturnedInjectingOrderId() {
		$id = 1;
		$expectedStatusId = '5';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);

		$save = $this->Order->markAsReturned($id);

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);
		$this->assertTrue($save != false, 'markAsReturned() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsReturnedRecordNotExists() {
		$id = 1;
		$this->Order = $this->getMockForModel('Order', array('exists'));
		$this->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));
		$this->Order->id = $id;

		$save = $this->Order->markAsReturned();

		$this->assertTrue($save === false, 'Should return false if record does not exist.');
	}

	protected function getDatetimeMockedModel() {
		$this->datetime = '2015-03-15 12:34:56';
		$model = $this->getMockForModel('Order', array('getDatetime'));
		$model->expects($this->any())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->datetime)));
		return $model;
	}

	/**
	 * testSaveUpdatingStatus method
	 *
	 * @return	void
	 */
	public function testSaveUpdatingStatus() {
		$orderId = 1;
		$newStatusId = 3;
		$newComment = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ante tortor, accumsan sit amet scelerisque eu, interdum quis felis. Ut maximus et orci sit amet tincidunt. Proin placerat dapibus sapien, in eleifend diam viverra eget. Vivamus massa nunc.';
		$data = array('Order' => array(
			'orders_id' => $orderId,
			'orders_status' => $newStatusId,
			'status_history_comments' => $newComment,
		));
		$this->Order->OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', array('getDatetime'));
		$this->Order->OrderStatusHistory->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->mockDatetime)));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');

		$result = $this->Order->save($data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$this->assertEquals(($orderHistoryCountBefore+1), $orderHistoryCountAfter, 'Should create exactly one order_status_history record.');

		$this->assertEquals($orderId, $oSH['OrderStatusHistory']['orders_id'], 'orders_id not saved correctly.');
		$this->assertEquals($newStatusId, $oSH['OrderStatusHistory']['orders_status_id'], 'orders_status_id not saved correctly.');
		$this->assertEquals($newComment, $oSH['OrderStatusHistory']['comments'], '`status_history_comments` from Order should be saved to OrderHistoryStatus.');
		$this->assertEquals($this->mockDatetime, $oSH['OrderStatusHistory']['date_added'], 'date_added not saved correctly');
		$this->assertSame('0', $oSH['OrderStatusHistory']['customer_notified'], 'Should set customer_notified to 0');

		$this->assertEquals($data['Order']['orders_id'], $result['Order']['orders_id']);
		$this->assertEquals($data['Order']['orders_status'], $result['Order']['orders_status']);
		$this->assertArrayNotHasKey('comments', $result['Order']);
	}

	/**
	 * testSaveWithoutUpdatingStatus method
	 *
	 * @return	void
	 */
	public function testSaveWithoutUpdatingStatus() {
		$orderId = 1;
		$data = array('Order' => array(
			'orders_id' => $orderId,
			'cc_name' => 'First Last',
		));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');

		$result = $this->Order->save($data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$this->assertEquals($orderHistoryCountBefore, $orderHistoryCountAfter, 'Should not create or delete order_status_history records.');

		$this->assertEquals($data['Order']['cc_name'], $result['Order']['cc_name'], 'Order record shoudl still update.');
	}

	/**
	 * testSaveUpdatingStatusOnlyComment method
	 *
	 * @return	void
	 */
	public function testSaveUpdatingStatusOnlyComment() {
		$orderId = 1;
		$newComment = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
		$data = array('Order' => array(
			'orders_id' => $orderId,
			'status_history_comments' => $newComment,
		));
		$this->Order->OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', array('getDatetime'));
		$this->Order->OrderStatusHistory->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->mockDatetime)));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');

		$result = $this->Order->save($data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$this->assertEquals(($orderHistoryCountBefore+1), $orderHistoryCountAfter, 'Should create exactly one order_status_history record.');

		$this->assertEquals($data['Order']['orders_id'], $result['Order']['orders_id']);
		$this->assertarrayNotHasKey('`comments', $result['Order']);
	}

	/**
	 * testSaveUpdatingStatusOnlyStatus method
	 *
	 * @return	void
	 */
	public function testSaveUpdatingStatusOnlyStatus() {
		$orderId = 1;
		$newStatusId = 3;
		$data = array('Order' => array(
			'orders_id' => $orderId,
			'orders_status' => $newStatusId,
		));
		$this->Order->OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', array('getDatetime'));
		$this->Order->OrderStatusHistory->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->mockDatetime)));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');

		$result = $this->Order->save($data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$this->assertEquals(($orderHistoryCountBefore+1), $orderHistoryCountAfter, 'Should create exactly one order_status_history record.');

		$this->assertEquals($orderId, $oSH['OrderStatusHistory']['orders_id'], 'orders_id not saved correctly.');
		$this->assertEquals($newStatusId, $oSH['OrderStatusHistory']['orders_status_id'], 'orders_status_id not saved correctly.');
		$this->assertEquals($this->mockDatetime, $oSH['OrderStatusHistory']['date_added'], 'date_added not saved correctly');

		$this->assertEquals($data['Order']['orders_id'], $result['Order']['orders_id']);
		$this->assertEquals($data['Order']['orders_status'], $result['Order']['orders_status']);
	}

	/**
	 * Confirm if the new comment and existing comment match, the method returns
	 * false.
	 *
	 * @return void
	 */
	public function testSaveUpdatingStatusOnlyCommentMatch() {
		$data = [
			'Order' => [
				'status_history_comments' => 'foo',
			],
		];

		$Order = $this->getMockForModel('Order', ['field']);
		$Order->data = $data;

		$Order->expects($this->once())
			->method('field')
			->will($this->returnValue('foo'));

		$result = $Order->determineOrderStatusHistory();

		$this->assertFalse($result);
	}

	/**
	 *
	 * @return	void
	 */
	public function testMarkAsWithCommentsInData() {
		$id = 1;
		$expectedStatusId = '5';
		$this->Order = $this->getDatetimeMockedModel();
		$this->Order->id = 2; //Something other than the Order we wish to update.
		$before = $this->Order->findByOrdersId($id);
		$data = array(
			'status_history_comments' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ante tortor, accumsan sit amet scelerisque eu, interdum quis felis. Ut maximus et orci sit amet tincidunt. Proin placerat dapibus sapien, in eleifend diam viverra eget. Vivamus massa nunc.',
		);
		$this->Order->OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', array('getDatetime'));
		$this->Order->OrderStatusHistory->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->mockDatetime)));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');


		$save = $this->Order->markAsReturned($id, $data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$after = $this->Order->findByOrdersId($id);

		$this->assertNotEquals(
			$before['Order']['orders_status'],
			$expectedStatusId,
			'Record to modify should have a different status than the expected status.'
		);

		$this->assertEquals(($orderHistoryCountBefore+1), $orderHistoryCountAfter, 'Should create exactly one order_status_history record.');

		$this->assertTrue($save != false, 'markAsReturned() should not return false.');
		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Failed to update `orders_status`');
		$this->assertNotEquals($data['status_history_comments'], $after['Order']['comments'], 'Order comments should not be updated.');
		$this->assertEquals($data['status_history_comments'], $oSH['OrderStatusHistory']['comments'], '`status_history_comments` should be saved to OrderStatusHistory.');
		$this->assertEquals($this->datetime, $after['Order']['last_modified'], '`last_modified` should be updated to mocked time.');
	}

	/**
	 *
	 */
	public function testAfterSaveUpdatesDatePurchased() {
		$this->Order = $this->getMockForModel('Order', array('getDatetime'));
		$this->Order->expects($this->exactly(2))
			->method('getDatetime')
			->will($this->returnValue(new Datetime($this->mockDatetime)));
		$data =  array('Order' => array(
				'customers_id' => 1,
				'inbound_tracking_number' => '1Z99999999999',
				'carrier' => 'ups',
				'width' => '2',
				'length' => '4',
				'depth' => '6',
				'weight_oz' => '8',
				'mail_class' => 'priority',
				'package_type' => 'rectparcel',
				'customs_description' => 'Testing',
		));
		$data['Order'] = $this->getOrderCustomerData($data);

		$saved = $this->Order->save($data);
		$result = $this->Order->findByOrdersId($this->Order->getLastInsertId());

		$this->assertArrayHasKey('Order', $result);
		$this->assertEquals($this->mockDatetime, $result['Order']['date_purchased']);
	}

	/**
	 * testAddDefaultOrderFee
	 *
	 * @return	void
	 */
	public function testAddDefaultOrderFee() {
		$id = 1;
		$before = $this->Order->OrderFee->findAllByOrdersId($id);
		$this->assertEquals(1, count($before));

		$this->Order->id = $id;
		$result = $this->Order->addDefaultOrderFee();

		$this->assertArrayHasKey('orders_id', $result);
		$value = Configure::read('FeeByWeight.0');
		$this->assertEquals($value, $result['value']);

		$after = $this->Order->OrderFee->findAllByOrdersId($id);
		$this->assertEquals(2, count($after));
	}

	/**
	 * testAddressForPayment
	 *
	 * @return	void
	 */
	public function testAddressForPayment() {
		$id = 1;
		$this->Order->id = $id;
		$result = $this->Order->addressForPayment();
		$this->assertArrayHasKey('Address', $result);
		$this->assertArrayHasKey('Zone', $result);
		$this->assertArrayHasKey('Country', $result);
		$this->assertArrayHasKey('countries_iso_code_2', $result['Country']);
		$this->assertEquals('US', $result['Country']['countries_iso_code_2']);
	}

	/**
	 * testRecordPayment
	 *
	 * @return	void
	 */
	public function testRecordPayment() {
		$customerId = 1;
		$orderId = 1;
		$lorem = 'Lorem ipsum dolor sit amet';

		$this->Order->id = $orderId;
		$existingOrder = $this->Order->read();

		$this->assertArrayHasKey('Order', $existingOrder);
		$this->assertEquals('Lo', $existingOrder['Order']['cc_expires']);
		$this->assertEquals($lorem, $existingOrder['Order']['cc_owner']);
		$this->assertEquals(1, $existingOrder['Order']['orders_status']);
		$this->assertEquals(1, $existingOrder['Order']['billing_status']);

		$customer = $this->Order->Customer->findByCustomersId($customerId);
		$updatedOrder = $this->Order->recordPayment($customer['Customer']);

		$this->assertEquals('0614', $updatedOrder['Order']['cc_expires']);
		$this->assertEquals($lorem . ' ' . $lorem, $updatedOrder['Order']['cc_owner']);
		$this->assertEquals(4, $updatedOrder['Order']['orders_status']);
		$this->assertEquals(4, $updatedOrder['Order']['billing_status']);
		$this->assertEquals('XXXXXXXXXXXXamet', $updatedOrder['Order']['cc_number']);

		$this->Order->id = $orderId;
		$afterUpdatedOrder = $this->Order->read();

		$this->assertEquals('0614', $afterUpdatedOrder['Order']['cc_expires']);
		$this->assertEquals($lorem . ' ' . $lorem, $afterUpdatedOrder['Order']['cc_owner']);
		$this->assertSame('4', $afterUpdatedOrder['Order']['orders_status']);
		$this->assertSame('4', $afterUpdatedOrder['Order']['billing_status']);
		$this->assertEquals('XXXXXXXXXXXXamet', $afterUpdatedOrder['Order']['cc_number']);
	}

	/**
	 * @dataProvider provideValidateTrackingNotEmpty
	 */
	public function testValidateTrackingNotEmpty($carrier, $tracking, $pass) {
		$data = array('Order' => array(
			'carrier' => $carrier,
			'inbound_tracking_number' => $tracking,
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		));
		$data['Order'] = $this->getOrderCustomerData($data);
		$this->Order->set($data);
		$assert = 'assert' . ($pass ? 'True' : 'False');
		$this->{$assert}($this->Order->validates());
	}

	public function provideValidateTrackingNotEmpty() {
		return array(
			array('none', '', true),
			array('dhl', '', false),
			array('ups', '', false),
			array('usps', '', false),
			array('dhl', '123213', true),
			array('ups', '12321312', true),
			array('usps', '12312312', true),
		);
	}

	/**
	 * Ensure that new orders cannot be created with previously used inbound
	 * tracking numbers.
	 *
	 * @dataProvider provideValidateTrackingUnique
	 */
	public function testValidateTrackingUnique($first, $second, $resultKey = null) {
		$first = $this->getOrderCustomerData($first);
		$second = $this->getOrderCustomerData($second);
		$this->Order->save($first);
		$this->Order->create();
		$this->Order->set($second);
		$this->Order->validates();

		if ($resultKey) {
			$this->assertArrayHasKey(
				$resultKey,
				$this->Order->validationErrors,
				'Order with previously used inbound trackung number should fail validation.'
			);
		} else {
			$this->assertEmpty(
				$this->Order->validationErrors,
				'Order with unique tracking number should not fail validation.'
			);
		}
	}

	public function provideValidateTrackingUnique() {
		return [
			[
				$this->buildValidateTrackingUnique('ups', '12345'),
				$this->buildValidateTrackingUnique('ups', '12345'),
				'ups_track_num',
			],
			[
				$this->buildValidateTrackingUnique('ups', '12345'),
				$this->buildValidateTrackingUnique('ups', '12346'),
				null,
			],
			[
				$this->buildValidateTrackingUnique('usps', '12345'),
				$this->buildValidateTrackingUnique('usps', '12345'),
				'usps_track_num_in',
			],
			[
				$this->buildValidateTrackingUnique('usps', '12345'),
				$this->buildValidateTrackingUnique('usps', '12346'),
				null,
			],
			[
				$this->buildValidateTrackingUnique('fedex', '12345'),
				$this->buildValidateTrackingUnique('fedex', '12345'),
				'fedex_track_num',
			],
			[
				$this->buildValidateTrackingUnique('fedex', '12345'),
				$this->buildValidateTrackingUnique('fedex', '12346'),
				null,
			],
			[
				$this->buildValidateTrackingUnique('fedex_freight', '12345'),
				$this->buildValidateTrackingUnique('fedex_freight', '12345'),
				'fedex_freight_track_num',
			],
			[
				$this->buildValidateTrackingUnique('fedex_freight', '12345'),
				$this->buildValidateTrackingUnique('fedex_freight', '12346'),
				null,
			],
			[
				$this->buildValidateTrackingUnique('dhl', '12345'),
				$this->buildValidateTrackingUnique('dhl', '12345'),
				'dhl_track_num',
			],
			[
				$this->buildValidateTrackingUnique('dhl', '12345'),
				$this->buildValidateTrackingUnique('dhl', '12346'),
				null,
			],
		];
	}

	protected function buildValidateTrackingUnique($carrier, $tracking) {
		$baseOrder = [
			'carrier' => '',
			'inbound_tracking_number' => '',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		];

		return ['Order' => array_merge($baseOrder, [
			'carrier' => $carrier,
			'inbound_tracking_number' => $tracking,
		])];
	}

	/**
	 * Confirm that the onError() method sets model property
	 * shouldCreateOrderStatusHistory to false when called.
	 *
	 * @return	void
	 */
	public function testOnError() {
		$this->Order->shouldCreateOrderStatusHistory = true;
		$this->assertTrue($this->Order->shouldCreateOrderStatusHistory);
		$this->Order->onError();
		$this->assertFalse($this->Order->shouldCreateOrderStatusHistory);
	}

	/**
	 * Confirm that passing createOrderDetailsForOrder() an $id results in
	 * setting $this->id to $id.
	 *
	 * @return	void
	 */
	public function testCreateOrderDetailsForOrderIdNotNull() {
		$id = 1;
		$this->assertFalse($this->Order->id);
		$this->Order->createOrderDetailsForOrder($id);
		$this->assertEquals($id, $this->Order->id);
	}

	/**
	 * Confirm that _findAwaitingPayments will modify the find query when
	 * variable $state is set to 'before', otherwise return the passed
	 * $results array.
	 *
	 * @dataProvider provideAwaitingPayments
	 *
	 * @return	void
	 */
	public function testFindAwaitingPayments($state, $query, $results = array()) {
		$beforeExpected = array(
			'conditions' => array(
				'TestOrder.orders_status' => 2
			)
		);

		$this->TestOrder = new TestOrder();
		$processedQuery = $this->TestOrder->_findAwaitingPayments($state, $query, $results);
		if ($state == 'before') {
			$this->assertArrayHasKey('conditions', $processedQuery);
			$this->assertEquals($beforeExpected['conditions'], $processedQuery['conditions']);
		} else {
			$this->assertArrayNotHasKey('conditions', $processedQuery);
			$this->assertEquals($results, $processedQuery);
		}
	}

	public function provideAwaitingPayments() {
		$query = array(
			'recursive' => -1,
		);
		return array(
			array('before', $query),
			array('foo', $query),
			array('bar', $query, array('foo' => 'bar')),
		);
	}

	/**
	 * Confirm that the method returns true and that the CreditCard behavior
	 * is disabled when `billing_type` = `invoice` and `invoicing_authorized` =
	 * `1`.
	 *
	 * @return	void
	 */
	public function testCheckForInvoiceCustomerWhenTrue() {
		$customer = array(
			'billing_type' => 'invoice',
			'invoicing_authorized' => 1,
		);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
		$result = $this->Order->checkForInvoiceCustomer($customer, true);
		$this->assertTrue($result);
		$this->assertFalse($this->Order->Behaviors->enabled('CreditCard'));
	}

	/**
	 * Confirm that the method returns true and that the CreditCard behavior
	 * is NOT enabled when `billing_type` != `invoice` and `invoicing_authorized` =
	 * `1` and no second arg is supplied.
	 *
	 * @return	void
	 */
	public function testCheckForInvoiceCustomerWhenTrueNotDisabled() {
		$customer = array(
			'billing_type' => 'invoice',
			'invoicing_authorized' => 1,
		);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
		$result = $this->Order->checkForInvoiceCustomer($customer);
		$this->assertTrue($result);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
	}

	/**
	 * Confirm that the method returns false and that the CreditCard behavior
	 * is enabled when `billing_type` != `invoice` and `invoicing_authorized` =
	 * `1`.
	 *
	 * @return	void
	 */
	public function testCheckForInvoiceCustomerWhenFalse() {
		$customer = array(
			'billing_type' => 'cc',
			'invoicing_authorized' => 1,
		);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
		$result = $this->Order->checkForInvoiceCustomer($customer);
		$this->assertFalse($result);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
	}

	/**
	 * Confirm that the method returns false and that the CreditCard behavior
	 * is enabled when `billing_type` and `invoicing_authorized` are not set.
	 *
	 * @return	void
	 */
	public function testCheckForInvoiceCustomerWhenNoData() {
		$customer = array();
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
		$result = $this->Order->checkForInvoiceCustomer($customer);
		$this->assertFalse($result);
		$this->assertTrue($this->Order->Behaviors->enabled('CreditCard'));
	}

	/**
	 * Confirm that recordInvoicePayment() sets values correctly.
	 *
	 * @return	void
	 */
	public function testRecordInvoicePayment() {
		$customerId = 1;
		$orderId = 1;
		$lorem = 'Lorem ipsum dolor sit amet';

		// This is called by Order::checkForInvoiceCustomer() before
		// Order::recordInvoicePayment() is called.
		$this->Order->Behaviors->disable('CreditCard');

		$this->Order->id = $orderId;
		$existingOrder = $this->Order->read();

		$this->assertArrayHasKey('Order', $existingOrder);
		$this->assertEquals('Lo', $existingOrder['Order']['cc_expires']);
		$this->assertEquals($lorem, $existingOrder['Order']['cc_owner']);
		$this->assertEquals(1, $existingOrder['Order']['orders_status']);
		$this->assertEquals(1, $existingOrder['Order']['billing_status']);

		$customer = $this->Order->Customer->findByCustomersId($customerId);
		$updatedOrder = $this->Order->recordInvoicePayment($customer['Customer']);

		$this->assertEquals('INV', $updatedOrder['Order']['cc_expires']);
		$this->assertEquals($lorem . ' ' . $lorem, $updatedOrder['Order']['cc_owner']);
		$this->assertEquals(1, $updatedOrder['Order']['orders_status']);
		$this->assertEquals(5, $updatedOrder['Order']['billing_status']);
		$this->assertEquals('Invoice', $updatedOrder['Order']['cc_number']);

		$this->Order->id = $orderId;
		$afterUpdatedOrder = $this->Order->read();

		$this->assertEquals('INV', $afterUpdatedOrder['Order']['cc_expires']);
		$this->assertEquals($lorem . ' ' . $lorem, $afterUpdatedOrder['Order']['cc_owner']);
		$this->assertSame('1', $afterUpdatedOrder['Order']['orders_status']);
		$this->assertSame('5', $afterUpdatedOrder['Order']['billing_status']);
		$this->assertEquals('Invoice', $afterUpdatedOrder['Order']['cc_number']);
	}

	/**
	 * Confirm that `inbound_tracking_number` is assigned to carrier correctly.
	 *
	 * @return	void
	 */
	public function testMarshalTrackingNumberWithNumber() {
		$this->Order->data = array(
			'Order' => array(
				'carrier' => 'ups',
				'inbound_tracking_number' => '123',
			),
		);
		$this->Order->marshalTrackingNumber();
		$this->assertArrayHasKey('ups_track_num', $this->Order->data['Order']);
		$this->assertEquals('123', $this->Order->data['Order']['ups_track_num']);
	}

	/**
	 * Confirm that `inbound_tracking_number` is null if not set.
	 *
	 * @return	void
	 */
	public function testMarshalTrackingNumberWithOutNumber() {
		$this->Order->data = array(
			'Order' => array(
				'carrier' => 'ups',
			),
		);
		$this->Order->marshalTrackingNumber();
		$this->assertArrayHasKey('ups_track_num', $this->Order->data['Order']);
		$this->assertEquals(null, $this->Order->data['Order']['ups_track_num']);
	}

	/*
	 * Confirm that findOrderForCharge returns data with the expected keys set
	 * for the supplied $orderId.
	 *
	 * @return	void
	 */
	public function testFindOrderForCharge() {
		$orderId = 1;
		$order = $this->Order->findOrderForCharge($orderId);
		$this->assertArrayHasKey('Order', $order);
		$this->assertArrayHasKey('Customer', $order);
		$this->assertArrayHasKey('OrderShipping', $order);
		$this->assertArrayHasKey('OrderStorage', $order);
		$this->assertArrayHasKey('OrderInsurance', $order);
		$this->assertArrayHasKey('OrderFee', $order);
		$this->assertArrayHasKey('OrderSubtotal', $order);
		$this->assertArrayHasKey('OrderTotal', $order);
		$this->assertEquals($orderId, $order['Order']['orders_id']);
	}

	/**
	 * Confirm that saveOrderForCharge saves order data as expected.
	 *
	 * @return	void
	 */
	public function testSaveOrderForCharge() {
		$orderId = 1;
		$before = $this->Order->findOrderForCharge($orderId);
		$data = array(
			'submit' => 'charge',
			'Order' => array(
				'orders_id' => $orderId,
				'last_modified' => '2015-09-23 14:19:51',
				'orders_status' => '4'
			),
			'OrderShipping' => array(
				'value' => '0.00',
				'orders_total_id' => '1'
			),
			'OrderStorage' => array(
				'value' => '10.23',
				'orders_total_id' => '2'
			),
			'OrderInsurance' => array(
				'value' => '5.69',
				'orders_total_id' => '3'
			),
			'OrderFee' => array(
				'value' => '19.95',
				'orders_total_id' => '41231231'
			)
		);
		$result = $this->Order->saveOrderForCharge($data);
		$this->assertTrue($result);
		$after = $this->Order->findOrderForCharge($orderId);
		$this->assertNotEqual($before['Order']['orders_status'], $after['Order']['orders_status']);
		$this->assertEquals('10.23', $after['OrderStorage']['value']);
		$this->assertEquals('5.69', $after['OrderInsurance']['value']);
		$this->assertEquals('19.95', $after['OrderFee']['value']);
	}

	/**
	 * Confirm that saveOrderForCharge saves order data with custom fields as expected.
	 *
	 * @return	void
	 */
	public function testSaveOrderForChargeWithCustomFields() {
		$orderId = 1;
		$before = $this->Order->findOrderForCharge($orderId);
		$data = array(
			'submit' => 'charge',
			'Order' => array(
				'orders_id' => $orderId,
				'last_modified' => '2015-09-23 14:19:51',
				'orders_status' => '4'
			),
			'OrderRepack' => array(
				'title' => 'Foo',
				'value' => '1.11',
				'orders_total_id' => '3333'
			),
			'OrderBattery' => array(
				'title' => 'Bar',
				'value' => '2.22',
				'orders_total_id' => '4444'
			),
		);
		$result = $this->Order->saveOrderForCharge($data);
		$this->assertTrue($result);
		$this->assertEquals('0.0000', $before['OrderRepack']['value']);
		$this->assertEquals('0.0000', $before['OrderBattery']['value']);
		$this->assertEquals('113.6000', $before['OrderTotal']['value']);

		$after = $this->Order->findOrderForCharge($orderId);
		$this->assertNotEqual($before['Order']['orders_status'], $after['Order']['orders_status']);
		$this->assertEquals('1.11', $after['OrderRepack']['value']);
		$this->assertEquals('2.22', $after['OrderBattery']['value']);
		$this->assertEquals('116.9300', $after['OrderTotal']['value']);
	}

	/**
	 * Confirm that checkIfOrderCanBeCharged returns the correct bool
	 * depending on orders_status.
	 *
	 * @dataProvider provideCheckIfOrderCanBeCharged
	 * @return	void
	 */
	public function testCheckIfOrderCanBeCharged($input, $expected) {
		$Orders = $this->getMockForModel('Order', array('taskFactory'));
		$Task = $this->getMock('Task', array('createJob'));
		$Task->expects($this->any())
			->method('createJob')
			->will($this->returnValue(true));
		$Orders->expects($this->any())
			->method('taskFactory')
			->will($this->returnValue($Task));

		$this->assertEquals($expected, $Orders->checkIfOrderCanBeCharged($input));
	}

	public function provideCheckIfOrderCanBeCharged() {
		return array(
			array(
				array(
					'Order' => array(
						'orders_status' => 1
					),
					'Customer' => array(
						'is_active' => 1
					),
				),
				array('allow' => true),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 2
					),
					'Customer' => array(
						'is_active' => 1
					),
				),
				array('allow' => true),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 3
					),
					'Customer' => array(
						'is_active' => 1
					),
					'OrderStatus' => array(
						'orders_status_name' => 'foo',
					),
				),
				array(
					'allow' => false,
					'message' => 'Orders cannot be charged while in status: Foo',
				),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 4
					),
					'Customer' => array(
						'is_active' => 1
					),
					'OrderStatus' => array(
						'orders_status_name' => 'foo',
					),
				),
				array(
					'allow' => false,
					'message' => 'Orders cannot be charged while in status: Foo',
				),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 5
					),
					'Customer' => array(
						'is_active' => 1
					),
					'OrderStatus' => array(
						'orders_status_name' => 'foo',
					),
				),
				array(
					'allow' => false,
					'message' => 'Orders cannot be charged while in status: Foo',
				),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 999
					),
					'Customer' => array(
						'is_active' => 1
					),
					'OrderStatus' => array(
						'orders_status_name' => 'foo',
					),
				),
				array(
					'allow' => false,
					'message' => 'Orders cannot be charged while in status: Foo',
				),
			),
			array(
				array(
					'Order' => array(
						'orders_status' => 1,
						'orders_id' => 1,
					),
					'Customer' => array(
						'is_active' => 0,
						'billing_id' => 'bar',
					),
				),
				array(
					'allow' => false,
					'message' => 'The order can not be charged because customer bar has a closed account.',
				),
			),
		);
	}

	/**
	 * Confirm that saveOrder saves an order.
	 *
	 * @return	void
	 */
	public function testSaveOrder() {
		$data = array(
			'Order' => array(
				'customer_id' => 1,
				'inbound_tracking_number' => '1Z12345',
				'carrier' => 'ups',
				'width' => '2',
				'length' => '4',
				'depth' => '6',
				'weight_oz' => '8',
				'mail_class' => 'priority',
				'package_type' => 'rectparcel',
				'customs_description' => 'Testing',
			),
		);
		$data['Order'] = $this->getOrderCustomerData($data);
		$result = $this->Order->saveOrder($data);
		$this->assertTrue($result);
		$order = $this->Order->findByOrdersId($this->Order->id);
		$this->assertEquals($data['Order']['inbound_tracking_number'], $order['Order']['ups_track_num']);
	}

	/**
	 * Confirm the method returns true if all keys are valid.
	 *
	 * @return	void
	 */
	public function testCheckOrderKeys() {
		$data = array(
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
		);
		$result = $this->Order->checkOrderKeys($data);
		$this->assertTrue($result);
	}

	/**
	 * Confirm an exception is thrown with message if an invalid key exists.
	 *
	 * @return	void
	 */
	public function testCheckOrderKeysThowsException() {
		$data = array(
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'shipped_from' => 'foo',
		);
		$this->setExpectedException('BadRequestException', "An invalid key exists in the request: shipped_from");
		$result = $this->Order->checkOrderKeys($data);
	}

	/**
	 * Confirm that findOrderTotalsReport returns expected data with various
	 * query combinations.
	 *
	 * @dataProvider provideFindOrderTotalsReport
	 * @return void
	 */
	public function testFindOrderTotalsReport($data, $expected) {
		$result = $this->Order->findOrderTotalsReport($data);
		$this->assertEquals($expected['count'], count($result));
		if ($result) {
			$this->assertArrayHasKey(0, $result);
			$this->assertArrayHasKey('date_purchased', $result[0]);
			$this->assertArrayHasKey('total', $result[0]);
			$this->assertArrayHasKey('ot_fee', $result[0]);
			$this->assertArrayHasKey('ot_insurance', $result[0]);
			$this->assertArrayHasKey('ot_shipping', $result[0]);
			$this->assertEquals($expected['total'], $result[0]['total']);
		}

	}

	/**
	 * provideFindOrderTotalsReport
	 *
	 * @return array
	 */
	public function provideFindOrderTotalsReport() {
		return array(
			array(
				'data' => array(
					'interval' => 'year',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00'
				),
				'result' => array(
					'count' => 1,
					'total' => 10,
				),
			),
			array(
				'data' => array(
					'interval' => 'month',
					'from_date' => '2015-07-01 00:00:00',
					'to_date' => '2015-07-31 00:00:00'
				),
				'result' => array(
					'count' => 1,
					'total' => 2,
				),
			),
			array(
				'data' => array(
					'interval' => 'week',
					'from_date' => '2015-10-19 00:00:00',
					'to_date' => '2015-10-25 00:00:00'
				),
				'result' => array(
					'count' => 0,
					'total' => 0,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => '2015-07-19 00:00:00',
					'to_date' => '2015-07-19 23:59:59'
				),
				'result' => array(
					'count' => 1,
					'total' => 2,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00',
					'orders_status' => 1,
				),
				'result' => array(
					'count' => 3,
					'total' => 2,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => array(
						'day' => '21',
						'month' => '10',
						'year' => '2014'
					),
					'to_date' => array(
						'day' => '21',
						'month' => '10',
						'year' => '2015'
					),
				),
				'result' => array(
					'count' => 4,
					'total' => 4,
				),
			),
		);
	}

	/**
	 * Confirm that setWeight() calculates the value for `weight_oz` correctly
	 * and unsets `weight_lb` when it should.
	 *
	 * @dataProvider provideTestSetWeight
	 * @return void
	 */
	public function testSetWeight($data, $expected) {
		$result = $this->Order->setWeight($data);
		$this->assertEquals($expected['weight_oz'], $result['weight_oz']);
		if ($expected['unset'] == true) {
			$this->assertArrayNotHasKey('weight_lb', $result);
		} else {
			$this->assertArrayHasKey('weight_lb', $result);
		}
	}

	/**
	 * provideTestSetWeight
	 *
	 * @return array
	 */
	public function provideTestSetWeight() {
		return array(
			array(
				'data' => array(
					'weight_lb' => 1,
					'weight_oz' => 4,
				),
				'expected' => array(
					'weight_oz' => 20,
					'unset' => true,
				),
			),
			array(
				'data' => array(
					'weight_lb' => '',
					'weight_oz' => 8,
				),
				'expected' => array(
					'weight_oz' => 8,
					'unset' => true,
				),
			),
			array(
				'data' => array(
					'weight_lb' => 1.5,
					'weight_oz' => 0,
				),
				'expected' => array(
					'weight_oz' => 24,
					'unset' => true,
				),
			),
			array(
				'data' => array(
					'weight_lb' => 'foo',
					'weight_oz' => 7,
				),
				'expected' => array(
					'weight_oz' => 7,
					'unset' => false,
				),
			),
			array(
				'data' => array(
					'weight_lb' => 1,
					'weight_oz' => 'foo',
				),
				'expected' => array(
					'weight_oz' => 'foo',
					'unset' => false,
				),
			),
		);
	}

	/**
	 * Confirm that order totals per status can be found correctly
	 *
	 * @return void
	 */
	public function testFindTotalsPerStatus() {
		$result = $this->Order->findTotalsPerStatus();
		$this->assertEquals(4, count($result));
		$this->assertContains('Lorem ipsum', key($result[0]));
		$this->assertEquals(2, $result[1]['Lorem ipsum dolor sit amet']);
		$this->assertEquals(1, $result[2]['Lorem ipsum dolor sit amet']);
	}

	/**
	 * Confirm that findAndChargeAllOrdersAwaitingPayment returns expected array keys
	 * and data.
	 *
	 * @return void
	 */
	public function testFindAndChargeAllOrdersAwaitingPayment() {
		$result = $this->Order->findAndChargeAllOrdersAwaitingPayment();
		$this->assertArrayHasKey('Order', $result[0]);
		$this->assertArrayHasKey('Customer', $result[0]);
		$this->assertArrayHasKey('CustomerReminder', $result[0]);
		$this->assertArrayHasKey('OrderTotal', $result[0]);
		$this->assertEquals(1, $result[1]['CustomerReminder'][0]['customers_id']);
	}

	/**
	 * Confirm that findAndChargeAllOrdersAwaitingPayment returns an empty result if
	 * the count is set to `1`.
	 *
	 * @return void
	 */
	public function testFindAndChargeAllOrdersAwaitingPaymentSetCount() {
		Configure::write('Orders.paymentReminders', 1);
		$result = $this->Order->findAndChargeAllOrdersAwaitingPayment();
		$this->assertEmpty($result);
	}

	/**
	 * Confirm the customer set default_mail_type will convert to a valid
	 * order mail_class value.
	 *
	 * @dataProvider providesMailClassFromCustomer
	 */
	public function testMailClassFromCustomer($data, $expected) {
		$this->assertSame($expected, $this->Order->mailClassFromCustomer($data));
	}

	public function providesMailClassFromCustomer() {
		return [
			['priority_mail', 'priority'],
			['parcel_post',  'parcel'],
			['apobox_direct',  null],
			['something_else',  null],
		];
	}

	/**
	 * Confirm that the expected OrderStatusHistory record is created and that
	 * the `customer_notified` field is set to `1`.
	 *
	 * @return void
	 */
	public function testSaveUpdatingStatusNotifyCustomer() {
		$orderId = 1;
		$newStatusId = 3;
		$data = array('Order' => array(
			'orders_id' => $orderId,
			'orders_status' => $newStatusId,
			'notify_customer' => 1
		));
		$this->Order->OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', array('getDatetime'));
		$this->Order->OrderStatusHistory->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create($this->mockDatetime)));
		$orderHistoryCountBefore = $this->Order->OrderStatusHistory->find('count');

		$result = $this->Order->save($data);

		$orderHistoryCountAfter = $this->Order->OrderStatusHistory->find('count');
		$orderStatusHistoryId = $this->Order->OrderStatusHistory->getLastInsertId();
		$oSH = $this->Order->OrderStatusHistory->find('first', array(
			'conditions' => array('OrderStatusHistory.orders_status_history_id' => $orderStatusHistoryId)
		));

		$this->assertEquals(($orderHistoryCountBefore+1), $orderHistoryCountAfter, 'Should create exactly one order_status_history record.');

		$this->assertEquals($orderId, $oSH['OrderStatusHistory']['orders_id'], 'orders_id not saved correctly.');
		$this->assertEquals($newStatusId, $oSH['OrderStatusHistory']['orders_status_id'], 'orders_status_id not saved correctly.');
		$this->assertSame('1', $oSH['OrderStatusHistory']['customer_notified'], 'Should set customer_notified to 1');
		$this->assertEquals($this->mockDatetime, $oSH['OrderStatusHistory']['date_added'], 'date_added not saved correctly');

		$this->assertEquals($data['Order']['orders_id'], $result['Order']['orders_id']);
		$this->assertEquals($data['Order']['orders_status'], $result['Order']['orders_status']);
		$this->assertArrayNotHasKey('comments', $result['Order']);
	}

	/**
	 * Confirm that all expected methods are called and processCharge returns
	 * true if successful.
	 *
	 * @return void
	 */
	public function testProcessChargeSuccess() {
		$data = [
			'Order' => [
				'orders_id' => 1,
			],
			'OrderTotal' => [
				'value' => 5,
			],
			'Customer' => [],
		];
		$Order = $this->getMockForModel('Order', [
			'addressForPayment',
			'checkIfOrderCanBeCharged',
			'charge',
			'sendStatusUpdateEmail',
			'recordPayment',
		]);
		$Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue([]));
		$Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->will($this->returnValue(['allow' => true]));
		$Order->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Order->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Order->expects($this->once())
			->method('recordPayment')
			->will($this->returnValue(true));

		$result = $Order->processCharge($data);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that if checkIfOrderCanBeCharged fails a message is returned
	 * and the charge does not proceed.
	 *
	 * @return void
	 */
	public function testProcessChargeCannotBeCharged() {
		$data = [
			'Order' => [
				'orders_id' => 1,
			],
			'OrderTotal' => [
				'value' => 5,
			],
			'Customer' => [],
		];
		$Order = $this->getMockForModel('Order', [
			'addressForPayment',
			'checkIfOrderCanBeCharged',
			'charge',
			'sendStatusUpdateEmail',
			'recordPayment',
		]);
		$Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->will($this->returnValue(['allow' => false, 'message' => 'error']));
		$Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue([]));
		$Order->expects($this->never())->method('charge');
		$Order->expects($this->never())->method('sendStatusUpdateEmail');
		$Order->expects($this->never())->method('recordPayment');

		$result = $Order->processCharge($data);
		$this->assertSame('error', $result);
	}

	/**
	 * Confirm that if a call to charge() fails sendStatusUpdateEmail and recordPayment
	 * are not called and processCharge returns false.
	 *
	 * @return void
	 */
	public function testProcessChargeFailedCharge() {
		$data = [
			'Order' => [
				'orders_id' => 1,
			],
			'OrderTotal' => [
				'value' => 5,
			],
			'Customer' => [],
		];
		$Order = $this->getMockForModel('Order', [
			'addressForPayment',
			'checkIfOrderCanBeCharged',
			'charge',
			'sendStatusUpdateEmail',
			'recordPayment',
		]);
		$Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue([]));
		$Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->will($this->returnValue(['allow' => true]));
		$Order->expects($this->once())
			->method('charge')
			->will($this->returnValue(false));
		$Order->expects($this->never())->method('sendStatusUpdateEmail');
		$Order->expects($this->never())->method('recordPayment');

		$result = $Order->processCharge($data);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when a charge is successful but not correctly recorded a
	 * log entry is made the the method returns the expected response.
	 *
	 * @return void
	 */
	public function testProcessChargeSuccessRecordFails() {
		$data = [
			'Order' => [
				'orders_id' => 1,
			],
			'OrderTotal' => [
				'value' => 5,
			],
			'Customer' => [],
		];
		$Order = $this->getMockForModel('Order', [
			'addressForPayment',
			'checkIfOrderCanBeCharged',
			'charge',
			'sendStatusUpdateEmail',
			'recordPayment',
			'log',
		]);
		$Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue([]));
		$Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->will($this->returnValue(['allow' => true]));
		$Order->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Order->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Order->expects($this->once())
			->method('recordPayment')
			->will($this->returnValue(false));
		$Order->expects($this->once())
			->method('log')
			->with(
				$this->identicalTo('OrderModel::processCharge: Order #1 was not properly recorded.'),
				$this->identicalTo('orders')
			);

		$result = $Order->processCharge($data);
		$this->assertSame('not recorded', $result);
	}

	/**
	 * testChargeSuccess
	 *
	 * @return void
	 */
	public function testChargeSuccess() {
		$card = [];
		$Payment = $this->getMockBuilder('Payment')
			->disableOriginalConstructor()
			->getMock();
		$Order = $this->getMockForModel('Order', [
			'getPaymentLib',
		]);
		$Customer = $this->getMockForModel('Customer', [
			'initForCharge',
		]);
		$Order->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($Payment));
		$Customer->expects($this->once())
			->method('initForCharge');

		$Order->charge($card);
	}

	/**
	 * testChargeFailure
	 *
	 * @return void
	 */
	public function testChargeFailure() {
		$card = [];
		$Order = $this->getMockForModel('Order', [
			'log',
		]);
		$Order->expects($this->once())
			->method('log')
			->with(
				$this->stringContains('OrderModel::charge'),
				$this->identicalTo('orders')
			);
		$this->assertFalse($Order->charge($card));
	}

	/**
	 * Confirm that findAndChargeAllOrdersAwaitingPayment returns expected array keys
	 * and data and that the expected charge related methods are called.
	 *
	 * @return void
	 */
	public function testFindAndChargeAllOrdersAwaitingPaymentDoCharge() {
		$Order = $this->getMockForModel('Order', [
			'processCharge',
		]);
		$CustomerReminder = $this->getMockForModel('CustomerReminder', [
			'clearRecord',
		]);
		$Order->expects($this->any())
			->method('processCharge')
			->will($this->returnValue(true));
		$CustomerReminder->expects($this->at(0))
			->method('clearRecord')
			->with(5, 'awaiting_payment');
		$CustomerReminder->expects($this->at(1))
			->method('clearRecord')
			->with(4, 'awaiting_payment');

		$result = $Order->findAndChargeAllOrdersAwaitingPayment(true);
		$this->assertArrayHasKey('Order', $result[0]);
		$this->assertArrayHasKey('Customer', $result[0]);
		$this->assertArrayHasKey('CustomerReminder', $result[0]);
		$this->assertArrayHasKey('OrderTotal', $result[0]);
		$this->assertEquals(1, $result[1]['CustomerReminder'][0]['customers_id']);
		$this->assertTrue($result[0]['Order']['charged']);
	}

	/**
	 * Confirm the `recordChargeFailed` method is called with the expected
	 * arguments when a charge fails.
	 *
	 * @return void
	 */
	public function testFindAndChargeAllOrdersAwaitingPaymentDoChargeChargeFails() {
		$Order = $this->getMockForModel('Order', [
			'processCharge',
		]);
		$CustomerReminder = $this->getMockForModel('CustomerReminder', [
			'clearRecord',
		]);
		$OrderStatusHistory = $this->getMockForModel('OrderStatusHistory', [
			'recordChargeFailed',
		]);
		$Order->expects($this->at(0))
			->method('processCharge')
			->will($this->returnValue(true));
		$Order->expects($this->at(1))
			->method('processCharge')
			->will($this->returnValue(false));
		$CustomerReminder->expects($this->once())
			->method('clearRecord')
			->with(5, 'awaiting_payment');
		$OrderStatusHistory->expects($this->once())
			->method('recordChargeFailed')
			->with($this->logicalAnd(
				$this->arrayHasKey('Order'),
				$this->arrayHasKey('OrderTotal'),
				$this->arrayHasKey('Customer'),
				$this->arrayHasKey('CustomerReminder')
			));

		$result = $Order->findAndChargeAllOrdersAwaitingPayment(true);
		$this->assertArrayHasKey('Order', $result[0]);
		$this->assertArrayHasKey('Customer', $result[0]);
		$this->assertArrayHasKey('CustomerReminder', $result[0]);
		$this->assertArrayHasKey('OrderTotal', $result[0]);
		$this->assertEquals(1, $result[1]['CustomerReminder'][0]['customers_id']);
		$this->assertTrue($result[0]['Order']['charged']);
	}

	/*
	 * getOrderCustomerData
	 * Confirm that getOrderAddressFields() returns an array with the expected
	 * fields.
	 *
	 * @return void
	 */
	public function testGetOrderAddressFields() {
		$expected = [
			'delivery_suburb',
			'delivery_street_address',
			'delivery_state',
			'delivery_postcode',
			'delivery_name',
			'delivery_country',
			'delivery_company',
			'delivery_city',
			'delivery_address_format_id',
			'customers_suburb',
			'customers_street_address',
			'customers_state',
			'customers_postcode',
			'customers_name',
			'customers_email_address',
			'customers_country',
			'customers_company',
			'customers_city',
			'customers_address_format_id',
			'billing_suburb',
			'billing_street_address',
			'billing_state',
			'billing_postcode',
			'billing_name',
			'billing_country',
			'billing_company',
			'billing_city',
			'billing_address_format_id'
		];
		$result = $this->Order->getOrderAddressFields();
		$this->assertSame($expected, $result);
	}

	/**
	 * Combines supplied order $data with the customer address required fields
	 * to successfully create an order record.
	 *
	 * @param array $data Existing data
	 * @param int $id An order id
	 * @return array The combined data
	 */
	public function getOrderCustomerData($data = [], $id = 1) {
		$fields = $this->Order->getOrderAddressFields();
		$customerData = $this->Order->find('first', [
			'fields' => $fields,
			'conditions' => ['orders_id' => $id],
		]);
		return array_merge($customerData['Order'], $data['Order']);
	}

	/**
	 * Confirm that findAndChargeAllOrdersAwaitingPayment does not attempt a charge
	 * or send an alert to customers with a closed account.
	 *
	 * @return void
	 */
	public function testFindAndChargeAllOrdersAwaitingPaymentActiveCustomersOnly() {
		$options = [
			'fields' => [
				'orders_id',
			],
			'contain' => [
				'CustomerReminder' => [
					'conditions' => [
						'reminder_type' => 'awaiting_payment',
					],
				],
			],
		];
		$orders = $this->Order->find('awaitingPayments', $options);
		$this->assertSame(3, count($orders), 'should be 3 awaiting payment orders');
		$customerIds = Hash::extract($orders, '{n}.CustomerReminder.{n}.customers_id');
		$this->assertContains('7', $customerIds, 'should contain the closed account id 7');

		$result = $this->Order->findAndChargeAllOrdersAwaitingPayment();
		$this->assertSame(2, count($result), 'should be 2 awaiting payment orders');
		$customerIds = Hash::extract($result, '{n}.CustomerReminder.{n}.customers_id');
		$this->assertNotContains('7', $customerIds, 'should NOT contain the closed account id 7');
	}

	/**
	 * Confirm calculateInsurance() returns the correct insurance amount for
	 * a valid $coverage amount, or false if the amount is invalid.
	 *
	 * @dataProvider provideCalculateInsurance
	 * @return void
	 */
	public function testCalculateInsurance($expected, $orderId) {
		$this->Order->id = $orderId;
		$result = $this->Order->calculateInsurance();
		$this->assertSame($expected, $result);
	}

	public function provideCalculateInsurance() {
		return [
			['1.75', 1],
			[false, 2],
		];
	}

	/**
	 * Confirm usesFedex() can correctly determnine if an order should use
	 * FedEx shipping instead of USPS.
	 *
	 * @dataProvider provideCanUseFedex
	 * @return void
	 */
	public function testCanUseFedex($expected, $order, $msg = null) {
		$result = $this->Order->usesFedex($order);
		$this->{'assert' . $expected}($result, $msg);
	}

	public function provideCanUseFedex() {
		return [
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'AA',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 15,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as delivery_state is not a US state'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 15,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as delivery_country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as weight is over 70 lbs but country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 60,
					'width' => 50,
					'depth' => 2,
					'weight_oz' => 40,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as dimensions are greater than 130 but country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 60,
					'width' => 50,
					'depth' => 2,
					'weight_oz' => 1500,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as dimensions are greater than 130 and weight is greater than 70 lbs but country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'AE',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
					'mail_class' => 'ANYTHING',
				]
			], 'Should be false as weight is over 70 lbs, country is US, and state is an APO'],
			['False', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 6,
					'width' => 5,
					'depth' => 2,
					'weight_oz' => 15,
					'mail_class' => 'PRIORITY',
				]
			], 'Should be false as country and state are US but mail_class is PRIORITY'],
			['True', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 6,
					'width' => 5,
					'depth' => 2,
					'weight_oz' => 15,
					'mail_class' => 'FEDEX',
				]
			], 'Should be true as country and state are US and mail_class is FEDEX'],
			['True', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
					'mail_class' => 'FEDEX',
				]
			], 'Should be true as weight is over 70 lbs, country and state are US, and mail_class is FEDEX'],
			['True', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 60,
					'width' => 50,
					'depth' => 2,
					'weight_oz' => 15,
					'mail_class' => 'FEDEX',
				]
			], 'Should be true as dimensions are over 130, country and state are US, and mail_class is FEDEX'],
		];
	}

	/**
	 * Confirm that the `mail_class` field value can be updated.
	 *
	 * @return void
	 */
	public function testSaveOrderForChargeMailClass() {
		$orderId = 1;
		$before = $this->Order->findOrderForCharge($orderId);
		$data = [
			'submit' => 'save',
			'Order' => [
				'orders_id' => $orderId,
				'last_modified' => '2015-09-23 14:19:51',
				'orders_status' => '4',
				'mail_class' => 'fedex',
			],
		];
		$this->assertSame('PRIORITY', $before['Order']['mail_class']);
		$result = $this->Order->saveOrderForCharge($data);
		$this->assertTrue($result);
		$after = $this->Order->findOrderForCharge($orderId);
		$this->assertSame('FEDEX', $after['Order']['mail_class']);
	}

	/**
	 * Confirm that packageSize() can do math
	 *
	 * @dataProvider providePackageSize
	 * @return void
	 */
	public function testPackageSize($expected, $order, $msg = null) {
		$TestOrder = new TestOrder();
		$result = $TestOrder->packageSize($order);
		$this->assertSame($expected, $result);
	}

	public function providePackageSize() {
		return [
			[22, ['Order' => ['length' => 2, 'width' => 4, 'depth' => 6]]],
			[11, ['Order' => ['length' => 1, 'width' => 2, 'depth' => 3]]],
			[2200, ['Order' => ['length' => 200, 'width' => 400, 'depth' => 600]]],
			[0, ['Order' => ['length' => 0, 'width' => 0, 'depth' => 0]]],
			[0, ['Order' => ['length' => null, 'width' => null, 'depth' => null]]],
		];
	}

	/**
	 * Confirm validShipping() can correctly determnine if an order should proceed
	 *
	 * @dataProvider provideValidShipping
	 * @return void
	 */
	public function testValidShipping($expected, $order, $msg = null) {
		$result = $this->Order->validShipping($order);
		$this->{'assert' . $expected}($result, $msg);
	}

	public function provideValidShipping() {
		return [
			['True', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'AA',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 44,
				]
			], 'Should be true as dimensions and weight are below FedEx limit and not US'],
			['True', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
				]
			], 'Should be true as weight is over 70 lbs, country is US, and state is not an APO'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
				]
			], 'Should be false as weight is over 70 lbs and country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 60,
					'width' => 50,
					'depth' => 2,
					'weight_oz' => 40,
				]
			], 'Should be false as dimensions are greater than 130 but country is not US'],
			['False', [
				'Order' => [
					'delivery_country' => 'Foo',
					'delivery_state' => 'VT',
					'length' => 60,
					'width' => 50,
					'depth' => 2,
					'weight_oz' => 1500,
				]
			], 'Should be true as dimensions are greater than 130 and weight is greater than 70 lbs but country is not US'],
			['True', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'VT',
					'length' => 6,
					'width' => 5,
					'depth' => 2,
					'weight_oz' => 15,
				]
			], 'Should be true as country is US and state is not an APO'],
			['False', [
				'Order' => [
					'delivery_country' => 'United States',
					'delivery_state' => 'AE',
					'length' => 2,
					'width' => 4,
					'depth' => 6,
					'weight_oz' => 1500,
				]
			], 'Should be false as weight is over 70 lbs, country is US, and state is an APO'],
		];
	}

	/**
	 * Confirm that when an order is marked as shipped, corresponding OrderData
	 * records are purged.
	 *
	 * @return	void
	 */
	public function testMarkAsShippedPurgesOrderData() {
		$id = 2;
		$this->Order->id = $id;
		$before = $this->Order->OrderData->findByOrdersId($id);

		$save = $this->Order->markAsShipped();
		$this->assertTrue($save != false, 'markAsShipped() should not return false.');
		$this->assertNotEmpty($before, 'should have at least one record for the order id');

		$after = $this->Order->OrderData->findByOrdersId($id);
		$this->assertEmpty($after, 'should have no records for the order id');
	}

	/**
	 * Confirm that getTurnaround returns the expected value from either the
	 * Tracking or Order table, depending on which one should be used.
	 *
	 * @dataProvider provideGetTurnaround
	 * @return void
	 */
	public function testGetTurnaround($orderId, $nowDate, $expected) {
		$this->Order = $this->getMockForModel('Order', array(
			'getDateTime'
		));
		$this->Order->expects($this->once())
			->method('getDateTime')
			->will($this->returnValue(new DateTime($nowDate)));

		$result = $this->Order->getTurnaround($orderId);
		$this->assertEquals($expected, $result);
	}

	public function provideGetTurnaround() {
		return array(
			array(1, '2015-12-09 17:00:00', 86400),
			array(2, '2015-12-09 17:00:00', 2678400),
			array(3, '2015-12-09 17:00:00', 27991407),
		);
	}

	/**
	 * Confirm that when an order is marked as shipped, the `turnaround_sec`
	 * value is updated.
	 *
	 * @return void
	 */
	public function testMarkAsShippedUpdatesTurnaroundSec() {
		$id = 1;
		$newStatus = 3;
		$turnAroundSec = 'Lorem ipsum d';
		$before = $this->Order->findByOrdersId($id);
		$this->assertSame(
			$turnAroundSec,
			$before['Order']['turnaround_sec'],
			'should match'
		);
		$this->assertFalse(
			is_numeric($before['Order']['turnaround_sec']),
			'should not be a number'
		);
		$this->assertEquals(1, $before['Order']['orders_status']);

		$result = $this->Order->markAsShipped($id, $newStatus);
		$this->assertSame(
			$newStatus,
			$result['Order']['orders_status'],
			'should match order_status with $newStatus'
		);

		$after = $this->Order->findByOrdersId($id);
		$this->assertEquals($newStatus, $after['Order']['orders_status']);
		$this->assertNotEquals(
			$turnAroundSec,
			$after['Order']['turnaround_sec'],
			'should not match'
		);
		$this->assertTrue(
			is_numeric($after['Order']['turnaround_sec']),
			'should be a number'
		);
	}

	/**
	 * Confirm countByInboundTracking returns the expected count
	 *
	 * @return void
	 */
	public function testCountByInboundTracking() {
		$trackingId = 'customer_1_request';
		$result = $this->Order->countByInboundTracking($trackingId);
		$this->assertEquals(1, $result);

		$trackingId = 'non-existing-tracking-id';
		$result = $this->Order->countByInboundTracking($trackingId);
		$this->assertEquals(0, $result);
	}

	/*
	 * Confirm that saveOrderForCharge correctly adds `order_total` rows if missing,
	 * as can happen with old orders. The missing rows should only be the following:
	 *
	 * * `ot_custom` (OrderStorage)
	 * * `ot_custom_1` (OrderRepack)
	 * * `ot_custom_2` (OrderBattery)
	 *
	 * The order total should not be effected by the addition of these rows unless
	 * an OrderFee is being added (which happens in this test).
	 *
	 * If `ot_fee` (OrderFee) is missing it needs to be added before Order::saveOrderForCharge()
	 * is called, and is handled by Order::addDefaultOrderFee().
	 *
	 * @return	void
	 */
	public function testSaveOrderForChargeOldOrderNoValues() {
		$orderId = 5;
		$data = [
			'submit' => 'charge',
			'Order' => [
				'orders_id' => $orderId,
				'last_modified' => '2015-09-23 14:19:51',
				'orders_status' => '4'
			],
			'OrderShipping' => [
				'value' => '10.00',
				'orders_total_id' => '33'
			],
			'OrderStorage' => [
				'value' => '0.00',
				'orders_total_id' => null
			],
			'OrderInsurance' => [
				'value' => '1.75',
				'orders_total_id' => '34'
			],
			'OrderFee' => [
				'value' => '9.95',
				'orders_total_id' => '37'
			],
			'OrderRepack' => [
				'title' => '',
				'value' => '0.00',
				'orders_total_id' => null
			],
			'OrderBattery' => [
				'title' => '',
				'value' => '0.00',
				'orders_total_id' => null
			]
		];

		$before = $this->Order->findOrderForCharge($orderId);
		$this->assertNull($before['OrderStorage']['orders_total_id']);
		$this->assertNull($before['OrderRepack']['orders_total_id']);
		$this->assertNull($before['OrderBattery']['orders_total_id']);
		$this->assertEqual(
			$data['OrderShipping']['value'] + $data['OrderInsurance']['value'],
			$before['OrderTotal']['value'],
			'Total should be the sum of OrderShipping and OrderInsurance'
		);

		$result = $this->Order->saveOrderForCharge($data);
		$this->assertTrue($result);

		$after = $this->Order->findOrderForCharge($orderId);
		$this->assertNotNull($after['OrderStorage']['orders_total_id']);
		$this->assertNotNull($after['OrderRepack']['orders_total_id']);
		$this->assertNotNull($after['OrderBattery']['orders_total_id']);
		$this->assertNotEqual($before['Order']['orders_status'], $after['Order']['orders_status']);
		$this->assertSame('0.0000', $after['OrderStorage']['value']);
		$this->assertSame('1.7500', $after['OrderInsurance']['value']);
		$this->assertSame('9.9500', $after['OrderFee']['value']);
		$this->assertEqual(
			(
				$data['OrderShipping']['value'] +
				$data['OrderInsurance']['value'] +
				$data['OrderFee']['value'] +
				$data['OrderStorage']['value'] +
				$data['OrderRepack']['value']
			),
			$after['OrderTotal']['value'],
			'Total should only increase by the value of OrderFee'
		);
	}

	/**
	 * Confirm that saveOrderForCharge correctly adds `order_total` rows if missing,
	 * as can happen with old orders and add new values if provided. The missing
	 * rows should only be the following:
	 *
	 * * `ot_custom` (OrderStorage)
	 * * `ot_custom_1` (OrderRepack)
	 * * `ot_custom_2` (OrderBattery)
	 *
	 * The order total is effected by the addition of these rows as they contain new values.
	 * Also, an OrderFee is being added.
	 *
	 * If `ot_fee` (OrderFee) is missing it needs to be added before Order::saveOrderForCharge()
	 * is called, and is handled by Order::addDefaultOrderFee().
	 *
	 * @return	void
	 */
	public function testSaveOrderForChargeOldOrderWithValues() {
		$orderId = 5;
		$data = [
			'submit' => 'charge',
			'Order' => [
				'orders_id' => $orderId,
				'last_modified' => '2015-09-23 14:19:51',
				'orders_status' => '4'
			],
			'OrderShipping' => [
				'value' => '10.00',
				'orders_total_id' => '33'
			],
			'OrderStorage' => [
				'value' => '1.00',
				'orders_total_id' => null
			],
			'OrderInsurance' => [
				'value' => '1.75',
				'orders_total_id' => '34'
			],
			'OrderFee' => [
				'value' => '9.95',
				'orders_total_id' => '37'
			],
			'OrderRepack' => [
				'title' => '',
				'value' => '2.00',
				'orders_total_id' => null
			],
			'OrderBattery' => [
				'title' => '',
				'value' => '0.00',
				'orders_total_id' => null
			]
		];

		$before = $this->Order->findOrderForCharge($orderId);
		$this->assertNull($before['OrderStorage']['orders_total_id']);
		$this->assertNull($before['OrderRepack']['orders_total_id']);
		$this->assertNull($before['OrderBattery']['orders_total_id']);
		$this->assertEqual(
			$data['OrderShipping']['value'] + $data['OrderInsurance']['value'],
			$before['OrderTotal']['value'],
			'Total should be the sum of OrderShipping and OrderInsurance'
		);

		$result = $this->Order->saveOrderForCharge($data);
		$this->assertTrue($result);

		$after = $this->Order->findOrderForCharge($orderId);
		$this->assertNotNull($after['OrderStorage']['orders_total_id']);
		$this->assertNotNull($after['OrderRepack']['orders_total_id']);
		$this->assertNotNull($after['OrderBattery']['orders_total_id']);
		$this->assertNotEqual($before['Order']['orders_status'], $after['Order']['orders_status']);
		$this->assertSame('1.0000', $after['OrderStorage']['value']);
		$this->assertSame('1.7500', $after['OrderInsurance']['value']);
		$this->assertSame('9.9500', $after['OrderFee']['value']);
		$this->assertSame('2.0000', $after['OrderRepack']['value']);
		$this->assertEqual(
			(
				$data['OrderShipping']['value'] +
				$data['OrderInsurance']['value'] +
				$data['OrderFee']['value'] +
				$data['OrderStorage']['value'] +
				$data['OrderRepack']['value']
			),
			$after['OrderTotal']['value'],
			'Total should only increase values of OrderFee, OrderStorage, and OrderRepack'
		);
	}

	/**
	 * Confirm the expected exception is thrown if the order id is invalid.
	 *
	 * @return void
	 */
	public function testSendStatusUpdateEmailOrderNotFound() {
		$this->setExpectedException('NotFoundException', 'The order was not found.');
		$this->Order->sendStatusUpdateEmail(99999);
	}

	/**
	 * Confirm the correct queued job is created for orders with orders_status
	 * equal to 2 or 3 OR any value NOT 2 or 3.
	 *
	 * @dataProvider provideSendStatusUpdateEmail
	 * @return void
	 */
	public function testSendStatusUpdateEmail($id, $method, $msg = '') {
		$Order = $this->getMockForModel('Order', [
			'taskFactory',
		]);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Order->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));
		$Task->expects($this->once())
			->method('createJob')
			->with(
				$this->identicalTo('AppEmail'),
				$this->isType('array'),
				$this->isNull(),
				$this->identicalTo($method),
				$this->isType('string')
			)
			->will($this->returnValue(true));

		$result = $Order->sendStatusUpdateEmail($id);

		$this->assertTrue($result);
	}

	public function provideSendStatusUpdateEmail() {
		return [
			[
				4,
				'Order::sendFailedPayment',
				'should match orders_status equal to 2 (awaiting payment)',
			],
			[
				2,
				'Order::sendShipped',
				'should match orders_status equal to 3 (shipped)',
			],
			[
				1,
				'Order::sendStatusUpdate',
				'should match orders_status NOT equal to 2 or 3',
			],
		];
	}
}
