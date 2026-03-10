<?php
/**
 * OrderStorage
 */

App::uses('AppModel', 'Model');

/**
 * OrderStorage Model
 *
 * @property	Order	$Order
 */
class OrderStorage extends AppModel {

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
			'title' => 'Storage Fees :',
			'class' => 'ot_custom',
			'sort_order' => 2,
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
				'rule' => array('notBlank'),
				'message' => 'Must not be empty.',
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
				'rule' => array('equalTo', 'ot_custom'),
				'message' => 'Must be "ot_custom".',
			),
		),
		'sort_order' => array(
			'equalTo' => array(
				'rule' => array('equalTo', 2),
				'message' => 'Must be "2".',
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

}
