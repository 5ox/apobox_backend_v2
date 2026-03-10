<?php

App::uses('AppModel', 'Model');

/**
 * CustomPackageRequest Model
 *
 * @property	Customers	$Customers
 */
class CustomPackageRequest extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'custom_orders';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'custom_orders_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'custom_orders_id';

	/**
	 * Default order of records.
	 *
	 * @var mixed
	 */
	public $order = [
		'CustomPackageRequest.custom_orders_id' => 'DESC',
	];

	/**
	 * List of package statuses.
	 */
	public $packageStatuses = [
		1 => 'Awaiting Package',
		3 => 'Shipped',
	];

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = [
		'custom_orders_id' => [
			'rule' => ['naturalNumber', false],
			'required' => 'update',
			'message' => 'Customer ID must be numeric.',
		],
		'customers_id' => [
			'rule' => ['naturalNumber', false],
			'required' => 'create',
			'message' => 'Customer ID must be numeric.',
		],
		'tracking_id' => [
			'alphaNumeric' => [
				'rule' => 'alphaNumeric',
				'required' => 'create',
				'message' => 'A tracking ID must be only letters and numbers.',
			],
			'unique' => [
				'rule' => ['isUnique'],
				'message' => 'This tracking ID already exists in the system.',
			],
			'exists' => [
				'rule' => 'trackingIdNotInOrder',
				'required' => 'create',
				'message' => 'A custom package request cannot be added for an already received package.',
			],
		],
		'billing_id' => [
			'rule' => '/^[0-9A-Z]{2,3}\d{1,4}$/i',
			'required' => 'create',
			'message' => 'A billing ID must be letters and digits.',
		],
		'orders_id' => [
			// Defaults to 0
			// Sets to order id in warehouse
			'rule' => ['naturalNumber', true],
			'message' => 'Order ID must be numeric.',
		],
		'package_status' => [
			// Should default to 1, changing this does not seem to affect view
			// 3 seems to be the completed status, but can't find docs
			'rule' => ['naturalNumber', false],
			'message' => 'Package status must be numeric.',
		],
		'package_repack' => [
			'rule' => ['inList', ['no', 'yes']],
			'required' => 'create',
			'message' => 'Repack must be yes or no.',
		],
		'insurance_coverage' => [
			'numeric' => [
				'rule' => ['numeric'],
				'allowEmpty' => true,
				'message' => 'Please enter a numeric (numbers only) dollar amount.',
			],
			'gte' => [
				'rule' => ['comparison', 'greater or equal', 0],
				'message' => 'You can request insurance between $0 (no insurance) and $5000.00.',
			],
			'lte' => [
				'rule' => ['comparison', 'less or equal', 5000],
				'message' => 'You can request insurance between $0 (no insurance) and $5000.00.',
			],
		],
		'mail_class' => [
			'rule' => ['inList', ['priority', 'parcel']],
			'required' => 'create',
			'message' => 'Mail class must be priority or parcel.',
		],
		'order_add_date' => [
			'rule' => ['datetime', 'ymd'],
			'required' => 'create',
			'message' => 'Date must not be empty.',
		],
		// 'insurance_fee' is deprecated and used by the legacy system only
	];

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = [
		'Customer' => [
			'className' => 'Customer',
			'foreignKey' => 'customers_id',
		],
		'Order' => [
			'className' => 'Order',
			'foreignKey' => 'orders_id',
		],
	];

	/**
	 * beforeValidate
	 *
	 * @param mixed $options Optional options
	 * @return bool True
	 */
	public function beforeValidate($options = []) {
		parent::beforeValidate($options);
		// Populate billing_id, order_add_date
		if (!$this->exists()) {
			$this->Customer->id = $this->data[$this->name]['customers_id'];
			$this->data[$this->name]['billing_id'] = $this->Customer->field('billing_id');
			$this->data[$this->name]['order_add_date'] = date_create()->format('Y-m-d H:i:s');
		}
		return true;
	}

	/**
	 * Returns all open custom package requests for a given user, optionally
	 * limited by an package status number.
	 *
	 * @param int $customerId The `customers_id`
	 * @param mixed $statusOnly A valid package status number or null
	 * @return mixed
	 */
	public function findOpen($customerId, $statusOnly = null) {
		$options = [
			'conditions' => [
				'customers_id' => $customerId,
				'orders_id' => 0
			],
			'order' => 'order_add_date DESC',
		];
		if ($statusOnly) {
			$options['conditions']['package_status'] = $statusOnly;
		}
		return $this->find('all', $options);
	}

	/**
	 * Updates a record with $orderId as long as `custom_orders_id` is present.
	 *
	 * @param array $data The request data containing a CustomPackageRequest array
	 * @param int $orderId The newly created order id
	 * @return mixed Array of saved data | false on failed save | bool true if nothing happened
	 */
	public function updateOrderId($data, $orderId) {
		unset($data['Order']);
		if (!empty($data['CustomPackageRequest']['custom_orders_id'])) {
			$data['CustomPackageRequest']['orders_id'] = $orderId;
			return $this->save($data, false, ['orders_id']);
		}
		return true;
	}

	/**
	 * Finds an order by $orderId and a matching CustomPackageRequest. If found,
	 * the CustomPackageRequest.orders_status value is updated to match the value
	 * of the found order.
	 *
	 * @param int $orderId An order id
	 * @return bool true on success or false on failure or no action taken
	 */
	public function updatePackageStatusToOrderStatus($orderId) {
		$options = [
			'conditions' => [
				'Order.orders_id' => $orderId,
			],
			'contain' => [
				'CustomPackageRequest' => [
					'fields' => [
						'custom_orders_id',
						'package_status',
					],
				],
			],
			'fields' => [
				'orders_status',
			],
		];
		$order = $this->Order->find('first', $options);
		if ($order && !is_null($order['CustomPackageRequest']['custom_orders_id'])) {
			$this->id = $order['CustomPackageRequest']['custom_orders_id'];
			return $this->saveField('package_status', $order['Order']['orders_status']);
		}
		return false;
	}

	/**
	 * Finds custom orders with `orders_id` set to 0 and searches for orders with
	 * matching `tracking_id`. If a match is found, the custom order `order_id` is
	 * updated with the matching order number.
	 *
	 * @param bool $dryRun True to update records, false to return results only
	 * @return array
	 */
	public function findMatchingRequests($dryRun = false) {
		$options = [
			'conditions' => [
				'orders_id' => 0,
			],
			'fields' => [
				'custom_orders_id',
				'tracking_id',
			],
		];
		$results = $this->find('list', $options);
		$output = [];
		if ($results) {
			foreach ($results as $id => $trackingId) {
				$options = [
					'conditions' => [
						'OR' => [
							'amazon_track_num' => $trackingId,
							'ups_track_num' => $trackingId,
							'usps_track_num' => $trackingId,
							'fedex_track_num' => $trackingId,
							'fedex_freight_track_num' => $trackingId,
							'dhl_track_num' => $trackingId,
						],
					],
					'fields' => [
						'orders_id',
					],
				];
				$order = $this->Order->find('first', $options);
				if ($order) {
					if (!$dryRun) {
						$this->id = $id;
						$this->saveField('orders_id', $order['Order']['orders_id']);
					}
					$output[$id] = $order['Order']['orders_id'];
				}
			}
		}
		return $output;
	}

	/**
	 * Finds custom orders with `orders_id` not set to 0 and searches for orders with
	 * matching `orders_id`. If a match is found, the custom order `package_status` is
	 * updated with the matching order's `orders_status`.
	 *
	 * @param bool $dryRun True to update records, false to return results only
	 * @return array
	 */
	public function findAndUpdateStatus($dryRun = false) {
		$options = [
			'fields' => [
				'orders_id',
				'package_status',
				'custom_orders_id',
			],
			'conditions' => [
				'CustomPackageRequest.orders_id NOT' => 0,
			],
			'contain' => [
				'Order' => [
					'fields' => [
						'orders_id',
						'orders_status',
					],
				],
			],
		];

		$results = $this->find('all', $options);
		$output = [];
		if ($results) {
			foreach ($results as $result) {
				if ($result['Order']['orders_status'] != $result['CustomPackageRequest']['package_status']) {
					if (!$dryRun) {
						$this->id = $result['CustomPackageRequest']['custom_orders_id'];
						$this->saveField('package_status', $result['Order']['orders_status']);
					}
					$output[$result['CustomPackageRequest']['custom_orders_id']] = $result['Order']['orders_status'];
				}
			}
		}
		return $output;
	}

	/**
	 * Returns true if there are no existing orders with the given tracking id.
	 *
	 * @param array $check The field being validated.
	 * @return bool
	 */
	public function trackingIdNotInOrder($check) {
		return !(bool)$this->Order->countByInboundTracking($check['tracking_id']);
	}

	/**
	 * Finds all custom package requests as efficiently as possible for the
	 * `orders_id` supplied in an $order array.
	 *
	 * @param array $orders An orders array containing at least `orders_id`
	 * @return array
	 */
	public function findAllForOrder($orders) {
		$options = [
			'conditions' => [
				'CustomPackageRequest.orders_id' => Hash::extract($orders, '{n}.Order.orders_id'),
			],
			'fields' => ['custom_orders_id', 'orders_id'],
			'recursive' => -1,
			'order' => false,
		];
		return $this->find('all', $options);
	}
}
