<?php
/**
 * SearchIndexFixture
 *
 */
class SearchIndexFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'association_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'model' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'association_key' => array('column' => array('association_key', 'model'), 'unique' => 0),
			'data' => array('column' => 'data', 'type' => 'fulltext')
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Data format for `data`:
	 * [billing_id]. [customers_firstname]. [customers_lastname]. [customers_email_address]
	 *
	 * Optional, appended to the end are AuthorizedName:
	 * [authorized_firstname]. [authorized_lastname]
	 *
	 * @var	array
	 */
	public $records = [
		[
			'id' => 1,
			'association_key' => '5',
			'model' => 'Customer',
			'data' => 'XU934. Invoice. Unique. test.user99@example.com',
			'created' => '2016-06-15 16:16:32',
			'modified' => '2016-06-15 16:16:32'
		],
		[
			'id' => 2,
			'association_key' => '2',
			'model' => 'Customer',
			'data' => 'IB1234. Incomplete. Billing. someone.else@example.com. George. Washington. Lorem. SetDefaults',
			'created' => '2016-06-15 16:16:32',
			'modified' => '2016-06-15 16:16:32'
		],
		[
			'id' => 3,
			'association_key' => '3',
			'model' => 'Customer',
			'data' => 'Empty Billing ID. Billing. someone.else@example.com',
			'created' => '2016-06-15 16:16:32',
			'modified' => '2016-06-15 16:16:32'
		],
		[
			'id' => 4,
			'association_key' => '7',
			'model' => 'Customer',
			'data' => 'ZZ1234. SetDefaults. Test. test.user1234@example.com',
			'created' => '2016-06-15 16:16:32',
			'modified' => '2016-06-15 16:16:32'
		],
		[
			'id' => 5,
			'association_key' => '1',
			'model' => 'Customer',
			'data' => 'Lorem. Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet. someone@example.com',
			'created' => '2016-08-10 16:16:32',
			'modified' => '2016-08-10 16:16:32'
		],
	];
}
