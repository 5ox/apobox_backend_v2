<?php
/**
 * TrackingFixture
 *
 */
class TrackingFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'tracking';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'tracking_id' => array('type' => 'string', 'null' => false, 'length' => 40, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'warehouse' => array('type' => 'string', 'null' => false, 'default' => 'Bancroft', 'length' => 30, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'timestamp' => array('type' => 'timestamp', 'null' => false, 'default' => null),
		'comments' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'shipped' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 5, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'tracking_id', 'unique' => 1)
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
			'tracking_id' => '123456789',
			'warehouse' => 'IN',
			'timestamp' => '2014-12-09 18:52:15',
			'comments' => '',
			'shipped' => '0',
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
		array(
			'tracking_id' => '987654321',
			'warehouse' => 'IN',
			'timestamp' => 1431360141,
			'timestamp' => '2015-11-08 18:52:15',
			'comments' => '',
			'shipped' => '0'
		),
		array(
			'tracking_id' => '123123123',
			'warehouse' => 'IN',
			'timestamp' => '2015-12-08 17:00:00',
			'comments' => '',
			'shipped' => '0',
			'created' => '2015-12-09 15:53:39',
			'modified' => '2015-12-09 15:53:39'
		),
	);

}
