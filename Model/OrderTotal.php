<?php
/**
 * OrderTotal
 */

App::uses('AppModel', 'Model');

/**
 * OrderTotal Model
 *
 * @property	Order	$Order
 */
class OrderTotal extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'orders_total';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'orders_total_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'title';

	/**
	 * Behaviors
	 */
	public $actsAs = array(
		'OrderDetail' => array(
			'title' => 'Total :',
			'class' => 'ot_total',
			'sort_order' => 6,
		),
	);

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'orders_total_id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'message' => 'Must be a natural number.',
			),
		),
		'orders_id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'message' => 'Must be a natural number.',
			),
		),
		'title' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 'Total :'),
				'message' => 'Must be "Total :".',
			),
		),
		'text' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Must not be empty.',
			),
		),
		'value' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				'message' => 'Must be a decimal number.',
			),
		),
		'class' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 'ot_total'),
				'message' => 'Must be "ot_total".',
			),
		),
		'sort_order' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 6),
				'message' => 'Must be "6".',
			),
		),
	);

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'orders_id',
		),
	);

	/**
	 * beforeFind
	 *
	 * @param array $query Data used to execute this query
	 * @return array The modified $query
	 */
	public function beforeFind($query) {
		$query['conditions'] = Hash::merge((array)$query['conditions'], array($this->alias . '.class' => 'ot_total'));
		return $query;
	}

	/**
	 * Calculate the order total.
	 *
	 * We don't currently add anything beyond the sub-total, so this method
	 * just calls OrderSubtotal->calculateTotal and uses it's result.
	 *
	 * @param int $orderId The order id.
	 * @return mixed The new order total.
	 */
	public function calculateTotal($orderId) {
		return $this->Order->OrderSubtotal->calculateTotal($orderId);
	}

	/**
	 * Calculate and update the order total record.
	 *
	 * This updates the OrderSubtotal record and then uses it's value,
	 *
	 * @param int $orderId The order id.
	 * @return mixed The new order total or false if saving fails.
	 */
	public function updateTotal($orderId) {
		$orderTotal = $this->findByOrdersId($orderId);
		$this->id = $orderTotal['OrderTotal']['orders_total_id'];

		$newTotal = $this->Order->OrderSubtotal->updateTotal($orderId);

		return $this->saveField('value', $newTotal) ? $newTotal : false;
	}

	/**
	 * Finds all charges for the supplied order $id
	 *
	 * @param int $orderId The order id
	 * @param bool $addFeeIfMissing Add an ot_fee if missing
	 * @return mixed
	 */
	public function findAllChargesForOrderId($orderId, $addFeeIfMissing = true) {
		$this->Behaviors->disable('OrderDetail');
		$options = array(
			'conditions' => array(
				'OrderTotal.orders_id' => $orderId,
				'OrderTotal.value >' => 0,
			),
			'order' => 'sort_order',
			'callbacks' => false,
		);
		$charges = $this->find('all', $options);
		$charges = $addFeeIfMissing ? $this->addFeeIfMissing($charges) : $charges;
		return $charges;
	}

	/**
	 * Adds an `ot_fee` record if missing. This is used on orders processed with
	 * the old system that do not have an ot_fee record, but instead added the
	 * APO Box Fee to the order total.
	 *
	 * @param array $charges The order_total records for this order.
	 * @return array The possibly modified order_total records for this order.
	 */
	public function addFeeIfMissing($charges) {
		if (array_search('ot_fee', Hash::extract($charges, '{n}.OrderTotal.class'))) {
			return $charges;
		}
		$fee = $this->calculateFee($charges);
		$charges[] = array(
			'OrderTotal' => array(
				'orders_total_id' => '000000',
				'orders_id' => '00000000',
				'title' => 'APO Box Fee :',
				'text' => '$' . sprintf('%.2f', $fee),
				'value' => sprintf('%.4f', $fee),
				'class' => 'ot_fee',
				'sort_order' => '4'
			)
		);
		usort($charges, function ($a, $b) {
			return $a['OrderTotal']['sort_order'] - $b['OrderTotal']['sort_order'];
		});
		return $charges;
	}

	/**
	 * Calculates the fee based on the charges array.
	 *
	 * @param array $charges The order_total records for this order.
	 * @return array The possibly modified order_total records for this order.
	 */
	public function calculateFee($charges) {
		$totalValue = current(Hash::extract($charges, '{n}.OrderTotal[class=/^ot_total$/].value'));
		$subTotalValue = current(Hash::extract($charges, '{n}.OrderTotal[class=/^ot_subtotal$/].value'));
		$feeValue = current(Hash::extract($charges, '{n}.OrderTotal[class=/^ot_fee$/].value'));
		$allValues = array_sum(Hash::extract($charges, '{n}.OrderTotal.value'));
		$otherValues = $allValues - ($feeValue + $subTotalValue + $totalValue);
		return $totalValue - $otherValues;
	}
}
