<?php
/**
 * CustomersInfoShell
 */
App::uses('AppShell', 'Console/Command');

/**
 */
class CustomersInfoShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = [
		'Customer',
		'CustomersInfo',
	];

	/**
	 * getOptionParser
	 *
	 * @codeCoverageIgnore Cake core
	 * @return void
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addArgument('add_date', [
			'help' => 'Adds a record for customers without a customers_info record.',
		])->addOption('dry-run', [
			'short' => 'd',
			'help' => ('Print results but take no action'),
			'boolean' => true
		])->description('CustomerInfo commands');
		return $parser;
	}

	/**
	 * Adds a record for customers without a customers_info record consisting of
	 * the customer id and the account created date.
	 *
	 * @return void
	 */
	public function add_date() {
		$customers = $this->Customer->findMissingCustomersInfo();
		if ($customers) {
			if ($this->params['dry-run']) {
				$this->_out('DRY RUN: ' . count($customers) . ' customers would be updated.', 'warning');
				return;
			}
			$updated = $this->CustomersInfo->updateAccountCreated($customers);
			if (empty($updated)) {
				$this->_out(count($customers) . ' customers sucessfully updated with account created dates.', 'info');
			} else {
				foreach ($updated as $id) {
					$this->_out('There was an error updating account created date for customer ' . $id . '.', 'error');
				}
			}
		} else {
			$this->_out('No customer info records in need of updating.', 'info');
		}
	}
}
