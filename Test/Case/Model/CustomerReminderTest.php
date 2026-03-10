<?php
App::uses('CustomerReminder', 'Model');

/**
 * CustomerReminder Test Case
 *
 */
class CustomerReminderTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.customer_reminder',
		'app.customer',
		'app.order',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->CustomerReminder = ClassRegistry::init('CustomerReminder');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->CustomerReminder);

		parent::tearDown();
	}

	/**
	 * Confirm that false is returned if incrementReminder() is passed any
	 * invalid data or required keys are missing.
	 *
	 * @dataProvider provideIncrementReminderReturnsFalse
	 * @return	void
	 */
	public function testIncrementReminderReturnsFalse($record, $type, $msg = '') {
		$result = $this->CustomerReminder->incrementReminder($record, $type);
		$this->assertFalse($result, $msg);
	}

	public function provideIncrementReminderReturnsFalse() {
		return array(
			array(array(), 'foo', 'Invalid $type should return false'),
			array(array(), 'partial_signup', 'Missing model key should return false'),
			array(array('Order' => array()), 'partial_signup', 'Missing `customers_id key` should return false'),
		);
	}

	/**
	 * Confirm that an existing CustomerReminder record does not exist and that
	 * a new one is created with expected data for type: awaiting_payment.
	 *
	 * @return void
	 */
	public function testIncrementReminderAddNewRecordAwaitingPayment() {
		$order = array(
			'Order' => array(
				'orders_id' => '999',
				'customers_id' => '1'
			),
			'Customer' => array(
				'billing_id' => 'Lorem ',
				'customers_email_address' => 'someone@example.com',
				'customers_firstname' => 'Lorem ipsum dolor sit amet',
				'customers_lastname' => 'Lorem ipsum dolor sit amet',
				'customers_id' => '1',
				'customers_fullname' => 'Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet'
			),
			'CustomerReminder' => array()
		);
		$options = array(
			'conditions' => array(
				'customers_id' => $order['Order']['customers_id'],
				'orders_id' => $order['Order']['orders_id'],
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertEmpty($before);

		$result = $this->CustomerReminder->incrementReminder($order, 'awaiting_payment');
		$this->assertTrue($result);

		$after = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($after);
		$this->assertEquals($order['Order']['customers_id'], $after[0]['CustomerReminder']['customers_id']);
		$this->assertEquals($order['Order']['orders_id'], $after[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(1, $after[0]['CustomerReminder']['reminder_count']);
	}

	/**
	 * Confirm that an existing CustomerReminder record's `reminder_count` field
	 * is incremented as expected for type: awaiting_payment.
	 *
	 * @return void
	 */
	public function testIncrementReminderUpdateExistingRecordAwaitingPayment() {
		$order = array(
			'Order' => array(
				'orders_id' => '4',
				'customers_id' => '1'
			),
			'Customer' => array(
				'billing_id' => 'Lorem ',
				'customers_email_address' => 'someone@example.com',
				'customers_firstname' => 'Lorem ipsum dolor sit amet',
				'customers_lastname' => 'Lorem ipsum dolor sit amet',
				'customers_id' => '1',
				'customers_fullname' => 'Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet'
			),
			'CustomerReminder' => array(
				(int) 0 => array(
					'customer_reminder_id' => '1',
					'customers_id' => '1',
					'orders_id' => '4',
					'reminder_type' => 'awaiting_payment',
					'reminder_count' => '1',
					'created' => '2015-12-09 15:53:39',
					'modified' => '2015-12-09 15:53:39'
				)
			)
		);
		$options = array(
			'conditions' => array(
				'customers_id' => $order['Order']['customers_id'],
				'orders_id' => $order['Order']['orders_id'],
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($before);
		$this->assertEquals($order['Order']['customers_id'], $before[0]['CustomerReminder']['customers_id']);
		$this->assertEquals($order['Order']['orders_id'], $before[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(1, $before[0]['CustomerReminder']['reminder_count']);

		$result = $this->CustomerReminder->incrementReminder($order, 'awaiting_payment');
		$this->assertTrue($result);

		$after = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($after);
		$this->assertEquals($order['Order']['customers_id'], $after[0]['CustomerReminder']['customers_id']);
		$this->assertEquals($order['Order']['orders_id'], $after[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(2, $after[0]['CustomerReminder']['reminder_count']);
	}

	/**
	 * Confirm that incrementReminder() returns `false` if save/update fails.
	 *
	 * @return void
	 */
	public function testIncrementReminderSaveFails() {
		$order = array(
			'Order' => array(
				'orders_id' => '999',
				'customers_id' => '1'
			),
			'Customer' => array(
				'billing_id' => 'Lorem ',
				'customers_email_address' => 'someone@example.com',
				'customers_firstname' => 'Lorem ipsum dolor sit amet',
				'customers_lastname' => 'Lorem ipsum dolor sit amet',
				'customers_id' => '1',
				'customers_fullname' => 'Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet'
			),
			'CustomerReminder' => array()
		);

		$CustomerReminder = $this->getMockForModel('CustomerReminder', array('save'));
		$CustomerReminder->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$result = $CustomerReminder->incrementReminder($order, 'awaiting_payment');
		$this->assertFalse($result);
	}

	/**
	 * Confirm that an existing CustomerReminder record does not exist and that
	 * a new one is created with expected data for type: partial_signup.
	 *
	 * @return void
	 */
	public function testIncrementReminderAddNewRecordPartialSignup() {
		$customer = array(
			'Customer' => array(
				'billing_id' => 'Lorem ',
				'customers_email_address' => 'someone@example.com',
				'customers_id' => '2',
				'customers_fullname' => 'Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet'
			),
			'CustomerReminder' => array()
		);
		$options = array(
			'conditions' => array(
				'customers_id' => $customer['Customer']['customers_id'],
				'reminder_type' => 'partial_signup',
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertEmpty($before);

		$result = $this->CustomerReminder->incrementReminder($customer, 'partial_signup');
		$this->assertTrue($result);

		$after = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($after);
		$this->assertEquals($customer['Customer']['customers_id'], $after[0]['CustomerReminder']['customers_id']);
		$this->assertEquals(0, $after[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(1, $after[0]['CustomerReminder']['reminder_count']);
	}

	/**
	 * Confirm that an existing CustomerReminder record's `reminder_count` field
	 * is incremented as expected for type: partial_signup.
	 *
	 * @return void
	 */
	public function testIncrementReminderUpdateExistingRecordPartialSignup() {
		$customer = array(
			'Customer' => array(
				'billing_id' => 'Lorem ',
				'customers_email_address' => 'someone@example.com',
				'customers_id' => '1',
				'customers_fullname' => 'Lorem ipsum dolor sit amet Lorem ipsum dolor sit amet'
			),
			'CustomerReminder' => array(
				(int) 0 => array(
					'customer_reminder_id' => '4',
					'customers_id' => '1',
					'orders_id' => '0',
					'reminder_type' => 'partial_signup',
					'reminder_count' => '2',
					'created' => '2015-12-09 15:53:39',
					'modified' => '2015-12-09 15:53:39'
				)
			)
		);
		$options = array(
			'conditions' => array(
				'customers_id' => $customer['Customer']['customers_id'],
				'reminder_type' => 'partial_signup',
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($before);
		$this->assertEquals($customer['Customer']['customers_id'], $before[0]['CustomerReminder']['customers_id']);
		$this->assertEquals(0, $before[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(2, $before[0]['CustomerReminder']['reminder_count']);

		$result = $this->CustomerReminder->incrementReminder($customer, 'partial_signup');
		$this->assertTrue($result);

		$after = $this->CustomerReminder->find('all', $options);
		$this->assertNotEmpty($after);
		$this->assertEquals($customer['Customer']['customers_id'], $after[0]['CustomerReminder']['customers_id']);
		$this->assertEquals(0, $after[0]['CustomerReminder']['orders_id']);
		$this->assertEquals(3, $after[0]['CustomerReminder']['reminder_count']);
	}

	/**
	 * Confirm that the purge() method removes the specific record.
	 *
	 * @return void
	 */
	public function testPurge() {
		$before = $this->CustomerReminder->find('all');
		$this->assertEquals(6, count($before));
		$result = $this->CustomerReminder->purge(99999);
		$this->assertTrue($result);
		$after = $this->CustomerReminder->find('all');
		$this->assertEquals(5, count($after));
	}

	/**
	 * Confirm that when passed a valid type (payment_info in this case) the
	 * customer reminder record is removed.
	 *
	 * @return void
	 */
	public function testClearRecordWithTypePaymentInfo() {
		$userId = 2;
		$options = array(
			'conditions' => array(
				'customers_id' => $userId,
				'reminder_type' => 'expired_card',
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertEquals($userId, $before[0]['CustomerReminder']['customers_id']);
		$result = $this->CustomerReminder->clearRecord($userId, 'payment_info');
		$this->assertTrue($result);
		$after = $this->CustomerReminder->find('all', $options);
		$this->assertEmpty($after);
	}

	/**
	 * Confirm that when passed a type not configured to clear customer reminder
	 * records, no updates are made and the method returns false.
	 *
	 * @return void
	 */
	public function testClearRecordWithUnusedType() {
		$userId = 2;
		$options = array(
			'conditions' => array(
				'customers_id' => $userId,
				'reminder_type' => 'expired_card',
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$this->assertEquals($userId, $before[0]['CustomerReminder']['customers_id']);
		$result = $this->CustomerReminder->clearRecord($userId, 'shipping');
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when passed a valid type (awaiting_payment in this case) the
	 * customer reminder record for the specific order is removed.
	 *
	 * @return void
	 */
	public function testClearRecordWithTypeAwaitingPayment() {
		$userId = 1;
		$orderIdToClear = 5;
		$options = array(
			'conditions' => array(
				'customers_id' => $userId,
				'reminder_type' => 'awaiting_payment',
			),
		);
		$before = $this->CustomerReminder->find('all', $options);
		$beforeOrderIds = Hash::extract($before, '{n}.CustomerReminder.orders_id');
		$this->assertTrue(in_array('5', $beforeOrderIds), 'Order ' . $orderIdToClear . ' should exist before test');
		$result = $this->CustomerReminder->clearRecord($orderIdToClear, 'awaiting_payment');
		$this->assertTrue($result);
		$after = $this->CustomerReminder->find('all', $options);
		$this->assertCount(count($before)-1, $after);
		$afterOrderIds = Hash::extract($after, '{n}.CustomerReminder.orders_id');
		$this->assertFalse(in_array('5', $afterOrderIds), 'Order ' . $orderIdToClear . ' should not exist after test');
	}
}
