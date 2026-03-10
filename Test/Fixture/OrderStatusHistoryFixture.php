<?php
/**
 * OrderStatusHistoryFixture
 *
 */
class OrderStatusHistoryFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'orders_status_history';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'orders_status_history_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 17, 'unsigned' => false, 'key' => 'primary'),
		'orders_id' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 17, 'unsigned' => true),
		'orders_status_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false),
		'date_added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'customer_notified' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 1, 'unsigned' => false),
		'comments' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'orders_status_history_id', 'unique' => 1)
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
			'orders_status_history_id' => 1,
			'orders_id' => '1',
			'orders_status_id' => 1,
			'date_added' => '2015-01-21 01:45:00',
			'customer_notified' => 1,
			'comments' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.'
		),
		array(
			'orders_status_history_id' => 2,
			'orders_id' => '1',
			'orders_status_id' => 1,
			'date_added' => '2015-01-22 15:22:00',
			'customer_notified' => 1,
			'comments' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.'
		),
		array(
			'orders_status_history_id' => 3,
			'orders_id' => '7',
			'orders_status_id' => 1,
			'date_added' => '2015-01-21 01:45:00',
			'customer_notified' => 1,
			'comments' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.'
		),
	);

}
