<?php
/**
 * OrderFee
 */

App::uses('AppModel', 'Model');

/**
 * OrderFee Model
 *
 * @property	Order	$Order
 */
class OrderFee extends AppModel {

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
			'title' => 'APO Box Fee :',
			'class' => 'ot_fee',
			'sort_order' => 4,
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
				'rule' => array('equalTo', 'APO Box Fee :'),
				'message' => 'Must be "APO Box Fee :".',
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
				'rule' => array('equalTo', 'ot_fee'),
				'message' => 'Must be "ot_fee".',
			),
		),
		'sort_order' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 4),
				'message' => 'Must be "4".',
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
	 * hasOne associations
	 *
	 * @var	array
	 */
	public $hasOne = array();

	/**
	 * Returns the Order Fee based on weight in oz.
	 *
	 * @param int $ounces The weight in ounces.
	 * @return float The fee amount.
	 */
	public function getFee($ounces) {
		$fees = $this->feeSchedule();
		ksort($fees, SORT_NUMERIC);
		$fees = array_reverse($fees, true);
		foreach ($fees as $oz => $fee) {
			if ($ounces >= $oz) {
				$value = $fee;
				break;
			}
		}

		return $value;
	}

	/**
	 * Returns fee schedule from Config.
	 *
	 * @return array
	 */
	protected function feeSchedule() {
		return Configure::read('FeeByWeight');
	}
}

