<?php
/**
 * CustomerReminder
 */

App::uses('AppModel', 'Model');

/**
 * CustomerReminder Model
 *
 * @property	Customers	$Customers
 * @property	Orders	$Orders
 */
class CustomerReminder extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'customer_reminder_id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = array(
		'customer_reminder_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'customers_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'orders_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'reminder_type' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'reminder_count' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array(
		'Customers' => array(
			'className' => 'Customers',
			'foreignKey' => 'customers_id',
		),
		'Orders' => array(
			'className' => 'Orders',
			'foreignKey' => 'orders_id',
		),
	);

	/**
	 * An array of valid `reminder_type` that need to be present when saving or
	 * incrementing a reminder record.
	 *
	 * @var array
	 */
	protected $reminderTypes = array(
		'awaiting_payment',
		'partial_signup',
		'expired_card',
	);

	/**
	 * Checks for a valid `reminder_type` and either adds a new record if an
	 * existing one isn't present or updates the existing record's `reminder_count`
	 * by `1`.
	 *
	 * @param array $record An order or customer record containing CustomerReminder
	 * and a `customers_id`.
	 * @param mixed $type The type of reminder
	 * @return bool true on success, false on failure
	 */
	public function incrementReminder($record, $type) {
		$model = key($record);
		if (!in_array($type, $this->reminderTypes) || $model == null || !array_key_exists('customers_id', $record[$model])) {
			return false;
		}
		if (empty($record['CustomerReminder'])) {
			$data['CustomerReminder'] = array(
				'customers_id' => $record[$model]['customers_id'],
				'orders_id' => 0,
				'reminder_type' => $type,
				'reminder_count' => 1,
			);
			if ($model == 'Order') {
				$data['CustomerReminder']['orders_id'] = $record[$model]['orders_id'];
			}
		} else {
			$data['CustomerReminder'] = array(
				'customer_reminder_id' => $record['CustomerReminder'][0]['customer_reminder_id'],
				'reminder_count' => $record['CustomerReminder'][0]['reminder_count'] + 1,
			);
		}
		if ($this->save($data)) {
			$this->clear();
			return true;
		}
		return false;
	}

	/**
	 * Deletes all reminder records for the supplied order $id.
	 *
	 * @param init $id The order id
	 * @return bool
	 */
	public function purge($id = null) {
		$options = array(
			'CustomerReminder.orders_id' => $id,
		);
		return $this->deleteAll($options, false);
	}

	/**
	 * Clears reminders for the specified $id and $type. Currently configured
	 * to clear:
	 *
	 * * `expired_card` reminders when a customer updates their payment information.
	 * * `awaiting_payment` reminders when a charge is successful for an awaiting_payment order
	 *
	 * @param int $id A customer or order id
	 * @param string $type The type of reminder to flag for clearing
	 * @return bool
	 */
	public function clearRecord($id, $type) {
		switch ($type) {
			case 'payment_info':
				$options = array(
					'CustomerReminder.customers_id' => $id,
					'CustomerReminder.reminder_type' => 'expired_card',
				);
				break;
			case 'awaiting_payment':
				$options = array(
					'CustomerReminder.orders_id' => $id,
					'CustomerReminder.reminder_type' => $type,
				);
				break;
			default:
				$options = array();
		}
		if (!empty($options)) {
			return $this->deleteAll($options, false);
		}
		return false;
	}
}
