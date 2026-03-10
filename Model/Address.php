<?php
/**
 * Address
 */

App::uses('AppModel', 'Model');

/**
 * AddressBook Model
 *
 * @property	Customers	$Customers
 */
class Address extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'address_book';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'address_book_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'full';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'customers_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Customer ID must be numeric.',
				'required' => true,
			),
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A customer ID is required.',
			),
		),
		'entry_company' => array(
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'Company name may not be longer than 32 characters.',
				'allowEmpty' => true,
			),
		),
		'entry_firstname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A first name is required.',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'First name may not be more than 32 characters.',
			),
		),
		'entry_lastname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A last name is required.',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'Last name may not be more than 32 characters.',
			),
		),
		'entry_street_address' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A street address is required.',
				'required' => true,
			),
			'maxLength' => array(
				'rule' => array('maxlength', 64),
				'message' => 'Street addresses may not be longer than 64 characters.',
			),
		),
		'entry_suburb' => array(
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'The second address line may not be longer than 32 characters.',
				'allowEmpty' => true,
			),
		),
		'entry_postcode' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A zip code is required.',
				'required' => true,
			),
			'maxLength' => array(
				'rule' => array('maxlength', 10),
				'message' => 'Zip codes may not be longer than 10 characters.',
			),
		),
		'entry_city' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A city is required.',
				'required' => true,
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'A city\'s name may not be longer than 32 characters.',
			),
		),
		'entry_zone_id' => array(
			'notBlank' => array(
				'rule' => array('validateZoneId'),
				'message' => 'A state/zone is required.',
				'required' => true,
			),
		),
		'entry_country_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'A country id must be numeric.',
				'required' => true,
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
			'foreignKey' => 'customers_id',
		),
		'Zone' => array(
			'className' => 'Zone',
			'foreignKey' => 'entry_zone_id',
		),
		'Country' => array(
			'className' => 'Country',
			'foreignKey' => 'entry_country_id',
		),
	);

	/**
	 * Fields in this model that should be trimmed before validation.
	 *
	 * @var array
	 */
	protected $_trimFields = array(
		'entry_street_address',
		'entry_suburb',
		'entry_postcode',
		'entry_city',
		'entry_basename',
	);

	/**
	 * A list of country ids that require states.
	 */
	protected $countriesRequiringStates = [
		14,
		38,
		81,
		195,
		204,
		223,
		250,
	];

	/**
	 * Sets up virtual fields.
	 *
	 * @param mixed $id The id to start the model on
	 * @param mixed $table The table to use for this model
	 * @param mixed $ds The The connection name this model is connected to
	 * @return void
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['full'] = sprintf(
			'CONCAT(
				%1$s.entry_street_address, if(%1$s.entry_suburb != "", ", ", ""),
				%1$s.entry_suburb, if(%1$s.entry_city != "", ", ", ""),
				%1$s.entry_city, if(%1$s.entry_zone_id != "", ", ", ""),
				if(
					%1$s.entry_zone_id != "",
					(SELECT zone_code from zones as Zone WHERE zone_id = %1$s.entry_zone_id),
					CONCAT(", ", (SELECT countries_name from countries as Country WHERE countries_id = %1$s.entry_country_id))
				)
			)',
			$this->alias
		);
	}

	/**
	 * beforeValidate
	 *
	 * @param array $options The options
	 * @return bool
	 */
	public function beforeValidate($options = array()) {
		parent::beforeValidate();
		$this->stripInvalidAddressChars();
		return $this->trim($this->_trimFields);
	}

	/**
	 * stripInvalidAddressChars
	 *
	 * @return void
	 */
	protected function stripInvalidAddressChars() {
		$allowCharsExpression = '/[^\da-z\s\.#\/\-]+/i';
		if (!empty($this->data[$this->alias]['entry_street_address'])) {
			$this->data[$this->alias]['entry_street_address'] =
				preg_replace($allowCharsExpression, '', $this->data[$this->alias]['entry_street_address']);
		}
		if (!empty($this->data[$this->alias]['entry_suburb'])) {
			$this->data[$this->alias]['entry_suburb'] =
				preg_replace($allowCharsExpression, '', $this->data[$this->alias]['entry_suburb']);
		}
	}

	/**
	 * Retrieves address data needed to process payments.
	 *
	 * @param int $id The address record id.
	 * @param mixed $customersId The customer record id.
	 * @return array The address data.
	 *
	 * @note Any changes to the data structure should be reflected in
	 *       Order::addressForPayment
	 */
	public function findForPayment($id, $customersId = null) {
		$conditions = array('Address.address_book_id' => $id);
		if ($customersId) {
			$conditions['Address.customers_id'] = $customersId;
		}

		return $this->find('first', array(
			'contain' => array(
				'Zone' => array(
					'fields' => array('zone_code')
				),
				'Country' => array(
					'fields' => array('countries_name', 'countries_iso_code_2', 'address_format_id'),
				),
			),
			'conditions' => $conditions,
			'fields' => array(
				'entry_firstname',
				'entry_lastname',
				'entry_company',
				'entry_street_address',
				'entry_suburb',
				'entry_city',
				'entry_postcode',
			)
		));
	}

	/**
	 * Will query and attach a Zone array to an existing address array that
	 * contains an entry_zone_id.
	 *
	 * @param array $address Optional address array
	 * @return array
	 * @throws CakeException
	 */
	public function attachZone($address = []) {
		if (empty($address)) {
			$address = $this->data;
		}

		if (empty($address['Address']['entry_zone_id'])) {
			throw new CakeException('Asked to attachZone, but did not provide Address.entry_zone_id.');
		}

		$zone = $this->Zone->read('zone_code', $address['Address']['entry_zone_id']);

		if (empty($zone)) {
			throw new CakeException('Asked to attachZone, but zone id provided does not exist.');
		}

		if (!empty($address['Zone']) && $zone['Zone']['zone_code'] !== $address['Zone']['zone_code']) {
			throw new CakeException('Asked to attachZone, but zone exists and entry_zone_id does not match.');
		}

		$address['Zone'] = $zone['Zone'];
		return $address;
	}

	/**
	 * Updates the default addresses for a customer. This is useful after
	 * initial creation of a customer.
	 *
	 * @param int $customerId The customer id
	 * @return void
	 */
	public function setDefaultsForCustomer($customerId) {
		$this->Customer->id = $customerId;
		$this->Customer->saveField('customers_default_address_id', $this->id, true);
		$this->Customer->saveField('customers_shipping_address_id', $this->id, true);
	}

	/**
	 * Will query and attach a Country array to an existing address array that
	 * contains an entry_country_id.
	 *
	 * @param array $address Optional address array
	 * @return array
	 * @throws CakeException
	 */
	public function attachCountry($address = []) {
		if (empty($address)) {
			$address = $this->data;
		}

		if (empty($address['Address']['entry_country_id'])) {
			throw new CakeException('Asked to attachCountry, but did not provide Address.entry_country_id.');
		}

		$country = $this->Country->findByCountriesId($address['Address']['entry_country_id']);

		if (empty($country)) {
			throw new CakeException('Asked to attachCountry, but country id provided does not exist.');
		}

		if (!empty($address['Country']) && $address['Country']['countries_name'] !== $country['Country']['countries_name']) {
			throw new CakeException('Asked to attachCountry, but country exists and countries_name does not match.');
		}

		$address['Country'] = $country['Country'];
		return $address;
	}

	/**
	 * Saves an address and sets it as the customer default address.
	 *
	 * @param array $data Request data containing Customer and Address arrays
	 * @return mixed The new address id on success, bool false on failure
	 */
	public function saveAndMakeDefault($data) {
		if (!empty($data['Customer']['entry_basename'])) {
			$data['Address']['entry_basename'] = $data['Customer']['entry_basename'];
		}
		unset($data['Customer']);
		if ($this->save($data)) {
			$addressId = $this->getInsertId();
			$this->Customer->id = $data['Address']['customers_id'];
			if ($this->Customer->saveField('customers_default_address_id', $addressId, true)) {
				return $addressId;
			}
		}

		return false;
	}

	/**
	 * Check that a zone_id is required for any country that has entries in the
	 * `zones` table.
	 *
	 * @param array $check The validation data to test.
	 * @return bool
	 */
	public function validateZoneId($check) {
		if (!empty($this->data[$this->alias]['entry_country_id'])
			&& !in_array(
				$this->data[$this->alias]['entry_country_id'],
				$this->countriesRequiringStates
			)
		) {
			return true;
		}

		return !empty($this->data[$this->alias]['entry_zone_id']);
	}
}
