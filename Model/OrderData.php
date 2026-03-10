<?php
/**
 * OrderData
 */

App::uses('AppModel', 'Model');

/**
 * OrderData Model
 *
 * @property	Orders	$Orders
 */
class OrderData extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'orders_data';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'orders_data_id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = [
		'orders_data_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'orders_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'data_type' => [
			'notBlank' => [
				'rule' => ['notBlank'],
			],
		],
		'data' => [
			'notBlank' => [
				'rule' => ['notBlank'],
			],
		],
	];

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = [
		'Orders' => [
			'className' => 'Orders',
			'foreignKey' => 'orders_id',
		],
	];

	/**
	 * Generic method to save data to the `orders_data` table. Currently used
	 * only to store FedEx ZPL label data using $dataType `fedex-zpl`.
	 *
	 * @param int $orderId An order id
	 * @param string $dataType The type of data to save
	 * @param mixed $data The data to save
	 * @return bool True if the data was saved, false on failure
	 */
	public function saveOrderData($orderId, $dataType, $data) {
		$record['OrderData'] = [
			'orders_id' => $orderId,
			'data_type' => $dataType,
			'data' => $data,
		];
		return (bool)$this->save($record);
	}

	/**
	 * Fetches OrderData `data` by order ID and data type.
	 *
	 * @param int $orderId An order id
	 * @param string $dataType The type of data to fetch
	 * @return mixed The data if it exists or null
	 */
	public function fetchOrderData($orderId, $dataType) {
		$options = [
			'conditions' => [
				'orders_id' => $orderId,
				'data_type' => $dataType,
			],
			'fields' => [
				'data',
			],
		];
		$record = $this->find('first', $options);
		return Hash::get($record, 'OrderData.data');
	}

	/**
	 * Checks if an existing record matched by order ID and data type exist.
	 *
	 * @param int $orderId An order id
	 * @param string $dataType The type of data to check
	 * @return bool True if a record exists
	 */
	public function checkOrderData($orderId, $dataType) {
		$options = [
			'conditions' => [
				'orders_id' => $orderId,
				'data_type' => $dataType,
			],
		];
		return (bool)$this->find('count', $options);
	}

	/**
	 * Removes all records matched by order ID and data type.
	 *
	 * @param int $orderId An order id
	 * @param string $dataType The type of data to check
	 * @return bool True no matter what
	 */
	public function clearOrderData($orderId, $dataType) {
		$options = [
			'OrderData.orders_id' => $orderId,
			'OrderData.data_type' => $dataType,
		];
		return $this->deleteAll($options, false);
	}
}
