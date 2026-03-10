<?php
/**
 * OrderStatusFixture
 *
 */
class OrderStatusFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'orders_status';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'orders_status_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'primary'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false, 'key' => 'primary'),
		'orders_status_name' => array('type' => 'string', 'null' => false, 'length' => 32, 'key' => 'index', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => array('orders_status_id', 'language_id'), 'unique' => 1),
			'idx_orders_status_name' => array('column' => 'orders_status_name', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Records
	 *
	 * @var	array
	 */
	public $records = array(
		array(
			'orders_status_id' => 1,
			'language_id' => 1,
			'orders_status_name' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'orders_status_id' => 2,
			'language_id' => 1,
			'orders_status_name' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'orders_status_id' => 3,
			'language_id' => 1,
			'orders_status_name' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'orders_status_id' => 4,
			'language_id' => 1,
			'orders_status_name' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'orders_status_id' => 5,
			'language_id' => 1,
			'orders_status_name' => 'Lorem ipsum dolor sit amet'
		),
	);

}
