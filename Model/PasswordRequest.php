<?php
/**
 * PasswordRequest
 */

App::uses('AppModel', 'Model');

/**
 * PasswordRequest Model
 *
 * @property	Customer	$Customer
 * @property	User	$User
 */
class PasswordRequest extends AppModel {

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				'message' => 'ID must be a UUID.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'customer_id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'message' => 'Customer ID must be a positive interger.',
			),
		),
	);

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array(
		'Customer' => array(
			'className' => 'Customer',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		),
		/*
		'Admin' => array(
			'className' => 'Admin',
			'foreignKey' => 'admin_id',
		),
		*/
	);

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
	public $hasMany = array();

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * A ISO 8601 time interval string sutable for passign to DateInterval::__construct
	 */
	protected $validFor = 'PT30M';

	/**
	 * Getter/setting for $validFor
	 *
	 * @param mixed $time The amount of time a reset is valid
	 * @return mixed
	 */
	public function validFor($time = null) {
		if ($time == null) {
			return $this->validFor;
		}
		$this->validFor = $time;
	}

	/**
	 * deleteExpired
	 *
	 * @return void
	 */
	public function deleteExpired() {
		$now = $this->getDatetime();
		$expires = $now->sub(new DateInterval($this->validFor()))->format('Y-m-d H:i:s');
		return $this->deleteAll(array($this->alias . '.created <=' => $expires));
	}

}
