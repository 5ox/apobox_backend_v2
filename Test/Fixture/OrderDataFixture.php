<?php
/**
 * OrderDataFixture
 *
 */
class OrderDataFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'orders_data';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'orders_data_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'orders_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'length' => 17, 'unsigned' => true),
		'data_type' => array('type' => 'string', 'null' => false, 'length' => 30, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'orders_data_id', 'unique' => 1)
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
			'orders_data_id' => 1,
			'orders_id' => '2',
			'data_type' => 'fedex-zpl',
			'data' => 'raw zpl data',
			'created' => '2016-05-10 15:58:57',
			'modified' => '2016-05-10 15:58:57'
		),
	);

}
