<?php
/**
 * Admin
 */

App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Admin Model
 *
 */
class Admin extends AppModel {

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'email';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'message' => 'IDs must be a natural number.',
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'This doesn\'t appear to be a valid email address.',
			),
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Email address is required',
			),
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'This email address is already in use.',
			),
		),
		'password' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Password is required',
			),
			'minLength' => array(
				'rule' => array('minLength', 8),
				'message' => 'Password must be at least 8 characters long.',
			),
		),
		// Fake field used only for validating password change
		'confirm_new_password' => array(
			'rule' => array('validateConfirmNewPassword'),
			'message' => 'New passwords do not match.',
			'allowEmpty' => false,
		),
		'role' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Role is required.',
			),
			'lowercaseWithSeperators' => array(
				'rule' => array('inList', array('manager', 'employee', 'api')),
				'message' => 'A role must be either "manager" or "employee".',
			),
		),
		'created' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				'message' => 'Must be a valid datetime.',
			),
		),
		'modified' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				'message' => 'Must be a valid datetime.',
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
	public $hasMany = array();

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * Adds a notBlank validation rule only if the role is API
	 * Removes password validation if `id` is set (editing record)
	 *
	 * @param array $options Optional options
	 * @return bool True
	 */
	public function beforeValidate($options = array()) {
		if ($this->data[$this->alias]['role'] == 'api') {
			$this->validator()->add('token', 'required', array(
				'rule' => 'notBlank',
				'message' => 'Token is required with an API user.',
			));
		}
		if (isset($this->data[$this->alias]['id']) && empty($this->data[$this->alias]['password'])) {
			$this->validator()->remove('password');
			$this->validator()->remove('confirm_new_password');
			unset(
				$this->data[$this->alias]['password'],
				$this->data[$this->alias]['confirm_new_password']
			);
		}
		return true;
	}

	/**
	 * Hashes `password` if not empty
	 *
	 * @param array $options Optional options
	 * @return bool True
	 */
	public function beforeSave($options = array()) {
		if (!empty($this->data[$this->alias]['password'])) {
			$passwordHasher = new BlowfishPasswordHasher();
			$this->data[$this->alias]['password'] = $passwordHasher->hash(
				$this->data[$this->alias]['password']
			);
		}
		return true;
	}

	/**
	 * Validation method that is only used during admin password change.
	 *
	 * @param array $customer The admin record being validated.
	 * @return bool Returns true if [confirm_new_password] field matches the [password] field.
	 */
	public function validateConfirmNewPassword($customer) {
		if ($customer['confirm_new_password'] == $this->data[$this->alias]['password']) {
			return true;
		}
		return false;
	}

	/**
	 * Takes a query string and determines which model to search
	 *
	 * @param array $query The search query
	 * @return string customer | tracking | order
	 */
	public function determineModelToSearch($query) {
		$wordCount = count(explode(' ', $query));
		if ($wordCount > 1) {
			return 'customer';
		}

		$tracking = stripos($query, Configure::read('Tracking.prefix'));
		if ($tracking !== false) {
			return 'tracking';
		}

		$isTrackingNumber = false;
		$letters = 0;
		$numbers = 0;
		$special = 0;
		$chars = str_split($query);
		foreach ($chars as $char) {
			if (ctype_alpha($char)) {
				$letters++;
			} elseif (is_numeric($char)) {
				$numbers++;
			} else {
				$special++;
			}
		}

		if ($special < 1 && $numbers > 4 && $letters < 9) {
			$isTrackingNumber = true;
		}

		if ($isTrackingNumber) {
			return 'order';
		}

		return 'customer';
	}

}
