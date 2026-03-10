<?php
/**
 * AuthorizedNameFixture
 *
 */
class AuthorizedNameFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'authorized_names_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'customers_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'authorized_firstname' => array('type' => 'string', 'null' => false, 'length' => 20, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'authorized_lastname' => array('type' => 'string', 'null' => false, 'length' => 20, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'authorized_names_id', 'unique' => 1)
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
			'authorized_names_id' => 1,
			'customers_id' => 1,
			'authorized_firstname' => 'John',
			'authorized_lastname' => 'Doe'
		),
		array(
			'authorized_names_id' => 2,
			'customers_id' => 1,
			'authorized_firstname' => 'John',
			'authorized_lastname' => 'Smith'
		),
		array(
			'authorized_names_id' => 3,
			'customers_id' => 2,
			'authorized_firstname' => 'George',
			'authorized_lastname' => 'Washington'
		),
		array(
			'authorized_names_id' => 4,
			'customers_id' => 2,
			'authorized_firstname' => 'Lorem',
			'authorized_lastname' => 'SetDefaults'
		),
	);

}
