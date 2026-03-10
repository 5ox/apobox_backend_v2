<?php
App::uses('CustomPackageRequest', 'Model');

/**
 * CustomPackageRequest Test Case
 *
 */
class CustomPackageRequestTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.custom_order',
		'app.customer',
		'app.address',
		'app.zone',
		'app.order',
		'app.order_status',
		'app.order_status_history',
		'app.password_request',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->CustomPackageRequest = ClassRegistry::init('CustomPackageRequest');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->CustomPackageRequest);

		parent::tearDown();
	}

	/**
	 * Tests all validation except the following which are tested separately.
	 *
	 * - billing_id
	 * - order_add_date
	 *
	 * @dataProvider validationProvider
	 */
	public function testValidationMainNew($field, $value, $pass) {
		$data = array(
			'CustomPackageRequest' => array(
				$field => $value,
			),
		);
		if ($field == 'billing_id') {
			$data['CustomPackageRequest']['customers_id'] = 1;
		}

		$this->CustomPackageRequest->create();
		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates($data);

		$test = ($pass) ? 'assertArrayNotHasKey' : 'assertArrayHasKey';
		$this->{$test}($field, $this->CustomPackageRequest->validationErrors);
	}

	/**
	 * Tests all validation except the following which are tested separately.
	 *
	 * - billing_id
	 * - order_add_date
	 *
	 * @dataProvider validationProvider
	 */
	public function testValidationMainExists($field, $value, $pass) {
		$data = array(
			'CustomPackageRequest' => array(
				'custom_orders_id' => 1,
				$field => $value,
			),
		);
		if ($field == 'billing_id') {
			$data['CustomPackageRequest']['customers_id'] = 1;
		}

		$this->CustomPackageRequest->create();
		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates($data);

		$test = ($pass) ? 'assertArrayNotHasKey' : 'assertArrayHasKey';
		$this->{$test}($field, $this->CustomPackageRequest->validationErrors);
	}

	public function validationProvider() {
		$tests = array(
			array('tracking_id', 'ABCD12345678', true),
			array('tracking_id', '%^$&', false),
			array('tracking_id', 'random text with spaces', false),
			array('tracking_id', '', false),
			array('tracking_id', '1234567890', false), // existing order

			array('package_status', '1', true),
			array('package_status', '3', true),
			array('package_status', '', false),
			array('package_status', 'not_a_number', false),

			array('package_repack', 'yes', true),
			array('package_repack', 'no', true),
			array('package_repack', '', false),
			array('package_repack', 'random', false),

			array('insurance_coverage', '123.45', true),
			array('insurance_coverage', 123.45, true),
			array('insurance_coverage', '', true),
			array('insurance_coverage', 0, true),
			array('insurance_coverage', 'text', false),

			array('mail_class', 'priority', true),
			array('mail_class', 'parcel', true),
			array('mail_class', 'BAD INPUT', false),
			array('mail_class', '', false),

			array('instructions', '', true),
			array('instructions', 'akajsdflkajsf kajsdfkasdjfla ', true),
		);

		$naturalNumberTests = array(
			'custom_orders_id' => false,
			'customers_id' => false,
			'orders_id' => array('zero' => true, 'empty' => false),
		);
		foreach ($naturalNumberTests as $field => $allow) {
			$tests = array_merge($tests, array(
				array($field, '12345', true),
				array($field, 12345, true),
				array($field, 'BAD INPUT', false),
				array($field, '12345.34', false),
				array($field, 12345.34, false),
				array($field, 0, (bool)$allow['zero']),
				array($field, '', (bool)$allow['empty']),
			));
		}

		return $tests;
	}

	/**
	 * Billing id is derived from the customers_id, so records must exist to
	 * populate the billing_id for testing.
	 *
	 * @dataProvider validationBillingIdProvider
	 */
	public function testValidationBillingId($customersId, $pass) {
		$data = array(
			'CustomPackageRequest' => array(
				'customers_id' => $customersId,
			),
		);

		$this->CustomPackageRequest->create();
		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates();

		$test = ($pass) ? 'assertArrayNotHasKey' : 'assertArrayHasKey';
		$this->{$test}('billing_id', $this->CustomPackageRequest->validationErrors);
	}

	public function validationBillingIdProvider() {
		return array(
			array(1, false), //invalid
			array(2, true),  //valid
			array(3, false), //empty
		);
	}

	/**
	 * Order_date_add is generated in the beforeValidate
	 */
	public function testValidationOrderAddDate() {
		$data = array(
			'CustomPackageRequest' => array(
				'customers_id' => 1,
			),
		);

		$this->CustomPackageRequest->create();
		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates();

		$this->assertArrayNotHasKey('order_add_date', $this->CustomPackageRequest->validationErrors);
	}

	public function testBeforeValidateNew() {
		$data = array(
			'CustomPackageRequest' => array(
				'customers_id' => 2,
			),
		);

		$this->CustomPackageRequest->create();
		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates();
		$dataNew = $this->CustomPackageRequest->data['CustomPackageRequest'];

		$this->assertArrayNotHasKey('billing_id', $this->CustomPackageRequest->validationErrors);
		$this->assertArrayHasKey('package_status', $this->CustomPackageRequest->validationErrors);
		$this->assertArrayNotHasKey('order_add_date', $this->CustomPackageRequest->validationErrors);
		$this->assertEquals('IB1234', $dataNew['billing_id']);
		$this->assertInstanceOf('DateTime', date_create($dataNew['order_add_date']));
	}

	public function testBeforeValidateExisting() {
		$data = array(
			'CustomPackageRequest' => array(
				'custom_orders_id' => 1,
				'package_status' => 2,
			),
		);

		$this->CustomPackageRequest->set($data);
		$result = $this->CustomPackageRequest->validates();
		$dataNew = $this->CustomPackageRequest->data['CustomPackageRequest'];

		$this->assertArrayNotHasKey('billing_id', $dataNew);
		$this->assertArrayNotHasKey('order_add_date', $this->CustomPackageRequest->validationErrors);
		$this->assertEquals(2, $dataNew['package_status']);
	}

	public function testSaveBadData() {
		$data = array(
			'CustomPackageRequest' => array(
				'customers_id' => 1,
				'tracking_id' => '12392193849385938',
				'orders_id' => 1,
				'package_status' => 1,
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'BAD INPUT',
				'instructions' => '',
				'order_add_date' => '2015-02-11 18:22:32',
			),
		);

		$requestCountBefore = $this->CustomPackageRequest->find('count');

		$this->CustomPackageRequest->create();
		$result = $this->CustomPackageRequest->save($data);

		$requestCountAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals(
			$requestCountBefore,
			$requestCountAfter,
			'Record should not have been created'
		);
		$this->assertArrayHasKey(
			'mail_class',
			$this->CustomPackageRequest->validationErrors,
			'Mail class validation should have failed'
		);
	}

	/**
	 * Tests that findOpen finds records with the correct customerId, or
	 * returns an empty array if nothing is found.
	 *
	 * @dataProvider findOpenProvider
	 *
	 * @return	void
	 */
	public function testFindOpen($customerId, $statusOnly, $pass) {
		$result = $this->CustomPackageRequest->findOpen($customerId, $statusOnly);

		$test = ($pass) ? 'assertArrayHasKey' : 'assertArrayNotHasKey';
		$this->{$test}(0, $result);
		if ($pass) {
			$this->{$test}('customers_id', $result[0]['CustomPackageRequest']);
			$this->assertEquals($customerId, $result[0]['CustomPackageRequest']['customers_id']);
		} else {
			$this->assertEmpty($result);
		}
	}

	public function findOpenProvider() {
		return array(
			array(1, null, true),
			array(2, null, true),
			array(1234567, null, false),
			array(null, null, false),
			array(1, 1, true),
			array(2, 3, false),
		);
	}

	/**
	 * Confirm that when a custom_orders_id is passed in $data a record's
	 * orders_id is correctly updated.
	 *
	 * @return void
	 */
	public function testUpdateOrderIdWithDataSuccess() {
		$id = 2;
		$data = array(
			'CustomPackageRequest' => array(
				'custom_orders_id' => $id,
			),
		);
		$orderId = '1234567';

		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $before['CustomPackageRequest']['orders_id']);

		$result = $this->CustomPackageRequest->updateOrderId($data, $orderId);
		$this->assertArrayHasKey(key($data), $result);
		$this->assertSame($orderId, $result['CustomPackageRequest']['orders_id']);

		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame($orderId, $after['CustomPackageRequest']['orders_id']);
	}

	/**
	 * Confirm that when a custom_orders_id is NOT passed in $data a record's
	 * orders_id is NOT updated.
	 *
	 * @return void
	 */
	public function testUpdateOrderIdWithNoDataSuccess() {
		$id = 2;
		$data = array();
		$orderId = '1234567';

		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $before['CustomPackageRequest']['orders_id']);

		$result = $this->CustomPackageRequest->updateOrderId($data, $orderId);
		$this->assertTrue($result);

		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $after['CustomPackageRequest']['orders_id']);
	}

	/**
	 * Confirm that boolean false is returned if saving a record fails.
	 *
	 * @return void
	 */
	public function testUpdateOrderIdSaveFails() {
		$id = 2;
		$data = array(
			'CustomPackageRequest' => array(
				'custom_orders_id' => $id,
			),
		);
		$orderId = '1234567';
		$model = $this->getMockForModel('CustomPackageRequest', array('save'));
		$model->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$result = $model->updateOrderId($data, $orderId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that at least one record can be found, and that if $dryRun is true
	 * no records are modified.
	 *
	 * @return void
	 */
	public function testFindMatchingRequestsDryRun() {
		$id = 3;
		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $before['CustomPackageRequest']['orders_id']);
		$result = $this->CustomPackageRequest->findMatchingRequests(true);
		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $after['CustomPackageRequest']['orders_id']);
		$this->assertArrayHasKey($id, $result);
	}

	/**
	 * Confirm that at least one record can be found, and that if $dryRun is false
	 * a record is updated to the expected value.
	 *
	 * @return void
	 */
	public function testFindMatchingRequests() {
		$id = 3;
		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('0', $before['CustomPackageRequest']['orders_id']);
		$result = $this->CustomPackageRequest->findMatchingRequests();
		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('9', $after['CustomPackageRequest']['orders_id']);
		$this->assertArrayHasKey($id, $result);
	}

	/**
	 * Confirm that when an `Order.orders_status` value does not match a
	 * `CustomPackageRequest.package_status` the values are update to match.
	 *
	 * @return void
	 */
	public function testUpdatePackageStatusToOrderStatus() {
		$orderId = 9;
		$orderStatus = '1';
		$beforeRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$beforeOrder = $this->CustomPackageRequest->Order->findByOrdersId($orderId);
		$this->assertSame($orderStatus, $beforeOrder['Order']['orders_status']);
		$this->assertNotSame($orderStatus, $beforeRequest['CustomPackageRequest']['package_status']);

		$result = $this->CustomPackageRequest->updatePackageStatusToOrderStatus($orderId);
		$this->assertArrayHasKey('CustomPackageRequest', $result);

		$afterRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$afterOrder = $this->CustomPackageRequest->Order->findByOrdersId($orderId);
		$this->assertSame($orderStatus, $afterOrder['Order']['orders_status']);
		$this->assertSame($orderStatus, $afterRequest['CustomPackageRequest']['package_status']);
	}

	/**
	 * Confirm that when no custom package requests exists for an order no
	 * records are updated and `updatePackageStatusToOrderStatus()` returns false.
	 *
	 * @return void
	 */
	public function testUpdatePackageStatusToOrderStatusNoChange() {
		$orderId = 4;
		$beforeRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$this->assertEmpty($beforeRequest);

		$result = $this->CustomPackageRequest->updatePackageStatusToOrderStatus($orderId);
		$this->assertFalse($result);

		$afterRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$this->assertEmpty($afterRequest);
	}

	/**
	 * Confirm that when an update should occur but `CustomPackageRequest::saveField()`
	 * failes no update actually happens and `updatePackageStatusToOrderStatus()` returns
	 * false.
	 *
	 * @return void
	 */
	public function testUpdatePackageStatusToOrderStatusSaveFails() {
		$orderId = 9;
		$orderStatus = '1';
		$model = $this->getMockForModel('CustomPackageRequest', array('saveField'));
		$model->expects($this->once())
			->method('saveField')
			->will($this->returnValue(false));

		$beforeRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$beforeOrder = $this->CustomPackageRequest->Order->findByOrdersId($orderId);
		$this->assertSame($orderStatus, $beforeOrder['Order']['orders_status']);
		$this->assertNotSame($orderStatus, $beforeRequest['CustomPackageRequest']['package_status']);

		$result = $model->updatePackageStatusToOrderStatus($orderId);
		$this->assertFalse($result);

		$afterRequest = $this->CustomPackageRequest->findByOrdersId($orderId);
		$this->assertSame(
			$beforeRequest['CustomPackageRequest']['package_status'],
			$beforeRequest['CustomPackageRequest']['package_status']
		);
	}

	/**
	 * Confirm that at least one record can be found, and that if $dryRun is true
	 * no records are modified.
	 *
	 * @return void
	 */
	public function testFindAndUpdateStatusDryRun() {
		$id = 4;
		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('3', $before['CustomPackageRequest']['package_status']);
		$result = $this->CustomPackageRequest->findAndUpdateStatus(true);
		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('3', $after['CustomPackageRequest']['package_status']);
		$this->assertArrayHasKey($id, $result);
	}

	/**
	 * Confirm that at least one record can be found, and that if $dryRun is false
	 * a record is updated to the expected value.
	 *
	 * @return void
	 */
	public function testFindAndUpdateStatus() {
		$id = 4;
		$before = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('3', $before['CustomPackageRequest']['package_status']);
		$result = $this->CustomPackageRequest->findAndUpdateStatus();
		$after = $this->CustomPackageRequest->findByCustomOrdersId($id);
		$this->assertSame('1', $after['CustomPackageRequest']['package_status']);
		$this->assertArrayHasKey($id, $result);
	}

	/**
	 * Confirm the method can find custom package requests with the required
	 * fields only.
	 *
	 * @return void
	 */
	public function testFindAllForOrder() {
		$order = [
			['Order' => [ 'orders_id' => 1]],
			['Order' => [ 'orders_id' => 2]],
			['Order' => [ 'orders_id' => 9]],
			['Order' => [ 'orders_id' => 999]],
		];

		$result = $this->CustomPackageRequest->findAllForOrder($order);
		$this->assertNotEmpty($result);
		$this->assertSame(2, count($result));
		$this->assertArrayHasKey('CustomPackageRequest', $result[0]);
		$this->assertArrayNotHasKey('tracking_id', $result[0]['CustomPackageRequest']);
		$this->assertSame(2, count($result[0]['CustomPackageRequest']));
	}
}
