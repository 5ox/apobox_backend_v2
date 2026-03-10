<?php
/**
 * OrderInsurance
 */

App::uses('AppModel', 'Model');

/**
 * OrderInsurance Model
 *
 * @property	Order	$Order
 */
class OrderInsurance extends AppModel {

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
			'title' => 'Insurance :',
			'class' => 'ot_insurance',
			'sort_order' => 3,
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
				'rule' => array('equalTo', 'Insurance :'),
				'message' => 'Must be "Insurance :".',
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
				'rule' => array('equalTo', 'ot_insurance'),
				'message' => 'Must be "ot_insurance".',
			),
		),
		'sort_order' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 3),
				'message' => 'Must be "3".',
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

}
