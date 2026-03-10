<?php
/**
 * PasswordRequestFixture
 *
 */
class PasswordRequestFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customer_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'NULL if record does not belong to customer'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'NULL if record does not belong to user'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
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
			'id' => '54e247cf-cfd0-4b18-b619-0fff9c363e35',
			'customer_id' => 1,
			'user_id' => 0,
			'created' => '2015-05-05 09:00:00'
		),
		array(
			'id' => '7549f1f8-2a97-48e2-95d3-de2a0eb1bfe3',
			'customer_id' => 0,
			'user_id' => 1,
			'created' => '2015-05-05 08:00:00'
		),
		array(
			'id' => '519d0a1b-dacf-4220-837b-d3f6854ced9a',
			'customer_id' => 1,
			'user_id' => 0,
			'created' => '2015-05-05 08:30:00'
		),
	);

}
