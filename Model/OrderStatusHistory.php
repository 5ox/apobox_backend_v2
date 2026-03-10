<?php
/**
 * OrderStatusHistory
 */

App::uses('AppModel', 'Model');

/**
 * OrderStatusHistory Model
 *
 * @property	Orders	$Orders
 */
class OrderStatusHistory extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'orders_status_history';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'orders_status_history_id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'orders_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Order id must be numeric.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'orders_status_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Order Status must be numeric.',
			),
		),
		'date_added' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				'message' => 'Date Added must be a valid date/time.',
			),
		),
		'customer_notified' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Customer Notified must be yes or no.',
			),
		),
	);

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array(
		'Orders' => array(
			'className' => 'Orders',
			'foreignKey' => 'orders_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		),
		'OrderStatus' => array(
			'className' => 'OrderStatus',
			'foreignKey' => 'orders_status_id',
		),
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
	 * beforeValidate
	 *
	 * @param array $options Optional options passed from Model::save()
	 * @return bool
	 */
	public function beforeValidate($options = array()) {
		parent::beforeValidate($options);
		$this->data[$this->alias]['date_added'] = date_format($this->getDatetime(), 'Y-m-d H:i:s');
		return true;
	}

	/**
	 * findCurrentStatusForOrderId
	 *
	 * @param mixed $id The optional OrderStatusHistory record id
	 * @param array $options The optional options
	 * @return mixed The result of the find
	 */
	public function findCurrentStatusForOrderId($id = null, $options = array()) {
		if ($id == null) {
			$id = $this->id;
		}

		$defaultOptions = array(
			'conditions' => array(
				$this->alias . '.orders_id' => $id,
			),
			'order' => array($this->alias . '.date_added' => 'DESC'),
		);

		$options = Hash::merge($defaultOptions, $options);

		return $this->find('first', $options);
	}

	/**
	 * findStatusesForOrderId
	 *
	 * @param mixed $id The optional OrderStatusHistory record id
	 * @param array $options The optional options
	 * @return mixed The result of the find
	 */
	public function findStatusesForOrderId($id = null, $options = array()) {
		if ($id == null) {
			$id = $this->id;
		}

		$defaultOptions = array(
			'conditions' => array(
				$this->alias . '.orders_id' => $id,
			),
			'order' => array($this->alias . '.date_added' => 'DESC'),
		);

		$options = Hash::merge($defaultOptions, $options);

		return $this->find('all', $options);
	}

	/**
	 * Sets and saves data specific to recording an order status history record
	 * when a charge fails. The `orders_status` should always be `2` (awaiting
	 * payment) if a charge has failed, so it's forced here.
	 *
	 * The `sleep()` delay is so this record is last as order status is set to
	 * awaiting payment before the charge is attempted. This entry should be at
	 * the top of the status history list for both customer and admin.
	 *
	 * @param array $order An order array
	 * @return bool The result of the `addRecord` method's save operation
	 */
	public function recordChargeFailed($order) {
		$data = [
			'status_history_comments' => 'charge failed, email sent',
			'notify_customer' => 1,
			'orders_status' => 2,
		];
		$order['Order'] = array_merge($order['Order'], $data);

		sleep(1);

		return $this->record($order);
	}

	/**
	 * Sets data and saves an order status history record.
	 *
	 * @param array $order An order array
	 * @return bool The result of the save operation
	 */
	public function record($order) {
		$this->create();
		$data = ['OrderStatusHistory' => []];
		$data['OrderStatusHistory']['orders_id'] = $order['Order']['orders_id'];
		if (!empty($order['Order']['orders_status'])) {
			$data['OrderStatusHistory']['orders_status_id'] = $order['Order']['orders_status'];
		}
		if (!empty($order['Order']['status_history_comments'])) {
			$data['OrderStatusHistory']['comments'] = $order['Order']['status_history_comments'];
		}
		if (isset($order['Order']['notify_customer']) && $order['Order']['notify_customer'] == 1) {
			$data['OrderStatusHistory']['customer_notified'] = true;
		}

		return $this->save($data);
	}
}
