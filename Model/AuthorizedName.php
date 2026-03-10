<?php
/**
 * AuthorizedName
 */

App::uses('AppModel', 'Model');

/**
 * AuthorizedName Model
 *
 * @property	Customers	$Customers
 */
class AuthorizedName extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'authorized_names_id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'authorized_names_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Invalid id.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'customers_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Invalid customers_id.',
				'required' => true
			),
		),
		'authorized_firstname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'First name is required.',
				'required' => true
			),
			'maxLength' => array(
				'rule' => array('maxLength', 20),
				'message' => 'First name may not be more than 20 characters.',
			),
		),
		'authorized_lastname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Last name is required.',
				'required' => true
			),
			'maxLength' => array(
				'rule' => array('maxLength', 20),
				'message' => 'Last name may not be more than 20 characters.',
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
			'foreignKey' => 'customers_id',
		),
	);

	/**
	 * Update the search index after saving a record.
	 *
	 * @param bool $created True if this save created a new record
	 * @param array $options Options passed from Model::save().
	 * @return void
	 */
	public function afterSave($created, $options = []) {
		parent::afterSave($created, $options);
		$this->updateSearchIndex($this->data[$this->alias]['customers_id']);
	}

	/**
	 * Update the search index after deleting a record.
	 *
	 * @return void
	 */
	public function afterDelete() {
		parent::afterDelete();
		$this->updateSearchIndex($this->data[$this->alias]['customers_id']);
	}

	/**
	 * Updates SearchIndex `data` using `Customer::indexData()` to figure out
	 * what to save.
	 *
	 * @param int $customerId A customer ID
	 * @return bool Result of saveField()
	 */
	public function updateSearchIndex($customerId) {
		$data = $this->Customer->indexData($customerId);
		$SearchIndex = ClassRegistry::init('SearchIndex');
		$record = $SearchIndex->findByAssociationKey($customerId);
		$SearchIndex->id = $record['SearchIndex']['id'];
		return $SearchIndex->saveField('data', $data);
	}
}
