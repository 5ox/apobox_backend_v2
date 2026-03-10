<?php
/**
 * OrderSubtotal
 */

App::uses('AppModel', 'Model');

/**
 * OrderSubtotal Model
 *
 * @property	Order	$Order
 */
class OrderSubtotal extends AppModel {

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
			'title' => 'Subtotal :',
			'class' => 'ot_subtotal',
			'sort_order' => 5,
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
				'rule' => array('equalTo', 'Subtotal :'),
				'message' => 'Must be "Subtotal :".',
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
				'rule' => array('equalTo', 'ot_subtotal'),
				'message' => 'Must be "ot_subtotal".',
			),
		),
		'sort_order' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 5),
				'message' => 'Must be "5".',
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
			'foreignKey' => 'orders_id',
		),
	);

	/**
	 * Calculate the order subtotal.
	 *
	 * @param int $orderId The order id.
	 * @return mixed The new order total.
	 */
	public function calculateTotal($orderId) {
		$conditions = array('orders_id' => $orderId);
		$shipping = $this->Order->OrderShipping->field('value', $conditions);
		$storage = $this->Order->OrderStorage->field('value', $conditions);
		$insurance = $this->Order->OrderInsurance->field('value', $conditions);
		$fee = $this->Order->OrderFee->field('value', $conditions);
		$repack = $this->Order->OrderRepack->field('value', $conditions);
		$battery = $this->Order->OrderBattery->field('value', $conditions);
		$return = $this->Order->OrderReturn->field('value', $conditions);
		$misaddressed = $this->Order->OrderMisaddressed->field('value', $conditions);
		$shipToUS = $this->Order->OrderShipToUS->field('value', $conditions);

		return $shipping + $storage + $insurance + $fee + $repack + $battery + $return + $misaddressed + $shipToUS;
	}

	/**
	 * Calculate and update the order subtotal record.
	 *
	 * @param int $orderId The order id.
	 * @return mixed The new order subtotal or false if saving fails.
	 */
	public function updateTotal($orderId) {
		$orderSubtotal = $this->findByOrdersId($orderId);
		$this->id = $orderSubtotal['OrderSubtotal']['orders_total_id'];

		$newTotal = $this->calculateTotal($orderId);

		return $this->saveField('value', $newTotal) ? $newTotal : false;
	}

}
