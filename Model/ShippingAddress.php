<?php
/**
 * Address
 */

App::uses('Address', 'Model');

/**
 * @property Customers $Customers
 */
class ShippingAddress extends Address {

	/**
	 * findMethods
	 *
	 * @var array
	 */
	public $findMethods = array('all' => true);

	/**
	 * Validation rule changes from the default Address::validate array.
	 *
	 * @var	array
	 */
	public $validateModifications = array(
		'entry_city' => array(
			'inList' => array(
				'rule' => array('inList', array('APO', 'FPO', 'DPO')),
				'message' => 'For shipping addresses, city must be either, APO, FPO, or DPO',
			),
		),
		'entry_zone_id' => array(
			'inList' => array(
				'rule' => array('inList', array(7, 9, 11, 182, 183, 184)),
				'message' => 'For shipping addresses, state must be AA, AE, or AP',
			),
		),
	);

	/**
	 * defaultFindConditions
	 *
	 * @var array
	 */
	public $defaultFindConditions = array(
		'ShippingAddress.entry_firstname !=' => null,
		'ShippingAddress.entry_lastname !=' => null,
		'ShippingAddress.entry_street_address !=' => null,
		'ShippingAddress.entry_postcode !=' => null,
		'ShippingAddress.entry_city IN' => array('APO', 'FPO', 'DPO'),
		'ShippingAddress.entry_zone_id IN' => array(7, 9, 11, 182, 183, 184),
	);

	/**
	 * __construct
	 *
	 * @param mixed $id The id to start the model on
	 * @param mixed $table The table to use for this model
	 * @param mixed $ds The connection name this model is connected to
	 * @return void
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->applyApoValidation();
	}

	/**
	 * Apply additional validation from APO type addresses.
	 *
	 * @return void
	 */
	protected function applyApoValidation() {
		$this->validate = Hash::merge($this->validate, $this->validateModifications);
	}

	/**
	 * _findFirst
	 *
	 * @param string $state The state of the model (before or after)
	 * @param array $query The query
	 * @param array $results The results
	 * @return array
	 */
	protected function _findFirst($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = Hash::merge($this->defaultFindConditions, (array)$query['conditions']);
		}

		return parent::_findFirst($state, $query, $results);
	}

	/**
	 * _findAll
	 *
	 * @param string $state The state of the model (before or after)
	 * @param array $query The query
	 * @param array $results The results
	 * @return array
	 */
	protected function _findAll($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = Hash::merge($this->defaultFindConditions, (array)$query['conditions']);
		}

		return parent::_findAll($state, $query, $results);
	}

	/**
	 * _findList
	 *
	 * @param string $state The state of the model (before or after)
	 * @param array $query The query
	 * @param array $results The results
	 * @return array
	 */
	protected function _findList($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = Hash::merge($this->defaultFindConditions, (array)$query['conditions']);
		}

		return parent::_findList($state, $query, $results);
	}

	/**
	 * _findCount
	 *
	 * @param string $state The state of the model (before or after)
	 * @param array $query The query
	 * @param array $results The results
	 * @return array
	 */
	protected function _findCount($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = Hash::merge($this->defaultFindConditions, (array)$query['conditions']);
		}

		return parent::_findCount($state, $query, $results);
	}
}
