<?php
/**
 * OrderDataShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: OrderDataShell
 *
 * @see AppShell
 */
class OrderDataShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = [
		'OrderData',
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
		$parser->addArgument('purge', [
			'help' => 'Purges all data of with the supplied data type',
			'choices' => ['fedex-zpl'],
			'required' => true
		])->addOption('dry-run', [
			'short' => 'd',
			'help' => ('Print results but take no action'),
			'boolean' => true
		])->description('OrderData commands');
		return $parser;
	}

	/**
	 * Removes label data for orders that are older than the amount of weeks
	 * specified in Configure var `ShippingApis.Fedex.label.purge`.
	 *
	 * @return void
	 */
	public function purge() {
		$purgeDate = Configure::read('ShippingApis.Fedex.label.purge');
		$date = date_create()->modify("$purgeDate weeks ago");
		$options = [
			'conditions' => [
				'data_type' => $this->args[0],
				'created <' => $date->format('Y-m-d H:i:s'),
			],
			'fields' => [
				'orders_data_id',
				'orders_id',
			],
		];
		$records = $this->OrderData->find('list', $options);
		if ($records) {
			foreach ($records as $id => $orderId) {
				if (!$this->param('dry-run')) {
					$this->OrderData->delete($id, false);
				}
				$this->_out('Removing id ' . $id . ' for order #' . $orderId, 'info');
			}
			if ($this->param('dry-run')) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
			}
		} else {
			$this->_out('No records are in need of purging.', 'info');
		}
	}
}
