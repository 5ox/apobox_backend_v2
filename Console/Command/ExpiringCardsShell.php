<?php
/**
 * ExpiringCardsShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: ExpiringCardsShell
 *
 * @see AppShell
 */
class ExpiringCardsShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = ['Customer'];

	/**
	 * Call on Customer::findExpiringCreditCards() to see if any credit card
	 * expiration email needs to be sent, and sends them if necessary.
	 *
	 * @return void
	 */
	public function main() {
		$customers = $this->Customer->findExpiringCreditCards();
		if ($customers) {
			$task = $this->taskFactory();
			foreach ($customers as $id => $customer) {
				$task->createJob('AppEmail',
					[
						'method' => 'sendCreditCardExpires',
						'recipient' => [$customer['customers_email_address'] => $customer['customers_fullname']],
						'vars' => [],
					],
					null,
					'ExpiringCardsShell::sendCreditCardExpires',
					$customer['customers_email_address']
				);
				$this->_out(
					'Credit card expiration notice sent to customer #' . $id . ' (' . $customer['customers_email_address'] . ').',
					''
				);
			}
		} else {
			$this->_out('No expiring customer cards found.', 'info');
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
