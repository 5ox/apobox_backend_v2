<?php
/**
 * CustomerReminderFixture
 *
 */
class CustomerReminderFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'customer_reminder_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'customers_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'orders_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'length' => 17, 'unsigned' => false),
		'reminder_type' => array('type' => 'string', 'null' => false, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'reminder_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'customer_reminder_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	/**
	 * Records
	 *
	 * @var	array
	 */
	public $records = array(
		array(
			'customer_reminder_id' => 1,
			'customers_id' => 1,
			'orders_id' => '4',
			'reminder_type' => 'awaiting_payment',
			'reminder_count' => 1,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'customer_reminder_id' => 2,
			'customers_id' => 1,
			'orders_id' => '99999',
			'reminder_type' => 'awaiting_payment',
			'reminder_count' => 1,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'customer_reminder_id' => 3,
			'customers_id' => 1,
			'orders_id' => '5',
			'reminder_type' => 'awaiting_payment',
			'reminder_count' => 2,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'customer_reminder_id' => 4,
			'customers_id' => 1,
			'orders_id' => '0',
			'reminder_type' => 'partial_signup',
			'reminder_count' => 2,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'customer_reminder_id' => 5,
			'customers_id' => 2,
			'orders_id' => '0',
			'reminder_type' => 'expired_card',
			'reminder_count' => 1,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'customer_reminder_id' => 6,
			'customers_id' => 7,
			'orders_id' => '10',
			'reminder_type' => 'awaiting_payment',
			'reminder_count' => 1,
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
	);

}
