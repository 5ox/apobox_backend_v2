<?php
/**
 * Order
 */

App::uses('AppModel', 'Model');

/**
 * Order Model
 *
 * @property	Customers	$Customers
 */
class Order extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'orders_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'orders_id';

	/**
	 * Default order of records.
	 *
	 * @var mixed
	 */
	public $order = array(
		'Order.orders_id' => 'DESC',
	);

	/**
	 * Behaviors
	 *
	 */
	public $actsAs = array(
		'CreditCard'
	);

	public $virtualFields = array(
		'delivery_address' =>
			'CONCAT_WS(", ",
				NULLIF(delivery_company, ""),
				NULLIF(delivery_name, ""),
				NULLIF(delivery_street_address, ""),
				NULLIF(delivery_suburb, ""),
				NULLIF(delivery_city, ""),
				NULLIF(delivery_state, ""),
				NULLIF(delivery_postcode, ""),
				NULLIF(delivery_country, "")
			)',
		'inbound_tracking' =>
			'COALESCE(
				NULLIF(amazon_track_num, ""),
				NULLIF(ups_track_num, ""),
				NULLIF(fedex_track_num, ""),
				NULLIF(fedex_freight_track_num, ""),
				NULLIF(dhl_track_num, ""),
				NULLIF(usps_track_num_in, "")
			)',
		'inbound_carrier' =>
			'CASE
				WHEN NULLIF(amazon_track_num, "") IS NOT NULL THEN "amazon"
				WHEN NULLIF(ups_track_num, "") IS NOT NULL THEN "ups"
		WHEN NULLIF(fedex_track_num, "") IS NOT NULL THEN "fedex"
		WHEN NULLIF(fedex_freight_track_num, "") IS NOT NULL THEN "fedex_freight"
		WHEN NULLIF(dhl_track_num, "") IS NOT NULL THEN "dhl"
		WHEN NULLIF(usps_track_num_in, "") IS NOT NULL THEN "usps"
		ELSE NULL
			END',
		'dimensions' => 'CONCAT_WS(" x ", length, width, depth)',
	);

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'customers_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'A customer is required.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'orders_status' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Order status must be numeric.',
			),
		),
		'billing_status' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Billing status must be numeric.',
			),
		),
		'usps_track_num' => array(
			'maxLength' => array(
				'rule' => array('maxLength', 40),
				'message' => 'Outbound tracking number must be 40 characters or less.',
			),
		),
		'moved_to_invoice' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Moved to invoice must be `0` or `1`.',
			),
		),
		'mail_class' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Mail class is required.',
			),
		),
		'package_type' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Package type is required.',
			),
		),
		'customs_description' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Customs description is required.',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 255),
				'message' => 'Customs description may not be longer than 255 characters.',
			),
		),
		'weight_oz' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Package weight is required.',
			),
		),
		'width' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Package width is required.',
			),
		),
		'length' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Package length is required.',
			),
		),
		'depth' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'Package depth is required.',
			),
		),
		// Fake field for saving a history records comments
		'status_history_comments' => array(
			'maxLength' => array(
				'rule' => array('maxLength', 255),
				'message' => 'Comments must be 255 characters or less.',
				'allowEmpty' => true,
			),
		),
		// Fake field for saving tracking numbers into the correct field
		'carrier' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'required' => true,
				'on' => 'create',
				'message' => 'You must select a carrier.',
			),
		),
		'inbound_tracking_number' => array(
			'notBlank' => array(
				'rule' => 'validateTrackingNotEmpty',
				'required' => true,
				'on' => 'create',
				'message' => 'A tracking number is required to create an order.',
			),
		),
		'amazon_track_num' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],
		'ups_track_num' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],
		'usps_track_num_in' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],
		'fedex_track_num' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],
		'fedex_freight_track_num' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],
		'dhl_track_num' => [
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This inbound tracking number already exists in the system.',
				'allowEmpty' => true,
				'required' => false,
				'on' => 'create',
			],
		],

		// Customer and address fields required for a successful order
		'customers_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A customer name is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 64),
				'message' => 'Name may not be longer than 64 characters.',
			),
		),
		'customers_company' => array(
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'Company name may not be longer than 32 characters.',
				'allowEmpty' => true,
			),
		),
		'customers_street_address' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A street address is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 64),
				'message' => 'Street addresses may not be longer than 64 characters.',
			),
		),
		'customers_suburb' => array(
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'The second address line may not be longer than 32 characters.',
				'allowEmpty' => true,
			),
		),
		'customers_postcode' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A zip code is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 10),
				'message' => 'Zip codes may not be longer than 10 characters.',
			),
		),
		'customers_city' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A city is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'A city\'s name may not be longer than 32 characters.',
			),
		),
		'customers_state' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A state is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'A state\'s name may not be longer than 32 characters.',
			),
		),
		'customers_country' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'A country is required.',
				'required' => 'create',
			),
			'maxLength' => array(
				'rule' => array('maxlength', 32),
				'message' => 'A country name may not be longer than 32 characters.',
			),
		),
		'customers_email_address' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Email address is required.',
				'required' => 'create',
			),
			'email' => array(
				'rule' => array('email'),
				'message' => 'This doesn\'t look like a valid email address.',
			),
		),
		'customers_address_format_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'An address format id must be numeric.',
				'required' => 'create',
			),
		),

		// Force insurance_coverage to be numeric for it's varchar field type
		'insurance_coverage' => array(
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
		'OrderStatus' => array(
			'foreignKey' => 'orders_status',
		),
	);

	/**
	 * hasOne associations
	 *
	 * @var	array
	 */
	public $hasOne = array(
		'OrderShipping' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderShipping.class' => 'ot_shipping')
		),
		'OrderStorage' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderStorage.class' => 'ot_custom')
		),
		'OrderInsurance' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderInsurance.class' => 'ot_insurance')
		),
		'OrderFee' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderFee.class' => 'ot_fee')
		),
		'OrderRepack' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderRepack.class' => 'ot_custom_1')
		),
		'OrderBattery' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderBattery.class' => 'ot_custom_2')
		),
		'OrderReturn' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderReturn.class' => 'ot_custom_3')
		),
		'OrderMisaddressed' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderMisaddressed.class' => 'ot_custom_4')
		),
		'OrderShipToUS' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderShipToUS.class' => 'ot_custom_5')
		),
		'OrderSubtotal' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderSubtotal.class' => 'ot_subtotal')
		),
		'OrderTotal' => array(
			'foreignKey' => 'orders_id',
			'conditions' => array('OrderTotal.class' => 'ot_total')
		),
		'CustomPackageRequest' => array(
			'foreignKey' => 'orders_id',
			'dependent' => false,
		),
	);

	/**
	 * hasMany associations
	 *
	 * @var	array
	 */
	public $hasMany = array(
		'OrderStatusHistory' => array(
			'foreignKey' => 'orders_id',
			'order' => 'date_added DESC',
			'dependent' => true,
		),
		'CustomerReminder' => array(
			'foreignKey' => 'orders_id',
			'dependent' => true,
		),
		'OrderData' => array(
			'foreignKey' => 'orders_id',
			'dependent' => true,
		),
	);

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * A flag to set once OrderTotal record is verified to exist. Prevents
	 * multiple checks for each Order* model.
	 */
	public $orderTotalExists = false;

	/**
	 * Order status ids that allow a charge to proceed.
	 *
	 * @var array
	 */
	protected $_orderStatusToCharge = array(1, 2);

	/**
	 * Valid intervals available for order totals report.
	 *
	 * @var array
	 */
	protected $_validIntervals = array(
		'year' => 'Yearly',
		'month' => 'Monthly',
		'week' => 'Weekly',
		'day' => 'Daily',
	);

	/**
	 * Valid fields a report can be sorted by.
	 *
	 * @var array
	 */
	protected $_validSortFields = array(
		'date_purchased' => 'Date',
		'total' => 'Order Count',
		'ot_fee' => 'Fee',
		'ot_insurance' => 'Insurance',
		'ot_shipping' => 'Shipping',
	);

	/**
	 * Map of mail classes keyed to the customer default_postal_type field.
	 *
	 * The apobox_direct class is not actively used, so will return null. The
	 * same as if it was not set.
	 */
	public $customerMailClassMap = [
		'priority_mail' => 'priority',
		'parcel_post' => 'parcel',
		'apobox_direct' => null,
	];

	/**
	 * Ensure the tracking number exists if a carrier is set. Or allow empty
	 * if carrier is set to "none".
	 *
	 * @param array $check An array with the single key/value pair being checked.
	 * @return bool
	 */
	public function validateTrackingNotEmpty($check) {
		if ($this->data['Order']['carrier'] === 'none') {
			return true;
		}

		return !empty(array_values($check)[0]);
	}

	/**
	 * create
	 *
	 * @param array $data Optional data array to assign to the model after it is created
	 * @param bool $filterKey If true, overwrites any primary key input with an empty value
	 * @return array The current Model::data; after merging $data and/or defaults from database
	 */
	public function create($data = array(), $filterKey = false) {
		$defaultData = array(
			'orders_status' => 1,
			'billing_status' => 2,
			'payment_method' => 'Payments Pro',
			'amazon_track_num' => '',
			'ups_track_num' => '',
			'usps_track_num' => '',
			'usps_track_num_in' => '',
			'fedex_track_num' => '',
			'fedex_freight_track_num' => '',
			'dhl_track_num' => '',
			'currency' => 'USD',
			'currency_value' => 1.000000,
			'shipping_tax' => 0.0000,
			'qbi_imported' => 0,
			'package_flow' => 0,
			'shipped_from' => '',
			'warehouse' => Configure::read('Warehouse.code'),
			'moved_to_invoice' => 0,
		);
		$data = Hash::merge($defaultData, $data);

		return parent::create($data, $filterKey);
	}

	/**
	 * beforeValidate
	 *
	 * @param array $options Optional options passed from Model::save()
	 * @return bool True
	 */
	public function beforeValidate($options = array()) {
		if (!empty($this->data[$this->alias]['mail_class'])) {
			$this->data[$this->alias]['mail_class'] = strtoupper($this->data[$this->alias]['mail_class']);
		}
		if (!empty($this->data[$this->alias]['package_type'])) {
			$this->data[$this->alias]['package_type'] = strtoupper($this->data[$this->alias]['package_type']);
		}
		$this->data[$this->alias]['last_modified'] = $this->getDatetime()->format('Y-m-d H:i:s');
		$this->marshalTrackingNumber();

		return true;
	}

	/**
	 * beforeSave
	 *
	 * @param array $options Optional options passed from Model::save()
	 * @return bool True
	 */
	public function beforeSave($options = array()) {
		parent::beforeSave($options);
		$this->shouldCreateOrderStatusHistory = $this->determineOrderStatusHistory();

		return true;
	}

	/**
	 * afterSave
	 *
	 * @param bool $created True if this save created a new record
	 * @param array $options Optional options passed from Model::save()
	 * @return void
	 */
	public function afterSave($created, $options = array()) {
		parent::afterSave($created, $options);
		$this->orderStatusAfterSave();
		if ($created) {
			$this->id = $this->getLastInsertId();
			$this->saveField('date_purchased', $this->getDatetime()->format('Y-m-d H:i:s'));
			$this->createOrderDetailsForOrder();
		}
	}

	/**
	 * onError
	 *
	 * @return void
	 */
	public function onError() {
		$this->shouldCreateOrderStatusHistory = false;
	}

	/**
	 * Evaluates the data to determine if an OrderStatusHistory record should be
	 * inserted.
	 *
	 * @return bool
	 */
	public function determineOrderStatusHistory() {
		$data = !empty($this->data[$this->alias]) ? $this->data[$this->alias] : [];
		if (!empty($data['notify_customer'])) {
			return true;
		}
		if (empty($data['orders_status']) && empty($data['status_history_comments'])) {
			return false;
		}
		if (!empty($data['orders_status'])) {
			$oldStatus = $this->field('orders_status', array($this->primaryKey => $this->id));
			if ($oldStatus != $data['orders_status']) {
				return true;
			}
		}
		if (!empty($data['status_history_comments'])) {
			$oldComments = $this->field('comments', array($this->primaryKey => $this->id));
			if ($oldComments != $data['status_history_comments']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * orderStatusAfterSave
	 *
	 * @return bool True on successful save, false on error or not creating a record
	 */
	public function orderStatusAfterSave() {
		if (empty($this->shouldCreateOrderStatusHistory)) {
			return false;
		}

		return $this->OrderStatusHistory->record($this->data);
	}

	/**
	 * marshalTrackingNumber
	 *
	 * @return void
	 */
	public function marshalTrackingNumber() {
		if (!empty($this->data[$this->alias]['carrier'])) {
			// Save inbound tracking number to apropriate field based on carrier
			$carrier = $this->data[$this->alias]['carrier'];
			$carrier .= '_track_num';
			if ($carrier == 'usps_track_num') {
				$carrier .= '_in';
			}
			if (isset($this->data[$this->alias]['inbound_tracking_number'])) {
				$this->data[$this->alias][$carrier] = $this->data[$this->alias]['inbound_tracking_number'];
			} else {
				$this->data[$this->alias][$carrier] = null;
			}
		}
	}

	/**
	 * createOrderDetailsForOrder
	 *
	 * @param mixed $id The optional order id
	 * @return void
	 */
	public function createOrderDetailsForOrder($id = null) {
		if ($id != null) {
			$this->id = $id;
		}

		$data = array('orders_id' => $this->id);

		$this->OrderShipping->set($data);
		$this->OrderShipping->save();

		$this->OrderStorage->set($data);
		$this->OrderStorage->save();

		$this->OrderInsurance->set(array(
			'orders_id' => $this->id,
			'value' => $this->calculateInsurance(),
		));
		$this->OrderInsurance->save();

		$this->addDefaultOrderFee();

		$this->OrderRepack->set($data);
		$this->OrderRepack->save();

		$this->OrderBattery->set($data);
		$this->OrderBattery->save();

		$this->OrderReturn->set($data);
		$this->OrderReturn->save();

		$this->OrderMisaddressed->set($data);
		$this->OrderMisaddressed->save();

		$this->OrderShipToUS->set($data);
		$this->OrderShipToUS->save();

		$this->OrderSubtotal->set($data);
		$this->OrderSubtotal->save();

		$this->OrderTotal->set($data);
		$this->OrderTotal->save();

		$this->OrderTotal->updateTotal($this->id);
	}

	/**
	 * Finds the corresponding insurance amount from Insurance based on
	 * the value of $coverage.
	 *
	 * @return mixed The insurance amount or false
	 */
	public function calculateInsurance() {
		$coverage = $this->field('insurance_coverage');
		return ClassRegistry::init('Insurance')->getFeeForCoverageAmount($coverage);
	}

	/**
	 * Adds a OrderFee record. This is useful for orders created in the old
	 * system that do not have one.
	 *
	 * @return array
	 */
	public function addDefaultOrderFee() {
		$weight = $this->field('weight_oz');

		$this->OrderFee->set(array(
			'orders_id' => $this->id,
			'value' => $this->OrderFee->getFee($weight),
		));
		$result = $this->OrderFee->save();
		return $result['OrderFee'];
	}

	/**
	 * Returns the billing address saved on the order in a record array format.
	 *
	 * @see Address::findForPayment Contains similar logic, and should be reviewed when structure changes are made in this method.
	 * @return array The billing address saved on the order in a record array format.
	 */
	public function addressForPayment() {
		$order = $this->read();
		$country = $this->Customer->Address->Country->findByCountriesName($order['Order']['billing_country']);
		return array(
			'Address' => array(
				'entry_street_address' => $order['Order']['billing_street_address'],
				'entry_suburb' => $order['Order']['billing_suburb'],
				'entry_city' => $order['Order']['billing_city'],
				'entry_postcode' => $order['Order']['billing_postcode'],
			),
			'Zone' => array('zone_code' => $order['Order']['billing_state']),
			'Country' => $country['Country'],
		);
	}

	/**
	 * Updates the order after a successful payment.
	 *
	 * @param array $customer The customer data array.
	 * @param array $requestData Optional request data array.
	 * @return mixed The save result.
	 */
	public function recordPayment($customer, $requestData = array()) {
		$order = $this->read();
		$newData = array(
			'payment_method' => 'Payments Pro',
			'cc_type' => '',
			'cc_owner' => $customer['cc_firstname'] . ' ' . $customer['cc_lastname'],
			'cc_number' => $customer['cc_number'],
			'cc_expires' => sprintf('%02d', $customer['cc_expires_month']) . sprintf('%02d', $customer['cc_expires_year']),

			'orders_status' => $this->paymentStatus($requestData),
			'billing_status' => $this->paymentStatus($requestData),
			'notify_customer' => 1,
			'last_modified' => date_format(date_create(), 'Y-m-d H:i:s'),
		);
		$order['Order'] = array_merge($order['Order'], $newData);
		return $this->save($order, true, array_keys($newData));
	}

	/**
	 * Set the order status as provided or default to 4 (Paid).
	 *
	 * @param array $data The request data.
	 * @return int The order status id.
	 */
	protected function paymentStatus($data) {
		return !empty($data['orders_status']) ? $data['orders_status'] : 4;
	}

	/**
	 * Updates the order after a successful invoice.
	 *
	 * @param array $customer The customer data array.
	 * @return mixed The save result.
	 */
	public function recordInvoicePayment($customer) {
		$order = $this->read();
		$newData = array(
			'payment_method' => 'Invoice',
			'cc_type' => 'Invoice',
			'cc_owner' => $customer['customers_firstname'] . ' ' . $customer['customers_lastname'],
			'cc_number' => 'Invoice',
			'cc_expires' => 'INV',
			'orders_status' => 1,
			'billing_status' => 5,
			'notify_customer' => 1,
		);
		$order['Order'] = array_merge($order['Order'], $newData);
		return $this->save($order, true, array_keys($newData));
	}

	/**
	 * Available custom/magic find methods
	 *
	 * @var array
	 */
	public $findMethods = array(
		'awaitingPayments' => true,
	);

	/**
	 * Magic find for "Awaiting Payment" (2)
	 *
	 * @param string $state The state of the find.
	 * @param array $query Additional find parameters.
	 * @param array $results The results if in `after` state.
	 * @return array
	 */
	protected function _findAwaitingPayments($state, $query, $results = array()) {
		if ($state === 'before') {
			$query['conditions'][$this->alias . '.orders_status'] = 2;
			return $query;
		}
		return $results;
	}

	/**
	 * markAs
	 *
	 * @param int $id The order id
	 * @param int $ordersStatusId The ordersStatusId
	 * @param array $data The data
	 * @return mixed
	 */
	public function markAs($id, $ordersStatusId, $data = array()) {
		if (!empty($id)) {
			$this->id = $id;
		}
		$id = $this->id;
		if (!$this->exists()) {
			return false;
		}

		if (!empty($data)) {
			$this->set($data);
		}

		$this->set([
			'orders_id' => $id, // prevents override in $data
			'orders_status' => $ordersStatusId,
			'last_modified' => date_format($this->getDatetime(), 'Y-m-d H:i:s'),
		]);

		if ($ordersStatusId == 3) {
			// set the turnaround_sec time
			$this->set(['turnaround_sec' => $this->getTurnaround($id)]);
		}

		$result = $this->save();
		if ($result && $ordersStatusId == 4) {
			// purge CustomerReminder records for this order when paid
			$this->CustomerReminder->purge($id);
		}
		if ($result && $ordersStatusId == 3) {
			// check for and update an associated custom order's package_status
			$this->CustomPackageRequest->updatePackageStatusToOrderStatus($id);
			// purge OrderData records for type `fedex-zpl`
			$this->OrderData->clearOrderData($id, 'fedex-zpl');
		}
		return $result;
	}

	/**
	 * Change an order's status to "Warehouse"
	 *
	 * @param mixed $id The order id
	 * @param array $data The optional data array
	 * @return mixed The array result of the save or false on failure
	 */
	public function markAsWarehouse($id = null, $data = array()) {
		return $this->markAs($id, 1, $data);
	}

	/**
	 * Change an order's status to "Awaiting Payment"
	 *
	 * @param mixed $id The order id
	 * @param array $data The optional data array
	 * @return mixed The array result of the save or false on failure
	 */
	public function markAsAwaitingPayment($id = null, $data = array()) {
		return $this->markAs($id, 2, $data);
	}

	/**
	 * Change an order's status to "Shipped"
	 *
	 * @param mixed $id The order id
	 * @param array $data The optional data array
	 * @return mixed The array result of the save or false on failure
	 */
	public function markAsShipped($id = null, $data = array()) {
		return $this->markAs($id, 3, $data);
	}

	/**
	 * Change an order's status to "Paid"
	 *
	 * @param mixed $id The order id
	 * @param array $data The optional data array
	 * @return mixed The array result of the save or false on failure
	 */
	public function markAsManuallyPaid($id = null, $data = array()) {
		return $this->markAs($id, 4, $data);
	}

	/**
	 * Change an order's status to "Returned"
	 *
	 * @param mixed $id The order id
	 * @param array $data The optional data array
	 * @return mixed The array result of the save or false on failure
	 */
	public function markAsReturned($id = null, $data = array()) {
		return $this->markAs($id, 5, $data);
	}

	/**
	 * Checks if a customer is an invoice customer, and optionally
	 * disables the CreditCard behavior to allow processing a order without
	 * making a charge.
	 *
	 * @param array $customer A customer data array
	 * @param bool $disable Whether to disable the CreditCard behavior
	 * @return bool
	 */
	public function checkForInvoiceCustomer($customer, $disable = false) {
		if (
			isset($customer['billing_type']) && $customer['billing_type'] == 'invoice' &&
			isset($customer['invoicing_authorized']) && $customer['invoicing_authorized'] == true
		) {
			if ($disable) {
				$this->Behaviors->disable('CreditCard');
			}
			return true;
		}
		return false;
	}

	/**
	 * Finds an order and necessary related data for a charge identified by $id
	 *
	 * @param mixed $id The order id
	 * @return mixed The result of the find
	 */
	public function findOrderForCharge($id = null) {
		$options = array(
			'contain' => array(
				'Customer',
				'OrderShipping',
				'OrderStorage',
				'OrderInsurance',
				'OrderFee',
				'OrderRepack',
				'OrderBattery',
				'OrderReturn',
				'OrderMisaddressed',
				'OrderShipToUS',
				'OrderSubtotal',
				'OrderTotal',
				'OrderStatus',
			),
			'conditions' => array(
				'Order.orders_id' => $id
			),
		);
		return $this->find('first', $options);
	}

	/**
	 * Saves an order before making a charge/invoice
	 *
	 * @param array $data The data to save
	 * @return mixed The result of saveAssociated()
	 */
	public function saveOrderForCharge($data = array()) {
		$options = array(
			'fieldList' => array(
				'Order' => array(
					'orders_status',
					'last_modified',
					'mail_class',
				),
				'OrderShipping' => array('value'),
				'OrderStorage' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderInsurance' => array('value'),
				'OrderFee' => array('value'),
				'OrderRepack' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderBattery' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderReturn' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderMisaddressed' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderShipToUS' => array(
					'value',
					'title',
					'sort_order',
					'class',
				),
				'OrderSubtotal' => array('value'),
			),
		);
		return $this->saveAssociated($data, $options);
	}

	/**
	 * * Checks if an order is in a valid status to be charged.
	 * * Checks if the customer's account is closed.
	 *
	 * @param array $order An order
	 * @return array $result [allow] true if the charge can proceed, false plus
	 * message if the charge will abort.
	 */
	public function checkIfOrderCanBeCharged($order) {
		if (!in_array($order['Order']['orders_status'], $this->_orderStatusToCharge)) {
			return array(
				'allow' => false,
				'message' => 'Orders cannot be charged while in status: ' . ucfirst($order['OrderStatus']['orders_status_name'])
			);
		}

		if (!$order['Customer']['is_active']) {
			$emailVars = array(
				'type' => 'closed account charge',
				'subject' => 'Closed Account Charge',
				'message' => 'A charge was attempted for order ' . $order['Order']['orders_id'] . ' with  closed account ' . $order['Customer']['billing_id'] . '.',
			);
			$task = $this->taskFactory();
			$task->createJob('AppEmail',
				[
					'method' => 'sendManagerMessage',
					'recipient' => Configure::read('Email.Address.support'),
					'vars' => $emailVars,
				],
				null,
				'Order::checkIfOrderCanBeCharged',
				$order['Customer']['billing_id']
			);
			return array(
				'allow' => false,
				'message' => 'The order can not be charged because customer ' . $order['Customer']['billing_id'] . ' has a closed account.',
			);
		}
		return array(
			'allow' => true,
		);
	}

	/**
	 * Saves an order.
	 *
	 * @param mixed $data The model data to save
	 * @return mixed The result of the save
	 */
	public function saveOrder($data) {
		unset($data['CustomPackageRequest']);
		$this->create();
		return $this->save($data);
	}

	/**
	 * Checks for valid fields from user supplied data before proceeding to
	 * setup and save an order.
	 *
	 * @param mixed $data The model data
	 * @return bool True
	 * @throws BadRequestException
	 */
	public function checkOrderKeys($data) {
		$valid = array(
			'customers_id',
			// required
			'carrier',
			'inbound_tracking_number',
			'length',
			'width',
			'depth',
			'weight_oz',
			'mail_class',
			'package_type',
			// optional
			'customs_description',
			'customers_address_id',
			'delivery_address_id',
			'billing_address_id',
			'insurance',
			'insurance_coverage',
			'comments',
		);
		$valid = array_merge($valid, $this->getOrderAddressFields());
		foreach ($data as $key => $val) {
			if (!in_array($key, $valid)) {
				throw new BadRequestException("An invalid key exists in the request: $key");
			}
		}
		return true;
	}

	/**
	 * Provides data for a report based on various conditions.
	 *
	 * @param array $data Optional search data
	 * @return array $combinedResults The report results
	 */
	public function findOrderTotalsReport($data = array()) {
		$interval = (!empty($data['interval']) && array_key_exists($data['interval'], $this->_validIntervals) ?
			strtoupper($data['interval']) : 'DAY');

		$sortField = (!empty($data['sort']) && array_key_exists($data['sort'], $this->_validSortFields) ?
			$data['sort'] : 'date_purchased');
		$sortDirection = (!empty($data['direction']) ? $data['direction'] : 'asc');

		$toDate = $this->deconstruct('date_purchased', $data['to_date']);
		$fromDate = $this->deconstruct('date_purchased', $data['from_date']);
		$fromYear = DateTime::createFromFormat('Y-m-d H:i:s', $fromDate)->format('Y');
		$toYear = DateTime::createFromFormat('Y-m-d H:i:s', $toDate)->format('Y');

		switch ($interval) {
			case 'DAY':
				$dateGroup = '%Y-%m-%d';
				$dateSuffix = ($toYear != $fromYear ? ' \'%y' : '');
				$dateString = '%b %e' . $dateSuffix;
				break;
			case 'WEEK':
				$dateGroup = '%Y-%v';
				$dateString = 'Week %v \'%y';
				break;
			case 'MONTH':
				$dateGroup = '%Y-%m';
				$dateString = '%b \'%y';
				break;
			case 'YEAR':
				$dateGroup = '%Y';
				$dateString = '%Y';
				break;
		}

		$options = array(
			'conditions' => array(
				'Order.date_purchased >=' => $fromDate,
				'Order.date_purchased <=' => $toDate,
			),
			'fields' => array(
				'DATE_FORMAT(Order.date_purchased, "' . $dateGroup . '") AS date_group',
				'DATE_FORMAT(Order.date_purchased, "' . $dateString . '") AS date_string',
				'COUNT(DISTINCT(Order.orders_id)) AS total',
				'SUM(OrderFee.value) AS ot_fee',
				'SUM(OrderInsurance.value) AS ot_insurance',
				'SUM(OrderShipping.value) AS ot_shipping',
			),
			'group' => array(
				'date_group',
			),
			'order' => array(
				$sortField => $sortDirection,
			),
			'contain' => array(
				'OrderFee',
				'OrderInsurance',
				'OrderShipping',
			),
		);
		if (!empty($data['orders_status'])) {
			$options['conditions']['Order.orders_status'] = $data['orders_status'];
		}

		$results = $this->find('all', $options);
		$combinedResults = Hash::map($results, '{n}', function ($data) {
			return array(
				'date_purchased' => $data[0]['date_string'],
				'total' => $data[0]['total'],
				'ot_fee' => $data[0]['ot_fee'],
				'ot_insurance' => $data[0]['ot_insurance'],
				'ot_shipping' => $data[0]['ot_shipping'],
			);
		});

		return $combinedResults;
	}

	/**
	 * Finds the total number of orders per each order status
	 *
	 * @return array $combinedResults `orders_status_name => count`
	 */
	public function findTotalsPerStatus() {
		$options = array(
			'fields' => array(
				'COUNT(Order.orders_status) AS total',
				'OrderStatus.orders_status_name',
			),
			'group' => array(
				'Order.orders_status',
			),
			'contain' => array(
				'OrderStatus',
			),
		);
		$results = $this->find('all', $options);

		$combinedResults = Hash::map($results, '{n}', function ($data) {
			return array(
				$data['OrderStatus']['orders_status_name'] => $data[0]['total'],
			);
		});
		return $combinedResults;
	}

	/**
	 * Adds `weight_lb` to `weight_oz` to get the total weight in ounces. As
	 * this happens before validation, both are checked to make sure values are
	 * numeric. If the check fails, the save won't proceed due to the presence
	 * of the `weight_lb` key.
	 *
	 * @param array $data The data
	 * @return array $data The modified request data
	 */
	public function setWeight($data) {
		if ((!empty($data['weight_lb']) && !is_numeric($data['weight_lb'])) || !is_numeric($data['weight_oz'])) {
			return $data;
		}
		if (!empty($data['weight_lb'])) {
			$oz = ($data['weight_lb'] * 16) + $data['weight_oz'];
			$data['weight_oz'] = (float)$oz;
		}
		unset($data['weight_lb']);
		return $data;
	}

	/**
	 * Finds all orders awaiting payment that either have no CustomerReminder
	 * record or `CustomerReminder.reminder_count` less than the specified limit.
	 * If found, and `$charge` is `true`, a charge is attempted for the order.
	 * The charge result is added to the order array and if successful, existing
	 * `awaiting_payment` CustomerReminders are cleared.
	 *
	 * @param bool $charge Attempt to charge the order or not
	 * @return array The order and customer ids to alert or empty.
	 */
	public function findAndChargeAllOrdersAwaitingPayment($charge = false) {
		$paymentReminders = Configure::check('Orders.paymentReminders') ? Configure::read('Orders.paymentReminders') : 3;
		$options = [
			'conditions' => [
				'Customer.is_active' => 1,
			],
			'fields' => [
				'orders_id',
				'customers_id',
				'orders_status',
			],
			'contain' => [
				'OrderTotal' => [
					'fields' => [
						'value',
					],
				],
				'CustomerReminder' => [
					'conditions' => [
						'reminder_type' => 'awaiting_payment',
					],
				],
				'Customer' => [
					'fields' => [
						'billing_id',
						'customers_email_address',
						'customers_firstname',
						'customers_lastname',
						'cc_firstname',
						'cc_lastname',
						'cc_number_encrypted',
						'cc_number',
						'cc_expires_month',
						'cc_expires_year',
						'card_token',
						'is_active',
					],
				],
			],
		];
		$orders = $this->find('awaitingPayments', $options);

		$alerts = [];
		foreach ($orders as $order) {
			$count = Hash::get($order, 'CustomerReminder.0.reminder_count', 0);
			$charged = false;
			if ($count < $paymentReminders) {
				if ($charge) {
					$charged = $this->processCharge($order);
					if ($charged) {
						$this->CustomerReminder->clearRecord(
							$order['Order']['orders_id'],
							'awaiting_payment'
						);
					} else {
						$this->OrderStatusHistory->recordChargeFailed($order);
					}
				}
				$order['Customer']['customers_fullname'] = $order['Customer']['customers_firstname'] . ' ' .
					$order['Customer']['customers_lastname'];
				$order['Order']['charged'] = $charged;
				$alerts[] = $order;
			}
		}
		return $alerts;
	}

	/**
	 * The mail class as provided from the customer's default_postal_type.
	 *
	 * @param string $customerClass The customer's default_postal_type field.
	 * @return string
	 */
	public function mailClassFromCustomer($customerClass) {
		if (!isset($this->customerMailClassMap[$customerClass])) {
			return null;
		}
		return $this->customerMailClassMap[$customerClass];
	}

	/**
	 * Process the charge and record payment.
	 *
	 * This method contains logic duplicated in controllers. All of the
	 * duplicated logic should be moved into a more general `Lib/Payment` class.
	 *
	 * @param array $order The order record.
	 * @return bool|string True on success, or string if partial success.
	 */
	public function processCharge($order) {
		$this->id = $order['Order']['orders_id'];
		$options = [
			'address' => $this->addressForPayment(),
			'total' => sprintf('%2.f', $order['OrderTotal']['value']),
			'description' => 'Order #' . $this->id
		];

		$allowCharge = $this->checkIfOrderCanBeCharged($order);
		if (!$allowCharge['allow']) {
			return $allowCharge['message'];
		}

		if ($this->charge($order['Customer'], $options)) {
			$recorded = $this->recordPayment($order['Customer']);
			$this->sendStatusUpdateEmail($this->id);
			if (!$recorded) {
				$this->log(
					'OrderModel::processCharge: Order #' . $this->id . ' was not properly recorded.',
					'orders'
				);
			}

			return $recorded ? true : 'not recorded';
		}

		return false;
	}

	/**
	 * Charge a credit card (array) or a stored card (string) with a simple single transaction.
	 *
	 * This method is a duplicate of the `charge()` method in the Payment
	 * component. These should be moved into a more general `Lib/Payment` class.
	 *
	 * @param mixed $card credit card data in an array or a string of a stored card ID to charge.
	 * @param array $options any options that Lib/Payment::chargeCard accepts.
	 * @return mixed False on failure, Payment ID on success.
	 */
	public function charge($card, $options = []) {
		$this->paymentLib = $this->getPaymentLib();
		$card = $this->Customer->initForCharge($card);
		try {
			$result = $this->paymentLib->chargeCard($card, $options);
			return $result;
		} catch (Exception $exception) {
			$message = $exception->getMessage() ? $exception->getMessage() : 'Unspecified charge error';
			$this->log('OrderModel::charge: ' . $message, 'orders');
			return false;
		}

		return true;
	}

	/**
	 * Sends email for order status updates.
	 *
	 * @param int $id The order id.
	 * @throws NotFoundException
	 * @return bool
	 */
	public function sendStatusUpdateEmail($id) {
		$order = $this->find('first', [
			'contain' => [
				'Customer',
				'OrderStatus',
				'OrderStatusHistory',
			],
			'conditions' => [
				'Order.orders_id' => $id
			],
		]);

		if (!$order) {
			throw new NotFoundException('The order was not found.');
		}

		$task = $this->taskFactory();

		$recipient = [
			$order['Customer']['customers_email_address'] =>
				$order['Customer']['customers_firstname'] . ' ' . $order['Customer']['customers_lastname']
		];
		$vars = [
			'firstName' => $order['Customer']['customers_firstname'],
			'lastName' => $order['Customer']['customers_lastname'],
			'orderId' => $id,
			'status' => $order['OrderStatus']['orders_status_name'],
			'comments' => Hash::get($order, 'OrderStatusHistory.0.comments'),
			'order' => $order,
		];

		// Awaiting Payment
		if ($order['Order']['orders_status'] == 2) {
			unset($vars['status']);
			return $task->createJob('AppEmail',
				[
					'method' => 'sendFailedPayment',
					'recipient' => $recipient,
					'vars' => $vars
				],
				null,
				'Order::sendFailedPayment',
				$order['Customer']['billing_id']
			);
		}
		// Shipped
		if ($order['Order']['orders_status'] == 3) {
			$mailClass = ($order['Order']['mail_class'] == 'FEDEX') ? 'Fedex' : 'Usps';
			$tracking = [
				'outboundTracking' => $order['Order']['usps_track_num'],
				'inboundTracking' => $order['Order']['usps_track_num_in'],
				'trackingUrl' => Configure::read("ShippingApis.${mailClass}.trackingUrl"),
			];
			$vars += $tracking;
			return $task->createJob('AppEmail',
				[
					'method' => 'sendShipped',
					'recipient' => $recipient,
					'vars' => $vars
				],
				null,
				'Order::sendShipped',
				$order['Customer']['billing_id']
			);
		}

		return $task->createJob('AppEmail',
			[
				'method' => 'sendStatusUpdate',
				'recipient' => $recipient,
				'vars' => $vars
			],
			null,
			'Order::sendStatusUpdate',
			$order['Customer']['billing_id']
		);
	}

	/**
	 * Returns an array of customer related fields required for an order.
	 *
	 * @return array
	 */
	public function getOrderAddressFields() {
		$fields = [
			'name',
			'company',
			'street_address',
			'suburb',
			'city',
			'postcode',
			'state',
			'country',
			'address_format_id',
		];
		$typeFields = array_map(function ($field) {
			$types = [
				'customers',
				'delivery',
				'billing',
			];
			foreach ($types as $type) {
				$result[] = $type . '_' . $field;
			}
			return $result;
		}, $fields);
		$typeFields = call_user_func_array('array_merge', $typeFields);
		array_push($typeFields, 'customers_email_address');
		rsort($typeFields);
		return $typeFields;
	}

	/**
	 * Checks to see if an order uses FedEx for shipping. The requirements are
	 * a US delivery address and `mail_class` = FEDEX.
	 *
	 * @param array $order The order data array
	 * @return bool True if the order uses FedEx for shipping
	 */
	public function usesFedex($order) {
		if ($this->validFedexDestination($order)) {
			if ($order['Order']['mail_class'] == 'FEDEX') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks FedEx sized packages to make sure they meet all criteria.
	 *
	 * @param array $order The order data array
	 * @return bool True if the order is valid for saving
	 */
	public function validShipping($order) {
		if ($this->packageSize($order) > 130 || $order['Order']['weight_oz'] > 1120) {
			return $this->validFedexDestination($order);
		}
		return true;
	}

	/**
	 * Checks an order for the presence of an APO zone and country in the
	 * validCountries configure variable array.
	 *
	 * @param array $order The order data array
	 * @return bool True if the order has a valid Fedex delivery address
	 */
	protected function validFedexDestination($order) {
		if (
			in_array($order['Order']['delivery_state'], Configure::read('ShippingApis.apoZones')) ||
			!in_array($order['Order']['delivery_country'], Configure::read('ShippingApis.Fedex.validCountries'))
		) {
			return false;
		}
		return true;
	}

	/**
	 * Calculates the total size of a package.
	 *
	 * @param array $order The order data array
	 * @return int The total package size in inches
	 */
	protected function packageSize($order) {
		return $order['Order']['length'] + ($order['Order']['width'] * 2) + ($order['Order']['depth'] * 2);
	}

	/**
	 * Finds an order specified by $id and checks to see if `Order.inbound_tracking`
	 * exists in the `Tracking.tracking_id` field. If `Tracking.tracking_id` is
	 * found and if it's earlier than `Order.inbound_tracking` it's used to
	 * calculate the number of seconds for `Order.turnaround_sec`. If it's not
	 * found or later than `Order.inbound_tracking` then `Order.inbound_tracking`
	 * is used for the calculation.
	 *
	 * @param int $id The order id
	 * @return int The `turnaround_sec` value in seconds
	 */
	public function getTurnaround($id) {
		$order = $this->findByOrdersId($id);
		$timestamp = new DateTime($order['Order']['date_purchased']);

		$Tracking = ClassRegistry::init('Tracking');
		$trackingRecord = $Tracking->findByTrackingId($order['Order']['inbound_tracking']);
		$trackingDate = null;
		if ($trackingRecord) {
			$trackingDate = new DateTime($trackingRecord['Tracking']['timestamp']);
		}

		if ($trackingDate && $trackingDate->getTimeStamp() < $timestamp->getTimeStamp()) {
			$timestamp = $trackingDate;
		}

		$now = $this->getDateTime();
		return $now->getTimeStamp() - $timestamp->getTimeStamp();
	}

	/**
	 * Return a count of orders with given tracking number.
	 *
	 * @param string $trackingNumber An inbound tracking number
	 * @return int
	 */
	public function countByInboundTracking($trackingNumber) {
		return $this->find('count', ['conditions' => [
			'inbound_tracking' => $trackingNumber,
		]]);
	}
}
