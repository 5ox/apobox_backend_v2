<?php
/**
 * OrderStatus
 */

App::uses('AppModel', 'Model');

/**
 * OrderStatus Model
 *
 */
class OrderStatus extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'orders_status';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'orders_status_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'orders_status_name';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'language_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'language_id must be numeric.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'orders_status_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Name cannot be empty.',
			),
		),
	);

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array();

	/**
	 * hasOne associations
	 *
	 * @var	array
	 */
	public $hasOne = array();

	/**
	 * hasMany associations
	 *
	 * @var	array
	 */
	public $hasMany = array(
		'OrderStatusHistory' => array(
			'foreignKey' => 'orders_status_id',
		),
	);

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();
}
