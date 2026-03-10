<?php
/**
 * AdminFixture
 *
 */
class AdminFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'role' => array('type' => 'string', 'null' => false, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'token' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
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
			'id' => 1,
			'email' => 'manager@example.com',
			'password' => 'password',
			'role' => 'manager',
			'token' => '',
			'created' => '2015-02-11 18:22:32',
			'modified' => '2015-02-11 18:22:32'
		),
		array(
			'id' => 2,
			'email' => 'employee@example.com',
			'password' => 'password',
			'role' => 'employee',
			'token' => '',
			'created' => '2015-02-11 18:22:32',
			'modified' => '2015-02-11 18:22:32'
		),
		array(
			'id' => 3,
			'email' => 'api@example.com',
			'password' => 'password',
			'role' => 'api',
			'token' => '1234567',
			'created' => '2015-08-11 18:22:32',
			'modified' => '2015-08-11 18:22:32'
		),
	);

}
