<?php
App::uses('CustomerReminderShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class CustomerReminderShellTest
 */
class CustomerReminderShellTest extends ShellTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [];

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Shell = $this->getMockBuilder('CustomerReminderShell')
			->setMethods(['_out', 'taskFactory'])
			->getMock();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Dispatch, $this->Shell);
	}

	/**
	 * Confirm taskFactory can return an instance of the QueuedTask class.
	 *
	 * @return void
	 */
	public function testTaskFactory() {
		$Shell = new CustomerReminderShell();
		$this->assertInstanceOf('QueuedTask', $Shell->taskFactory());
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndChargeAllOrdersAwaitingPayment
	 * returns no results.
	 *
	 * @return void
	 */
	public function testAwaitingPaymentNoOrders() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$Order = $this->getMockForModel('Order', ['findAndChargeAllOrdersAwaitingPayment']);

		$Order->expects($this->once())
			->method('findAndChargeAllOrdersAwaitingPayment')
			->with(false)
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No customers are in need of an awaiting payment alert.'),
				$this->identicalTo('info')
			);

		$this->Shell->awaiting_payment();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndChargeAllOrdersAwaitingPayment
	 * returns results and that no queued job is added when dry-run is enabled.
	 *
	 * @return void
	 */
	public function testAwaitingPaymentWithOrdersDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$ordersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Order' => ['orders_id' => $ordersId],
				'Customer' => ['billing_id' => $billingId],
			],
		];
		$Order = $this->getMockForModel('Order', [
			'findAndChargeAllOrdersAwaitingPayment',
		]);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['create'])
			->getMock();

		$Order->expects($this->once())
			->method('findAndChargeAllOrdersAwaitingPayment')
			->with(false)
			->will($this->returnValue($data));

		$Task->expects($this->never())
			->method('createJob');

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Charge attempt or awaiting payment notice would be sent to customer ' .
					"$billingId for order #$ordersId"
				),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(PHP_EOL . 'DRY RUN'),
				$this->identicalTo('error')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of charges to be attempted or notifications to be sent: ' . count($data)
				),
				$this->identicalTo('info')
			);

		$this->Shell->awaiting_payment();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndChargeAllOrdersAwaitingPayment
	 * returns results and:
	 * * customer was not charged
	 * * incrementReminder failed
	 *
	 * @return void
	 */
	public function testAwaitingPaymentWithOrdersNotChargedNotIncremented() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$ordersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Order' => [
					'orders_id' => $ordersId,
					'charged' => false,
				],
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Order = $this->getMockForModel('Order', [
			'findAndChargeAllOrdersAwaitingPayment',
		]);
		$CustomerReminder = $this->getMockForModel('CustomerReminder', [
			'incrementReminder',
		]);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Order->expects($this->once())
			->method('findAndChargeAllOrdersAwaitingPayment')
			->with(true)
			->will($this->returnValue($data));

		$Task->expects($this->once())
			->method('createJob')
			->with(
				$this->identicalTo('AppEmail'),
				$this->isType('array'),
				$this->isNull(),
				$this->identicalTo('CustomerReminderShell::sendAwaitingPaymentAlert'),
				$this->identicalTo($billingId)
			);

		$CustomerReminder->expects($this->once())
			->method('incrementReminder')
			->with(
				$this->isType('array'),
				$this->identicalTo('awaiting_payment')
			)
			->will($this->returnValue(false));

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Awaiting payment notice sent to customer ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ') ' .
					"for order #$ordersId"
				),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(
					'WARNING: alert count for ' . $data[0]['Order']['orders_id'] . ' not incremented.'
				),
				$this->identicalTo('warning')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					PHP_EOL . 'Total number of charges completed: 0'
				),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(4))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of notifications sent: 1'
				),
				$this->identicalTo('info')
			);

		$this->Shell->awaiting_payment();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndChargeAllOrdersAwaitingPayment
	 * returns results and:
	 * * customer was charged
	 *
	 * @return void
	 */
	public function testAwaitingPaymentWithOrdersCharged() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$ordersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Order' => [
					'orders_id' => $ordersId,
					'charged' => true,
				],
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Order = $this->getMockForModel('Order', [
			'findAndChargeAllOrdersAwaitingPayment',
		]);
		$CustomerReminder = $this->getMockForModel('CustomerReminder', [
			'incrementReminder',
		]);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Order->expects($this->once())
			->method('findAndChargeAllOrdersAwaitingPayment')
			->with(true)
			->will($this->returnValue($data));

		$Task->expects($this->never())
			->method('createJob');

		$CustomerReminder->expects($this->never())
			->method('incrementReminder');

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo("Order #$ordersId charged and recorded for customer $billingId"),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(
					PHP_EOL . 'Total number of charges completed: 1'
				),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of notifications sent: 0'
				),
				$this->identicalTo('info')
			);

		$this->Shell->awaiting_payment();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllPartialSignups
	 * returns no results.
	 *
	 * @return void
	 */
	public function testPartialSignupsNoCustomers() {
		$Customer = $this->getMockForModel('Customer', ['findAllPartialSignups']);

		$Customer->expects($this->once())
			->method('findAllPartialSignups')
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No customers are in need of a partial signup alert.'),
				$this->identicalTo('info')
			);

		$this->Shell->partial_signup();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllPartialSignups
	 * returns results and dry-run is enabled.
	 *
	 * @return void
	 */
	public function testPartialSignupsWithCustomersDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$customersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'customers_id' => $customersId,
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', ['findAllPartialSignups']);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Customer->expects($this->once())
			->method('findAllPartialSignups')
			->will($this->returnValue($data));

		$Task->expects($this->never())
			->method('createJob');

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Partial signup notice sent to customer ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(PHP_EOL . 'DRY RUN'),
				$this->identicalTo('error')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of emails sent: ' . count($data)
				),
				$this->identicalTo('info')
			);

		$this->Shell->partial_signup();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllPartialSignups
	 * returns results and:
	 * * email job added to the job queue
	 * * incrementReminder failed
	 *
	 * @return void
	 */
	public function testPartialSignupsWithCustomersNotIncremented() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$customersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'customers_id' => $customersId,
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', ['findAllPartialSignups']);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();
		$CustomerReminder = $this->getMockForModel('CustomerReminder', [
			'incrementReminder',
		]);

		$Customer->expects($this->once())
			->method('findAllPartialSignups')
			->will($this->returnValue($data));

		$Task->expects($this->once())
			->method('createJob')
			->with(
				$this->identicalTo('AppEmail'),
				$this->isType('array'),
				$this->isNull(),
				$this->identicalTo('CustomerReminderShell::sendPartialSignupAlert'),
				$this->identicalTo($billingId)
			);

		$CustomerReminder->expects($this->once())
			->method('incrementReminder')
			->with(
				$this->isType('array'),
				$this->identicalTo('partial_signup')
			)
			->will($this->returnValue(false));

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Partial signup notice sent to customer ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo("WARNING: alert count for $billingId not incremented."),
				$this->identicalTo('warning')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of emails sent: ' . count($data)
				),
				$this->identicalTo('info')
			);

		$this->Shell->partial_signup();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllExpiredPartialSignups
	 * returns no results.
	 *
	 * @return void
	 */
	public function testPurgePartialSignupsNoCustomers() {
		$Customer = $this->getMockForModel('Customer', ['findAllExpiredPartialSignups']);

		$Customer->expects($this->once())
			->method('findAllExpiredPartialSignups')
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No partial signup customers deleted.'),
				$this->identicalTo('info')
			);

		$this->Shell->purge_partial_signups();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllExpiredPartialSignups
	 * returns results and that no customers are deleted by purgeExpiredPartials.
	 *
	 * @return void
	 */
	public function testPurgePartialSignupsWithCustomersDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', [
			'findAllExpiredPartialSignups',
			'purgeExpiredPartials',
		]);

		$Customer->expects($this->once())
			->method('findAllExpiredPartialSignups')
			->will($this->returnValue($data));
		$Customer->expects($this->never())
			->method('purgeExpiredPartials');

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo(
					'Partial signup customer deleted: ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);

		$this->Shell->purge_partial_signups();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAllExpiredPartialSignups
	 * returns results and customers are deleted by calling purgeExpiredPartials.
	 *
	 * @return void
	 */
	public function testPurgePartialSignupsWithCustomers() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', [
			'findAllExpiredPartialSignups',
			'purgeExpiredPartials',
		]);

		$Customer->expects($this->once())
			->method('findAllExpiredPartialSignups')
			->will($this->returnValue($data));
		$Customer->expects($this->once())
			->method('purgeExpiredPartials')
			->will($this->returnValue(true));

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo(
					'Partial signup customer deleted: ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);

		$this->Shell->purge_partial_signups();
	}

	/**
	 * Confirm the shell outputs the expected messages if findExpiredCreditCards
	 * returns no results. Also confirm the `limit` param is set correctly from
	 * the config var.
	 *
	 * @return void
	 */
	public function testExpiredCardsNoCustomers() {
		$limit = 7;
		Configure::write('Customers.expiredCardReminders.sendMaxPerRun', $limit);
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$Customer = $this->getMockForModel('Customer', ['findExpiredCreditCards']);

		$Customer->expects($this->once())
			->method('findExpiredCreditCards')
			->with($limit)
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No expired customer cards found.'),
				$this->identicalTo('info')
			);

		$this->Shell->expired_cards();
	}

	/**
	 * Confirm the shell outputs the expected messages if findExpiredCreditCards
	 * returns results and dry-run is enabled.
	 *
	 * @return void
	 */
	public function testExpiredCardsWithCustomersDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', ['findExpiredCreditCards']);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Customer->expects($this->once())
			->method('findExpiredCreditCards')
			->will($this->returnValue($data));

		$Task->expects($this->never())
			->method('createJob');

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Credit card expired notice sent to customer ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(PHP_EOL . 'DRY RUN'),
				$this->identicalTo('error')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of emails sent: ' . count($data)
				),
				$this->identicalTo('info')
			);

		$this->Shell->expired_cards();
	}

	/**
	 * Confirm the shell outputs the expected messages if findExpiredCreditCards
	 * returns results and:
	 * * email job added to the job queue
	 * * incrementReminder failed
	 *
	 * @return void
	 */
	public function testExpiredCardsWithCustomersNotIncremented() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$billingId = 'FB1234';
		$data = [
			[
				'Customer' => [
					'billing_id' => $billingId,
					'customers_email_address' => 'foo@bar.com',
					'customers_fullname' => 'Foo Bar',
				],
			],
		];
		$Customer = $this->getMockForModel('Customer', ['findExpiredCreditCards']);
		$CustomerReminder = $this->getMockForModel('CustomerReminder', ['incrementReminder']);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Customer->expects($this->once())
			->method('findExpiredCreditCards')
			->will($this->returnValue($data));

		$Task->expects($this->once())
			->method('createJob')
			->with(
				$this->identicalTo('AppEmail'),
				$this->isType('array'),
				$this->isNull(),
				$this->identicalTo('CustomerReminderShell::sendCreditCardExpired'),
				$this->identicalTo($billingId)
			);

		$CustomerReminder->expects($this->once())
			->method('incrementReminder')
			->with(
				$this->isType('array'),
				$this->identicalTo('expired_card')
			)
			->will($this->returnValue(false));

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Credit card expired notice sent to customer ' .
					$billingId . ' (' . $data[0]['Customer']['customers_email_address'] . ')'
				),
				$this->identicalTo('')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo(
					"WARNING: alert count for $billingId not incremented."
				),
				$this->identicalTo('warning')
			);
		$this->Shell->expects($this->at(3))
			->method('_out')
			->with(
				$this->identicalTo(
					'Total number of emails sent: ' . count($data)
				),
				$this->identicalTo('info')
			);

		$this->Shell->expired_cards();
	}

	/**
	 * Confirm the shell outputs the expected messages if find (awaitingPayments)
	 * returns no results.
	 *
	 * @return void
	 */
	public function testAwaitingPaymentInactiveNoOrders() {
		$Order = $this->getMockForModel('Order', ['find']);

		$Order->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('awaitingPayments'),
				$this->isType('array')
			)
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No inactive customers have orders awaiting payment.'),
				$this->identicalTo('info')
			);

		$this->Shell->awaiting_payment_inactive();
	}

	/**
	 * Confirm the shell outputs the expected messages if find (awaitingPayments)
	 * returns results.
	 *
	 * @return void
	 */
	public function testAwaitingPaymentInactiveWithOrders() {
		$ordersId = '1234567';
		$billingId = 'FB1234';
		$data = [
			[
				'Order' => ['orders_id' => $ordersId],
				'Customer' => ['billing_id' => $billingId],
			],
		];
		$Order = $this->getMockForModel('Order', ['find']);

		$Order->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('awaitingPayments'),
				$this->isType('array')
			)
			->will($this->returnValue($data));

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo("Order #$ordersId is awaiting payment but customer $billingId is inactive."),
				$this->identicalTo('')
			);

		$this->Shell->awaiting_payment_inactive();
	}
}
