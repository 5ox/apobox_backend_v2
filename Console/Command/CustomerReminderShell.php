<?php
/**
 * CustomerReminderShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: CustomerReminderShell
 *
 * @see AppShell
 */
class CustomerReminderShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = array(
		'Order',
		'Customer',
		'CustomerReminder',
	);

	/**
	 * getOptionParser
	 *
	 * Define command line options for automatic processing and enforcement.
	 *
	 * @codeCoverageIgnore Cake core
	 * @access	public
	 * @return	mixed
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addArgument('awaiting_payment', [
			'help' => 'Checks for and attempts to charge orders awaiting payment',
		])->addArgument('awaiting_payment_inactive', [
			'help' => 'Outputs all orders in awaiting payment status for inactive customers',
		])->addArgument('partial_signup', [
			'help' => 'Checks for customers with incomplete address data',
		])->addArgument('purge_partial_signups', [
			'help' => 'Deletes customers with incomplete address data'
		])->addArgument('expired_cards', [
			'help' => 'Checks for expired customer credit cards'
		])->addOption('dry-run', [
			'short' => 'd',
			'help' => ('Print results but take no action'),
			'boolean' => true
		])->description('Customer Reminder commands');
		return $parser;
	}

	/**
	 * Finds all orders awaiting payment that either have no CustomerReminder
	 * record or `CustomerReminder.reminder_count` less than the specified limit.
	 * If found, a charge is attempted for the order. If the charge fails, the
	 * customer is sent an awaiting payment notification.
	 *
	 * Configure variable: Orders.paymentReminders
	 *
	 * @return void
	 */
	public function awaiting_payment() {
		$charge = !$this->params['dry-run'] ? true : false;
		$orders = $this->Order->findAndChargeAllOrdersAwaitingPayment($charge);
		if ($orders) {
			$task = $this->taskFactory();
			$emailCount = 0;
			$chargeCount = 0;
			foreach ($orders as $order) {
				if (!$this->params['dry-run']) {
					if (!$order['Order']['charged']) {
						$task->createJob('AppEmail',
							[
								'method' => 'sendAwaitingPaymentAlert',
								'recipient' => [$order['Customer']['customers_email_address'] => $order['Customer']['customers_fullname']],
								'vars' => ['order' => $order],
							],
							null,
							'CustomerReminderShell::sendAwaitingPaymentAlert',
							$order['Customer']['billing_id']
						);
						$this->_out(
							'Awaiting payment notice sent to customer ' .
							$order['Customer']['billing_id'] . ' (' .
							$order['Customer']['customers_email_address'] . ') for order #' .
							$order['Order']['orders_id'],
							'info'
						);
						if (!$this->CustomerReminder->incrementReminder($order, 'awaiting_payment')) {
							$this->_out(
								'WARNING: alert count for ' . $order['Order']['orders_id'] . ' not incremented.',
								'warning'
							);
						}
						$emailCount++;
					} else {
						$this->_out(
							'Order #' . $order['Order']['orders_id'] . ' charged and recorded for ' .
							'customer ' . $order['Customer']['billing_id'],
							'info'
						);
						$chargeCount++;
					}
				} else {
					$this->_out(
						'Charge attempt or awaiting payment notice would be sent to customer ' .
						$order['Customer']['billing_id'] . ' for order #' . $order['Order']['orders_id'],
						'info'
					);
					$emailCount++;
				}
			}
			if ($this->params['dry-run']) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
				$this->_out('Total number of charges to be attempted or notifications to be sent: ' . $emailCount, 'info');
			} else {
				$this->_out(PHP_EOL . 'Total number of charges completed: ' . $chargeCount, 'info');
				$this->_out('Total number of notifications sent: ' . $emailCount, 'info');
			}
		} else {
			$this->_out('No customers are in need of an awaiting payment alert.', 'info');
		}
	}

	/**
	 * Checks for customers with incomplete address data (partial signup) and
	 * emails them if their `CustomerReminder.reminder_count` is below the
	 * configured limit.
	 *
	 * Configure variable: Customers.signupReminders
	 *
	 * @return void
	 */
	public function partial_signup() {
		$customers = $this->Customer->findAllPartialSignups();
		if ($customers) {
			$task = $this->taskFactory();
			$count = 0;
			foreach ($customers as $customer) {
				if (!$this->params['dry-run']) {
					$task->createJob('AppEmail',
						[
							'method' => 'sendPartialSignupAlert',
							'recipient' => [$customer['Customer']['customers_email_address'] => $customer['Customer']['customers_fullname']],
							'vars' => ['customer' => $customer],
						],
						null,
						'CustomerReminderShell::sendPartialSignupAlert',
						$customer['Customer']['billing_id']
					);
				}
				$this->_out(
					'Partial signup notice sent to customer ' .
					$customer['Customer']['billing_id'] . ' (' .
					$customer['Customer']['customers_email_address'] . ')',
					''
				);
				if (!$this->params['dry-run'] && !$this->CustomerReminder->incrementReminder($customer, 'partial_signup')) {
					$this->_out(
						'WARNING: alert count for ' . $customer['Customer']['billing_id'] . ' not incremented.',
						'warning'
					);
				}
				$count++;
			}
			if ($this->params['dry-run']) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
			}
			$this->_out('Total number of emails sent: ' . $count, 'info');
		} else {
			$this->_out('No customers are in need of a partial signup alert.', 'info');
		}
	}

	/**
	 * Finds all customers with partial signups (missing address data) that
	 * have existed incomplete for a configured number of weeks. If found,
	 * the customer and customer_reminder data is deleted.
	 *
	 * @return void
	 */
	public function purge_partial_signups() {
		$customers = $this->Customer->findAllExpiredPartialSignups();
		if ($customers) {
			$count = 0;
			foreach ($customers as $customer) {
				if (!$this->params['dry-run']) {
					$this->Customer->purgeExpiredPartials($customers);
				}
				$this->_out(
					'Partial signup customer deleted: ' .
					$customer['Customer']['billing_id'] . ' (' .
					$customer['Customer']['customers_email_address'] . ')',
					''
				);
				$count++;
			}
			if ($this->params['dry-run']) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
			}
			$this->_out('Total number of customers deleted: ' . $count, 'info');
		} else {
			$this->_out('No partial signup customers deleted.', 'info');
		}
	}

	/**
	 * Call on Customer::findExpiredCreditCards() to see if any credit cards
	 * have expired and send an alert email. The alert is only sent once.
	 *
	 * @return void
	 */
	public function expired_cards() {
		$limit = Configure::read('Customers.expiredCardReminders.sendMaxPerRun');
		$limit = ($this->params['dry-run'] ? null : $limit);
		$customers = $this->Customer->findExpiredCreditCards($limit);
		if ($customers) {
			$task = $this->taskFactory();
			$count = 0;
			foreach ($customers as $customer) {
				if (!$this->params['dry-run']) {
					sleep(Configure::read('Customers.expiredCardReminders.sendDelaySeconds'));
					$task->createJob('AppEmail',
						[
							'method' => 'sendCreditCardExpired',
							'recipient' => [$customer['Customer']['customers_email_address'] => $customer['Customer']['customers_fullname']],
							'vars' => ['customer' => $customer],
						],
						null,
						'CustomerReminderShell::sendCreditCardExpired',
						$customer['Customer']['billing_id']
					);
				}
				$this->_out(
					'Credit card expired notice sent to customer ' .
					$customer['Customer']['billing_id'] . ' (' .
					$customer['Customer']['customers_email_address'] . ')',
					''
				);
				if (!$this->params['dry-run'] && !$this->CustomerReminder->incrementReminder($customer, 'expired_card')) {
					$this->_out(
						'WARNING: alert count for ' . $customer['Customer']['billing_id'] . ' not incremented.',
						'warning'
					);
				}
				$count++;
			}
			if ($this->params['dry-run']) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
			}
			$this->_out('Total number of emails sent: ' . $count, 'info');
		} else {
			$this->_out('No expired customer cards found.', 'info');
		}
	}

	/**
	 * Outputs all orders in awaiting payment status for customers with inactive
	 * accounts.
	 *
	 * @return void
	 */
	public function awaiting_payment_inactive() {
		$options = [
			'conditions' => [
				'Customer.is_active' => 0,
			],
			'contain' => [
				'Customer',
			],
		];
		$orders = $this->Order->find('awaitingPayments', $options);
		if ($orders) {
			foreach ($orders as $order) {
				$this->_out(
					'Order #' . $order['Order']['orders_id'] . ' is awaiting payment but customer ' .
					$order['Customer']['billing_id'] . ' is inactive.',
					''
				);
			}
		} else {
			$this->_out('No inactive customers have orders awaiting payment.', 'info');
		}
	}

	/**
	 * Instantiates and returns an instance of Queue.QueuedTask
	 *
	 * @return object A QueuedTask object from the Queue plugin
	 */
	public function taskFactory() {
		return ClassRegistry::init('Queue.QueuedTask');
	}
}
