<?php
/**
 * CustomOrdersMatchShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: CustomOrdersMatchShell
 *
 * @see AppShell
 */
class CustomOrdersMatchShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = [
		'CustomPackageRequest',
	];

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
		$parser->addArgument('orders', [
			'help' => 'Updates custom request ids with order ids',
		])->addArgument('status', [
			'help' => 'Updates custom request status id to match order status',
		])->addOption('dry-run', [
			'short' => 'd',
			'help' => ('Print results but take no action'),
			'boolean' => true
		])->description('Updates custom requests');
		return $parser;
	}

	/**
	 * Finds custom orders with `orders_id` set to 0 and searches for orders with
	 * matching `tracking_id`. If a match is found, the custom order `order_id` is
	 * updated with the matching order number.
	 *
	 * @return void
	 */
	public function orders() {
		$matches = $this->CustomPackageRequest->findMatchingRequests($this->params['dry-run']);
		if (!empty($matches)) {
			foreach ($matches as $id => $orderId) {
				$this->_out("Update ID $id with order number $orderId", 'comment');
			}
		} else {
			$this->_out('No matching records found.', 'info');
		}
	}

	/**
	 * status
	 *
	 * @return void
	 */
	public function status() {
		$matches = $this->CustomPackageRequest->findAndUpdateStatus($this->params['dry-run']);
		if (!empty($matches)) {
			foreach ($matches as $id => $orderStatus) {
				$this->_out("Update ID $id to order status $orderStatus", 'comment');
			}
		} else {
			$this->_out('No matching records found.', 'info');
		}
	}
}
