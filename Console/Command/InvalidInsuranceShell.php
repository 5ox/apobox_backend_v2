<?php
/**
 * InvalidInsuranceShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: InvalidInsuranceShell
 *
 * @see AppShell
 */
class InvalidInsuranceShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = [
		'Order',
		'OrderTotal',
		'Insurance',
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
		$parser->addOption('dry-run', [
			'short' => 'd',
			'help' => ('Print results but take no action'),
			'boolean' => true
		])->description('Checks for and attempts to fix invalid insurance amounts');
		return $parser;
	}

	/**
	 * Checks for suspicious `insurance_coverage` values in order records and lists
	 * them (dry-run) or attempts to update them. If the Order `insurance_coverage`
	 * amount is sucessfully updated the OrderTotal insurance fields are updated as
	 * well along with a new order total.
	 *
	 * Suspicious Order `insurance_coverage` data is flagged by having a comma, like
	 * `1,000` or non numeric characters.
	 *
	 * @return void
	 */
	public function main() {
		$options = [
			'fields' => [
				'orders_id',
				'insurance_coverage',
			],
			'conditions' => [
				'OR' => [
					'insurance_coverage LIKE' => '%,%',
					'insurance_coverage REGEXP' => '^[A-Za-z0-9]+$',
				],
			],
		];
		$matches = $this->Order->find('all', $options);
		if (!empty($matches)) {
			$num = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
			foreach ($matches as $match) {
				$amount = $num->parse(str_replace('$', '', $match['Order']['insurance_coverage']));
				if ($amount) {
					$amount = number_format($amount, 2, '.', '');
					$this->Order->id = $match['Order']['orders_id'];
					$saved = true;
					if (!$this->params['dry-run']) {
						$saved = $this->Order->saveField('insurance_coverage', $amount);
					}
					if ($saved) {
						$total = true;
						if (!$this->params['dry-run']) {
							$total = $this->updateOrderTotal($match['Order']['orders_id'], $amount);
						}
						if ($total) {
							$this->_out('Order #' . $match['Order']['orders_id'] . ' updated from ' . $match['Order']['insurance_coverage'] . ' to ' . $amount, 'comment');
						} else {
							$this->_out('Order #' . $match['Order']['orders_id'] . ' updated from ' . $match['Order']['insurance_coverage'] . ' to ' . $amount . ' but OrderTotal update failed', 'error');
						}
					} else {
						$this->_out('Order #' . $match['Order']['orders_id'] . ' failed to update from ' . $match['Order']['insurance_coverage'] . ' to ' . $amount, 'error');
					}
				} else {
					$this->_out('Order #' . $match['Order']['orders_id'] . ' cannot be automatically updated from \'' . $match['Order']['insurance_coverage'] . "'", 'warning');
				}
			}
			if ($this->params['dry-run']) {
				$this->_out(PHP_EOL . 'DRY RUN', 'error');
			}
		} else {
			$this->_out('No suspicious insurance_coverage records found.', 'info');
		}
	}

	/**
	 * Updates OrderTotal insurance related fields and calculates new order
	 * subtotal and total.
	 *
	 * @param int $orderId The order id
	 * @param float $amount The new insurance amount
	 * @return mixed The new total if success, bool false if failure
	 */
	protected function updateOrderTotal($orderId, $amount) {
		$insurance = $this->Insurance->getFeeForCoverageAmount($amount);

		$num = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$txtAmount = $num->formatCurrency($insurance, 'USD');
		$valAmount = number_format($insurance, 4, '.', '');

		$fields = [
			'OrderTotal.text' => "'$txtAmount'",
			'OrderTotal.value' => $valAmount,
		];
		$conditions = [
			'OrderTotal.orders_id' => $orderId,
			'OrderTotal.class' => 'ot_insurance',
		];

		if ($this->OrderTotal->updateAll($fields, $conditions)) {
			return $this->OrderTotal->updateTotal($orderId);
		}
		return false;
	}
}
