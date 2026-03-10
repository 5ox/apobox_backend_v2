<?php
/**
 * Customer
 */

App::uses('AppModel', 'Model');
App::uses('ApoboxPasswordHasher', 'Controller/Component/Auth');
App::uses('Payment', 'Lib');

/**
 * Customer Model
 *
 * @property	Address	$Address
 */
class Customer extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'customers_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'customers_email_address';

	/**
	 * Report fields
	 */
	public $reportFields = [
		'ShippingAddress.entry_postcode' => 'Shipping Postal Code',
		'ShippingAddress.entry_country_id' => 'Shipping Country',
		'ShippingAddress.entry_city' => 'Shipping City',
		'DefaultAddress.entry_postcode' => 'Billing Postal Code',
		'DefaultAddress.entry_country_id' => 'Billing Country',
		'DefaultAddress.entry_city' => 'Billing City',
		'Customer.insurance_amount' => 'Insurance Amount',

		// Virtual report fields
		// 'Customer.email_provider' => 'Email Provider',
		// 'Customer.credit_card_stored' => 'Credit Card Stored',
	];

	/**
	 * Magic find methods
	 *
	 */
	public $findMethods = [
		'active' => true,
		'AllIncompleteBillings' => true,
		'FirstIncompleteBilling' => true,
	];

	/**
	 * Behaviors
	 *
	 */
	public $actsAs = [
		'CreditCard',
		'Searchable' => [
			'fields' => [
				'billing_id',
				'customers_firstname',
				'customers_lastname',
				'customers_email_address',
			],
			'associations' => [
				'AuthorizedName' => [
					'authorized_firstname',
					'authorized_lastname',
				],
			],
		],
	];

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'billing_id' => array(
			'alphaNumeric' => array(
				'rule' => '/[A-Z]{2}\d{4}/',
				'message' => 'A billing ID must be two uppercase letters followed by four digits.',
			),
		),
		'customers_firstname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'required' => true,
				'message' => 'First name is required.',
			),
			'maxLength' => array(
				'rule' => array('maxLength', 32),
				'message' => 'First name may not be more than 32 characters.',
			),
		),
		'customers_lastname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'required' => true,
				'message' => 'Last name is required',
			),
			'maxLength' => array(
				'rule' => array('maxLength', 32),
				'message' => 'Last name may not be more than 32 characters.',
			),
		),
		'customers_dob' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				'message' => 'Customer\'s date fo birth must be a valid date.',
				'allowEmpty' => true,
			),
		),
		'customers_email_address' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'required' => true,
				'message' => 'Email address is required.',
			),
			'email' => array(
				'rule' => array('email'),
				'message' => 'This doesn\'t look like a valid email address.',
			),
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'This email address already exists in the system.',
			),
		),
		'backup_email_address' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'This doesn\'t look like a valid email address.',
				'allowEmpty' => true,
			),
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'This email address already exists in the system.',
			),
		),
		'customers_default_address_id' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A billing address is required',
			),
			'belongsToCustomer' => array(
				'rule' => array('addressIsCustomers'),
				'message' => 'This address does not belong to you.',
			),
		),
		'customers_shipping_address_id' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A shipping address is required.',
			),
			'isShippingAddress' => array(
				'rule' => array('isShippingAddress'),
				'message' => 'A shipping address must be an APO/FPO/DPO address.',
				'on' => 'create',
			),
			'isNotSetAsEmergencyAddress' => array(
				'rule' => array('notSetAsEmergencyAddress'),
				'message' => 'A shipping address cannot be the same as your backup shipping address.',
				'on' => 'update',
			),
			'belongsToCustomer' => array(
				'rule' => array('addressIsCustomers'),
				'message' => 'This address does not belong to you.',
			),
		),
		'customers_emergency_address_id' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A shipping address is required.',
			),
			'isNotSetAsShippingAddress' => array(
				'rule' => array('notSetAsShippingAddress'),
				'message' => 'A backup shipping address cannot be the same as your shipping address.',
				'on' => 'update',
			),
			'belongsToCustomer' => array(
				'rule' => array('addressIsCustomers'),
				'message' => 'This address does not belong to you.',
			),
		),
		'customers_newsletter' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Newsletter is required',
			),
		),
		'cc_firstname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Cardholder\'s first name is required.',
			),
			'specialChars' => array(
				'rule' => '/^[A-z0-9\s]+$/',
				'message' => 'First name should not contain special characters.',
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
				'message' => 'Credit card first name may not be more than 64 characters.',
			),
		),
		'cc_lastname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Cardholder\'s last name is required.',
			),
			'specialChars' => array(
				'rule' => '/^[A-z0-9\s]+$/',
				'message' => 'Last name should not contain special characters.',
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
				'message' => 'Credit card last name may not be more than 64 characters.',
			),
		),
		'cc_number' => array(
			'cc' => array(
				'rule' => array('cc'),
				'message' => 'This doesn\'t look like a valid credit card number.',
			),
		),
		'cc_cvv' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Card verification code is required.',
			),
			'cc' => array(
				'rule' => '/[\d]{3,4}/',
				'message' => 'A CVV code is 3 to 4 digits',
			),
		),
		'cc_expires_month' => array(
			'minLength' => array(
				'rule' => array('ccExpiration'),
				'message' => 'This expiration date is not valid.',
			),
		),
		'insurance_amount' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Please enter a numeric (numbers only) dollar amount.',
			),
			'gte' => array(
				'rule' => array('comparison', 'greater or equal', 0),
				'message' => 'You can request insurance between $0 (no insurance) and $5000.00.',
			),
			'lte' => array(
				'rule' => array('comparison', 'less or equal', 5000),
				'message' => 'You can request insurance between $0 (no insurance) and $5000.00.',
			),
		),
		'invoicing_authorized' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Invoicing authorization must be true or false.',
			),
		),
		'customers_password' => array(
			'minLength' => array(
				'rule' => array('minLength', 6),
				'required' => true,
				'message' => 'You password must be at least 6 characters long.',
			),
		),
	);

	/**
	 * _shellVirtualFields
	 *
	 * @var array
	 */
	protected $_shellVirtualFields = array(
		'customers_fullname' => 'CONCAT(
			`customers_firstname`,
			" ",
			`customers_lastname`
		)',
		'cc_expires_date' => 'DATE_FORMAT(
			STR_TO_DATE(
				CONCAT(
					cc_expires_year,
					"-",
					cc_expires_month
				),
				"%y-%m"
			),
			"%Y-%m"
		)'
	);

	/**
	 * Fields in this model that should be trimmed before validation.
	 *
	 * @var array
	 */
	protected $_trimFields = array(
		'customers_firstname',
		'customers_lastname',
		'customers_email_address',
	);

	/**
	 * Called after each successful save operation. Ensure any logic in this
	 * method preserves $this->data.
	 *
	 * @param bool $created True if this save created a new record
	 * @param array $options Options passed from Model::save().
	 * @return void
	 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
	 * @see Model::save()
	 */
	public function afterSave($created, $options = array()) {
		parent::afterSave($created, $options);
		if ($created) {
			$this->newBillingId();
		}
	}

	/**
	 * isShippingAddress
	 *
	 * @param mixed $check The data to check
	 * @return bool
	 */
	public function isShippingAddress($check) {
		$this->ShippingAddress->id = current($check);
		if (!$this->ShippingAddress->exists()) {
			return false;
		}

		$shippingAddressData = $this->ShippingAddress->read();
		$this->ShippingAddress->set($shippingAddressData);

		return $this->ShippingAddress->validates();
	}

	/**
	 * notSetAsShippingAddress
	 *
	 * @param mixed $check The data to check
	 * @return bool
	 */
	public function notSetAsShippingAddress($check) {
		$id = current($check);
		if (empty($this->id)) {
			$this->id = $this->data[$this->alias]['customers_id'];
		}

		if (!empty($this->data[$this->alias]['customers_shipping_address_id'])) {
			return ($this->data[$this->alias]['customers_shipping_address_id'] != $id);
		}

		$customer = $this->findByCustomersId($this->id, array('customers_shipping_address_id'));

		return ($customer[$this->alias]['customers_shipping_address_id'] != $id);
	}

	/**
	 * notSetAsEmergencyAddress
	 *
	 * @param mixed $check The data to check
	 * @return bool
	 */
	public function notSetAsEmergencyAddress($check) {
		$id = current($check);
		if (empty($this->id)) {
			$this->id = $this->data[$this->alias]['customers_id'];
		}

		if (!empty($this->data[$this->alias]['customers_emergency_address_id'])) {
			return ($this->data[$this->alias]['customers_emergency_address_id'] != $id);
		}

		$customer = $this->findByCustomersId($this->id, array('customers_emergency_address_id'));

		return ($customer[$this->alias]['customers_emergency_address_id'] != $id);
	}

	/**
	 * ccExpiration
	 *
	 * @param mixed $check The data to check
	 * @return bool
	 */
	public function ccExpiration($check) {
		if (empty($this->data[$this->alias]['cc_expires_year'])) {
			return false;
		}
		$year = $this->data[$this->alias]['cc_expires_year'];
		if ($year < 200) {
			$year += 2000;
		}
		$now = $this->getDatetime();
		if (date_format($now, 'n') != 12 && $year > date_format($now, 'Y')) {
			return true;
		}

		$month = sprintf('%02d', current($check));
		$expirationDate = date_create($year . '-' . $month . '-01');

		$invalidBefore = Configure::read('CreditCard.invalid_before');
		if (!empty($invalidBefore)) {
			$expirationDate->sub(new DateInterval('P' . $invalidBefore));
		}

		return ($now < $expirationDate);
	}

	/**
	 * addressIsCustomers
	 *
	 * @param mixed $check The data check
	 * @return bool
	 */
	public function addressIsCustomers($check) {
		$this->Address->id = current($check);
		if (!$this->Address->exists()) {
			return false;
		}

		$id = $this->id;
		if (!empty($this->data['Customer']['customers_id'])) {
			$id = $this->data['Customer']['customers_id'];
		}

		$addressCustomersId = $this->Address->field('customers_id');

		return ($addressCustomersId == $id);
	}

	/**
	 * hasMany associations
	 *
	 * @var	array
	 */
	public $hasMany = array(
		'Address' => array(
			'className' => 'Address',
			'foreignKey' => 'customers_id',
			'dependent' => false,
		),
		'Order' => array(
			'foreignKey' => 'customers_id',
			'dependent' => false,
		),
		'PasswordRequest' => array(),
		'AuthorizedName' => array(
			'foreignKey' => 'customers_id',
			'dependent' => false
		),
		'CustomerReminder' => array(
			'foreignKey' => 'customers_id',
			'dependent' => true,
		),
	);

	/**
	 * belongsTo associations
	 *
	 * This sets up some covenience model relations since
	 * an 'Address' can be linked to a customer in mutiple
	 * ways, including the default hasMany. At present
	 * there is no need for corresponding models for these
	 * relations because the use the `Address` model.
	 *
	 * @var	array
	 */
	public $belongsTo = array(
		'DefaultAddress' => array(
			'className' => 'Address',
			'foreignKey' => 'customers_default_address_id',
			'dependent' => false,
		),
		'ShippingAddress' => array(
			'foreignKey' => 'customers_shipping_address_id',
			'dependent' => false,
		),
		'EmergencyAddress' => array(
			'className' => 'Address',
			'foreignKey' => 'customers_emergency_address_id',
			'dependent' => false,
		),
	);

	/**
	 * Perform logic before validation.
	 *
	 * Checks if a `customers_email_address` is going to be saved, and then
	 * checks if it exists in the database. If it does, the record's `is_active`
	 * status is checked and the unique validation rule is removed if the
	 * existing email address is marked as not an active account.
	 *
	 * Runs any fields present in $this->_trimFields array through PHP's trim()
	 * function.
	 *
	 * @param array $options The options
	 * @return bool true
	 */
	public function beforeValidate($options = array()) {
		if (!empty($this->data[$this->alias]['customers_email_address'])) {
			$exists = $this->findAllByCustomersEmailAddress(
				$this->data[$this->alias]['customers_email_address'],
				array('is_active')
			);
			if ($exists) {
				$active = Hash::extract($exists, '{n}.Customer.is_active');
				if (!in_array(1, $active)) {
					$this->validator()->remove('customers_email_address', 'unique');
				}
			}
		}

		return $this->trim($this->_trimFields);
	}

	/**
	 * beforeSave
	 *
	 * @param array $options Optional options passed from Model::save()
	 * @return bool True if the operation should continue, false if it should abort
	 */
	public function beforeSave($options = array()) {
		parent::beforeSave($options);
		if (!empty($this->data[$this->alias]['customers_password'])) {
			$passwordHasher = new ApoboxPasswordHasher();
			$this->data[$this->alias]['customers_password'] =
				$passwordHasher->hash($this->data[$this->alias]['customers_password']);
		}

		// `cc_number` should only be not empty when attempting to update payment info
		if (!empty($this->data[$this->alias]['cc_number'])) {
			$this->Behaviors->disable('Searchable');
			try {
				$cardId = $this->authorizeCreditCard();
				if (!$cardId) {
					$this->invalidate('cc_number', 'The card you provided is invalid.');
					return false;
				}
				// Save the PayPal card ID.
				$this->data[$this->alias]['card_token'] = $cardId;
			} catch (Exception $e) {
				$message = $e->getMessage() ? $e->getMessage() : 'Unspecified authorize card error';
				$this->invalidate('cc_number', $message);
				$this->log('CustomerModel::beforeSave: ' . $message, 'customers');
				return false;
			}

			$this->data[$this->alias]['cc_number_encrypted'] =
				$this->encryptCC(
					$this->data[$this->alias]['cc_number_raw'],
					Configure::read('Security.creditCardKey')
				);

			$fieldList = $options['fieldList'];
			if (!empty($fieldList) && !array_key_exists('cc_number_encrypted', array_flip($fieldList))) {
				return false;
			}
		}

		// set `invoicing_authorized` to 0 for all new records if it's not
		// explicitly set.
		if (!isset($this->data[$this->alias]['customers_id'])) {
			if (!isset($this->data[$this->alias]['invoicing_authorized'])) {
				$this->data[$this->alias]['invoicing_authorized'] = 0;
			}
		}

		return true;
	}

	/**
	 * Custom find method for active customers.
	 *
	 * @param string $state The state of the find.
	 * @param array $query Additional find parameters.
	 * @param array $results The results if in `after` state.
	 * @return array The query params `before` state, the results in `after` state.
	 */
	protected function _findActive($state, $query, $results = []) {
		if ($state === 'before') {
			$query['conditions'][$this->alias . '.is_active'] = true;
			return $query;
		}
		return $results;
	}

	/**
	 * Find customers with incomplete billing information
	 * Can be used with a condition on th customer_id field to determine if
	 * the customers
	 *
	 * @param string $state The state of the find.
	 * @param array $query Additional find parameters.
	 * @param array $results The results if in `after` state.
	 * @return array The modified $query or the unmodified $results
	 */
	protected function _findAllIncompleteBillings($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions']['OR']['Customer.cc_firstname'] = '';
			$query['conditions']['OR']['Customer.cc_lastname'] = '';
			$query['conditions']['OR']['Customer.cc_number_encrypted'] = '';
			$query['conditions']['OR']['Customer.cc_expires_month'] = '';
			$query['conditions']['OR']['Customer.cc_expires_year'] = '';
			$query['conditions']['OR']['Customer.customers_default_address_id'] = null;
			$query['conditions']['OR'][] = 'Customer.customers_default_address_id = ""';
			return $query;
		}
		return $results;
	}

	/**
	 * Find a single customer with incomplete billing information
	 * Can be used with a condition on th customer_id field to determine if
	 * the customers
	 *
	 * @param string $state The state of the find.
	 * @param array $query Additional find parameters.
	 * @param array $results The results if in `after` state.
	 * @return array The modified $query or the unmodified $results
	 */
	protected function _findFirstIncompleteBilling($state, $query, $results = array()) {
		if ($state == 'before') {
			$query = $this->_findAllIncompleteBillings($state, $query, $results);
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			return array();
		}

		return $results[0];
	}

	/**
	 * Return a single customer record if active.
	 *
	 * This replicates the original findBy behavior (returning single result)
	 * while using a custom magic finder method.
	 *
	 * @param string $billingId The billingId to query.
	 * @return array The results of the find or empty array.
	 */
	public function findForQuickOrder($billingId) {
		$results = $this->findActiveByBillingId($billingId);
		return (!empty($results[0]) ? $results[0] : []);
	}

	/**
	 * addressIsInUse
	 *
	 * @param int $addressId The address id
	 * @param mixed $id The model primary id
	 * @return bool
	 */
	public function addressIsInUse($addressId, $id = null) {
		if ($id) {
			$this->id = $id;
		}

		$id = $this->id;

		if (is_array($this->id)) {
			$id = $this->id[0];
		}

		if ($id !== null && $id !== false) {
			$addressIds = $this->find('first', array(
				'conditions' => array($this->alias . '.' . $this->primaryKey => $id),
				'fields' => array(
					$this->alias . '.' . 'customers_default_address_id',
					$this->alias . '.' . 'customers_shipping_address_id',
					$this->alias . '.' . 'customers_emergency_address_id',
				),
			));

			unset($addressIds[$this->alias]['customers_id']);
			$addressIds = array_flip($addressIds[$this->alias]);

			return (isset($addressIds[$addressId]));
		}

		return false;
	}

	/**
	 * Return true if a given address is being used by a given customer as a
	 * shipping or emergency backup address.
	 *
	 * @param int $addressId The address id to check.
	 * @param int $id The customer id to check.
	 * @return bool
	 */
	public function addressIsInUseShipping($addressId, $id = null) {
		if ($id) {
			$this->id = $id;
		}

		$id = $this->id;

		if (is_array($this->id)) {
			$id = $this->id[0];
		}

		if ($id !== null && $id !== false) {
			$addressIds = $this->find('first', array(
				'conditions' => array($this->alias . '.' . $this->primaryKey => $id),
				'fields' => array(
					$this->alias . '.' . 'customers_shipping_address_id',
					$this->alias . '.' . 'customers_emergency_address_id',
				),
			));

			unset($addressIds[$this->alias]['customers_id']);
			$addressIds = array_flip($addressIds[$this->alias]);

			return (isset($addressIds[$addressId]));
		}

		return false;
	}

	/**
	 * encryptCC
	 *
	 * @param string $cardNumber The cleartext card number
	 * @param string $key The key
	 * @return mixed False or the encrypted card number
	 */
	public function encryptCC($cardNumber, $key) {
		if ($cardNumber == '') {
			return false;
		}

		srand((double)microtime() * 1000000); //for sake of MCRYPT_RAND
		$key = md5($key); //to improve variance

		/* Open module, and create IV */
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', 'cfb', '');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$ivSize = mcrypt_enc_get_iv_size($td);
		$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
		/* Initialize encryption handle */
		// @codeCoverageIgnoreStart
		if (mcrypt_generic_init($td, $key, $iv) < 0) {
			return false;
		}
		// @codeCoverageIgnoreEnd
		/* Encrypt data */
		$cT = mcrypt_generic($td, $cardNumber);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$encryptedCardNumber = $iv . $cT;

		return base64_encode($encryptedCardNumber);
	}

	/**
	 * decryptCC
	 *
	 * @param string $encryptedCardNumber The encrypted card number
	 * @param string $key The key
	 * @return mixed False or the decrypted card number
	 */
	public function decryptCC($encryptedCardNumber, $key) {
		if ($encryptedCardNumber == '') {
			return false;
		}
		$encryptedCardNumber = base64_decode($encryptedCardNumber);
		$key = md5($key); //to improve variance

		/* Open module, and create IV */
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', 'cfb', '');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$ivSize = mcrypt_enc_get_iv_size($td);
		$iv = substr($encryptedCardNumber, 0, $ivSize);

		if (strlen($iv) != $ivSize) {
			return false;
		}

		$encryptedCardNumber = substr($encryptedCardNumber, $ivSize);
		/* Initialize encryption handle */
		// @codeCoverageIgnoreStart
		if (mcrypt_generic_init($td, $key, $iv) < 0) {
			return false;
		}
		// @codeCoverageIgnoreEnd
		/* Dencrypt data */
		$cardNumber = mdecrypt_generic($td, $encryptedCardNumber);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $cardNumber;
	}

	/**
	 * Authorize a credit card and retrieve a card token if authorization
	 * passes.
	 *
	 * @param int $id The customer id.
	 * @return mixed A card_token if card is authorized, false otherwise.
	 */
	public function authorizeCreditCard($id = null) {
		$paypal = $this->getPaymentLib();
		if (!$id) {
			$id = $this->id;
		}

		$card = $this->data[$this->alias];
		$card['cc_number'] = $this->data[$this->alias]['cc_number_raw'];
		$card['cc_cvv'] = $this->data[$this->alias]['cc_cvv_raw'];

		$addressId = $this->field('customers_default_address_id');
		$address = $this->Address->findForPayment($addressId);

		return $paypal->authorizeAndStoreCard($card, $address);
	}

	/**
	 * Manually decrypts the old style encrypted credit card and sets the
	 * cvv code to `not_used` to allow processing a charge.
	 *
	 * @param array $customer The customer record.
	 * @return array The modified customer record.
	 */
	public function initForCharge($customer) {
		if (!empty($customer['cc_number_encrypted'])) {
			$customer['cc_number'] = $this->decryptCC(
				$customer['cc_number_encrypted'],
				Configure::read('Security.creditCardKey')
			);
			$customer['cc_cvv'] = 'not_used';
		}

		return $customer;
	}

	/**
	 * Get a formatted list of customers, optionally filtered by a query
	 * string. The list is formatted as:
	 *
	 *   <billing_id> <firstname> <lastname>
	 *   BD1234 Bob Daily
	 *
	 * @param string $query A string to search against
	 * @return array The formatted list of customers.
	 */
	public function listAll($query = null) {
		$conditions = array();
		if (!empty($query)) {
			$query = '%' . $query . '%';
			$conditions['OR'] = array(
				'billing_id LIKE' => $query,
				'customers_firstname LIKE' => $query,
				'customers_lastname LIKE' => $query,
			);
		}

		$this->virtualFields['name'] = 'CONCAT(
			`billing_id`,
			" ",
			`customers_firstname`,
			" ",
			`customers_lastname`
		)';
		return $this->find('list', array(
			'contain' => array(),
			'conditions' => $conditions,
			'fields' => array(
				'customers_id',
				'name',
			)
		));
	}

	/**
	 * Retrieves a customer's billing id. If a billing id does not exist, this
	 * method will call newBillingId to generate a new one, save it, and then
	 * return it.
	 *
	 * @throws BadRequestException
	 * @return string
	 */
	public function billingId() {
		if (!$this->id) {
			throw new BadRequestException('A customer record must be present to retrieve a billing id.');
		}

		return $this->field('billing_id') ?: $this->newBillingId();
	}

	/**
	 * Finds an available billing ID and stores it to the user's record. Since
	 * this method is called in afterSave, it must preserve $this->data.
	 *
	 * @throws BadRequestException
	 * @return string
	 */
	public function newBillingId() {
		if (!$this->id) {
			throw new BadRequestException('A customer record must be present to generate a billing id.');
		}

		$originalData = $this->data;

		$data = !empty($this->data['Customer']) ? $this->data['Customer'] : [];
		if (empty($data['customers_firstname'])) {
			$data['customers_firstname'] = $this->field('customers_firstname');
		}
		if (empty($data['customers_lastname'])) {
			$data['customers_lastname'] = $this->field('customers_lastname');
		}

		while (empty($newIdSaved)) {
			$firstLetter = strtoupper(substr($data['customers_firstname'], 0, 1));
			$lastLetter = strtoupper(substr($data['customers_lastname'], 0, 1));
			$randomNumber = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
			$billingId = $firstLetter . $lastLetter . $randomNumber;

			if (empty($this->findByBillingId($billingId))) {
				$newIdSaved = $this->saveField('billing_id', $billingId, ['callbacks' => false]);

				// append the new billing ID to SearchIndex data
				$SearchIndex = ClassRegistry::init('SearchIndex');
				$searchData = $SearchIndex->findByAssociationKey($this->id, ['id', 'data']);
				$SearchIndex->id = $searchData['SearchIndex']['id'];
				$SearchIndex->saveField('data', $billingId . '. ' . $searchData['SearchIndex']['data']);
			}
		}

		$this->data = $originalData;
		return $billingId;
	}

	/**
	 * Finds customers that have a credit card expiring next month.
	 *
	 * @return array An array of [customers_id] => customer email address & fullname or an empty array()
	 */
	public function findExpiringCreditCards() {
		$this->virtualFields = $this->_shellVirtualFields;
		$options = array(
			'conditions' => array(
				// don't fetch customers that don't have credit card dates
				'NOT' => array(
					'OR' => array(
						array('cc_expires_month' => null),
						array('cc_expires_month LIKE' => ''),
						array('cc_expires_year' => null),
						array('cc_expires_year LIKE' => ''),
					),
				),
				// fetch customers with `cc_expires_year` this year or next only and next month
				'OR' => array(
					array('cc_expires_year' => date('y')),
					array('cc_expires_year' => date('y', strtotime('next year'))),
				),
				'cc_expires_month' => date('m', strtotime('next month')),
			),
			'fields' => array(
				'customers_id',
				'customers_email_address',
				'customers_fullname',
				'cc_expires_date'
			),
		);
		$result = array();
		$customers = $this->find('all', $options);
		if ($customers) {
			foreach ($customers as $customer) {
				if ($customer['Customer']['cc_expires_date'] == date('Y-m', strtotime('next month'))) {
					$result[$customer['Customer']['customers_id']] = array(
						'customers_email_address' => $customer['Customer']['customers_email_address'],
						'customers_fullname' => $customer['Customer']['customers_fullname'],
					);
				}
			}
		}
		return $result;
	}

	/**
	 * Finds all customers with missing addresses that either have no CustomerReminder
	 * record or `CustomerReminder.reminder_count` less than the specified limit.
	 *
	 * @return array The customer data to alert or empty
	 */
	public function findAllPartialSignups() {
		$signupReminders = Configure::check('Customers.signupReminders') ? Configure::read('Customers.signupReminders') : 2;
		$this->virtualFields['customers_fullname'] = 'CONCAT(
			`customers_firstname`,
			" ",
			`customers_lastname`
		)';
		$options = array(
			'conditions' => array(
				'OR' => array(
					'customers_default_address_id' => null,
					'customers_shipping_address_id' => 0,
				),
			),
			'fields' => array(
				'customers_id',
				'billing_id',
				'customers_fullname',
				'customers_email_address',
			),
			'contain' => array(
				'CustomerReminder' => array(
					'conditions' => array(
						'reminder_type' => 'partial_signup',
					),
				),
			),
		);
		$customers = $this->find('all', $options);

		$alerts = array();
		foreach ($customers as $customer) {
			$count = Hash::get($customer, 'CustomerReminder.0.reminder_count', 0);
			if ($count < $signupReminders) {
				$alerts[] = $customer;
			}
		}
		return $alerts;
	}

	/**
	 * Finds all customers with partial signups (missing address data) that
	 * have existed incomplete for $purgePartials weeks.
	 *
	 * @return array The customer data to purge or empty
	 */
	public function findAllExpiredPartialSignups() {
		$purgePartials = Configure::check('Customers.purgePartials') ? Configure::read('Customers.purgePartials') : 4;
		$date = $this->getDateTime()->modify("$purgePartials weeks ago");
		$options = array(
			'conditions' => array(
				'OR' => array(
					'customers_default_address_id' => null,
					'customers_shipping_address_id' => 0,
				),
				'created <' => $date->format('Y-m-d H:i:s'),
			),
			'fields' => array(
				'customers_id',
				'billing_id',
				'customers_email_address',
			),
		);
		$customers = $this->find('all', $options);
		return $customers;
	}

	/**
	 * Requires a $customers array with at least `customers_id` set, and deletes
	 * every customer with the corresponding id.
	 *
	 * @param array $customers The customers to delete
	 * @return bool True on success (even if no records were deleted)
	 */
	public function purgeExpiredPartials($customers = array()) {
		$ids = Hash::extract($customers, '{n}.Customer.customers_id');
		return $this->deleteAll(array('Customer.customers_id' => $ids));
	}

	/**
	 * Finds all customers with expired credit cards that have not been sent
	 * a reminder alert (only one is sent).
	 *
	 * @param mixed $limit Optional limit to stop operation at
	 * @return array The customer data to alert or empty
	 */
	public function findExpiredCreditCards($limit = null) {
		$this->virtualFields = $this->_shellVirtualFields;
		$maxExpiredDate = date_create('-' . Configure::read('Customers.expiredCardReminders.maxMonths') . ' months');
		$options = array(
			'conditions' => array(
				// don't fetch customers that don't have credit card dates
				'NOT' => array(
					'OR' => array(
						array('cc_expires_month' => null),
						array('cc_expires_month LIKE' => ''),
						array('cc_expires_year' => null),
						array('cc_expires_year LIKE' => ''),
					),
				),
				// fetch customers with `cc_expires_date` in the past
				array('cc_expires_date <' => date('Y-m')),
				array('cc_expires_date >=' => $maxExpiredDate->format('Y-m')),
			),
			'fields' => array(
				'customers_id',
				'billing_id',
				'customers_email_address',
				'customers_fullname',
				'cc_expires_date'
			),
			'contain' => array(
				'CustomerReminder' => array(
					'conditions' => array(
						'reminder_type' => 'expired_card',
					),
				),
			),
		);
		$customers = $this->find('all', $options);

		$alerts = array();
		$customers = Hash::sort($customers, '{n}.CustomerReminder.{n}.reminder_count');
		foreach ($customers as $customer) {
			$count = Hash::get($customer, 'CustomerReminder.0.reminder_count', 0);
			if ($count < Configure::read('Customers.expiredCardReminders.numberToSend')) {
				$alerts[] = $customer;
				if (count($alerts) == $limit) {
					break;
				}
			}
		}
		return $alerts;
	}

	/**
	 * Sets payment related fields to empty strings and sets `is_active` to
	 * false (0).
	 *
	 * @param int $id The customer id
	 * @return mixed The updated fields or false on failure
	 */
	public function closeAccount($id = null) {
		if (!$this->exists($id)) {
			return false;
		}

		$fields = array(
			'is_active',
			'cc_firstname',
			'cc_lastname',
			'cc_number',
			'cc_number_encrypted',
			'cc_expires_month',
			'cc_expires_year',
			'cc_cvv',
			'card_token',
		);
		$data = array();
		$data[$this->alias] = array_combine($fields, array_fill(0, count($fields), ''));
		$data[$this->alias]['is_active'] = false;

		$this->id = $id;
		return $this->save($data, array(
			'validate' => false,
			'callbacks' => false,
			'fieldList' => $fields,
		));
	}

	/**
	 * Checks of the provided (by id) customer is a "partial signup", which is
	 * a customer with incomplete or missing default address data.
	 *
	 * @param int $id The customer id
	 * @return bool True if the customer is a partial signup, false if not
	 */
	public function isPartialSignup($id) {
		$options = [
			'conditions' => [
				'customers_id' => $id,
				'OR' => [
					['customers_default_address_id' => null],
					['customers_default_address_id' => 0],
				],
			],
			'fields' => [
				'customers_id',
			],
		];
		return (bool)$this->find('first', $options);
	}

	/**
	 * Custom fulltext index method, called by the Searchable behavior.
	 *
	 * @param int $id An optional customer id to override $this->id
	 * @return string The SearchIndex.data separated by a `.`
	 */
	public function indexData($id = null) {
		$id = !is_null($id) ? $id : $this->id;
		$options = [
			'conditions' => [
				'Customer.customers_id' => $id,
			],
			'fields' => $this->actsAs['Searchable']['fields'],
			'contain' => $this->actsAs['Searchable']['associations'],
		];
		$customer = $this->find('first', $options);

		$data = [];
		foreach ($this->actsAs['Searchable']['fields'] as $searchField) {
			if (isset($this->data[$this->alias][$searchField])) {
				$data[$searchField] = $this->data[$this->alias][$searchField];
			} else {
				if ($customer) {
					$data[$searchField] = $customer[$this->alias][$searchField];
				}
			}
		}

		$customer = Hash::remove($customer, '{s}.{n}.customers_id');
		$associated = [];
		foreach ($this->actsAs['Searchable']['associations'] as $model => $fields) {
			if (isset($customer[$model])) {
				$associated[] = Hash::flatten($customer[$model]);
			}
		}

		return join('. ', $data + Hash::flatten($associated));
	}

	/**
	 * Finds customers that don't have a `customers_info` record and returns
	 * an array consisting of the customer id and the date from `Customer.created`.
	 *
	 * @return array $customers an array of [customers_id] => [created]
	 */
	public function findMissingCustomersInfo() {
		$this->bindModel([
			'hasMany' => [
				'CustomersInfo' => [
					'foreignKey' => 'customers_info_id',
				],
			],
		]);
		$options = [
			'contain' => [
				'CustomersInfo' => [
					'fields' => [
						'customers_info_id',
					],
				],
			],
			'fields' => [
				'customers_id',
				'created',
			],
			'conditions' => [
				'Customer.created NOT' => '0000-00-00 00:00:00',
			],
		];
		$customers = $this->find('all', $options);
		$update = [];
		foreach ($customers as $customer) {
			if (empty($customer['CustomersInfo'])) {
				$update[$customer['Customer']['customers_id']] = $customer['Customer']['created'];
			}
		}
		return $update;
	}

	/**
	 * Provides data for a report based on various conditions.
	 *
	 * @param array $data Request data for the report
	 * @return array $combinedResults The report results
	 */
	public function findCustomerTotalsReport($data) {
		$limit = !empty($data['limit']) ? $data['limit'] : 10;

		if (!empty($data['field']) && array_key_exists($data['field'], $this->reportFields)) {
			$field = $data['field'];
		} else {
			$field = array_keys($this->reportFields)[0];
		}
		$nameField = $field;

		$conditions = [$field . ' IS NOT NULL'];

		if (!empty($data['to_date'])) {
			$toDate = $this->deconstruct('created', $data['to_date']);
			$conditions['Customer.created <='] = $toDate;
		}
		if (!empty($data['from_date'])) {
			$fromDate = $this->deconstruct('created', $data['from_date']);
			$conditions['Customer.created >='] = $fromDate;
		}

		$fields = [
			$field,
			'COUNT(DISTINCT(Customer.customers_id)) AS total',
		];

		$contain = [];
		switch ($field) {
			case 'ShippingAddress.entry_postcode':
				$contain = ['ShippingAddress'];
				break;
			case 'ShippingAddress.entry_country_id':
				$nameField = 'ShippingAddress.ShippingAddress.Country.countries_iso_code_2';
				$contain = ['ShippingAddress' => ['Country']];
				break;
			case 'ShippingAddress.entry_city':
				$contain = ['ShippingAddress'];
				break;
			case 'DefaultAddress.entry_postcode':
				$contain = ['DefaultAddress'];
				break;
			case 'DefaultAddress.entry_country_id':
				$nameField = 'DefaultAddress.DefaultAddress.Country.countries_iso_code_2';
				$contain = ['DefaultAddress' => ['Country']];
				break;
			case 'DefaultAddress.entry_city':
				$contain = ['DefaultAddress'];
				break;
			case 'Customer.insurance_amount':
				break;
		}

		$options = [
			'conditions' => $conditions,
			'fields' => $fields,
			'group' => [$field],
			'order' => ['total DESC'],
			'contain' => $contain,
			'limit' => $limit,
		];

		$results = $this->find('all', $options);

		$combinedResults = [];
		Hash::map($results, '{n}', function ($data) use (&$combinedResults, $nameField) {
			$data = Hash::flatten($data);
			$total = $data['0.total'];
			$total += isset($combinedResults[$data[$nameField]]) ? $combinedResults[$data[$nameField]]['y'] : 0;

			$combinedResults[$data[$nameField]] = [
				'name' => $data[$nameField],
				'y' => intval($total),
			];

			return true;
		});

		return array_values($combinedResults);
	}
}
