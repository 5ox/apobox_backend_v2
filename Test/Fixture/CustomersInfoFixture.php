<?php
/**
 * CustomersInfoFixture
 *
 */
class CustomersInfoFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'customers_info';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'customers_info_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'primary'),
		'customers_info_date_of_last_logon' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'customers_info_number_of_logons' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false),
		'customers_info_date_account_created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'customers_info_date_account_closed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'customers_info_date_account_last_modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'customers_info_source_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'global_product_notifications' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 1, 'unsigned' => false),
		'IP_signup' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'IP_lastlogon' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'IP_cc_update' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'IP_addressbook_update' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'customers_info_id', 'unique' => 1)
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
			'customers_info_id' => 1,
			'customers_info_date_of_last_logon' => '2015-11-06 15:10:02',
			'customers_info_number_of_logons' => 1,
			'customers_info_date_account_created' => '2015-11-06 15:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2015-11-06 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 2,
			'customers_info_date_of_last_logon' => '2015-12-06 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2015-12-06 15:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2015-11-06 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 3,
			'customers_info_date_of_last_logon' => '2015-10-06 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2015-12-07 15:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2015-11-06 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 4,
			'customers_info_date_of_last_logon' => '2014-10-06 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2014-06-26 14:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2015-11-06 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 5,
			'customers_info_date_of_last_logon' => '2014-10-05 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2014-06-26 15:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2015-11-06 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 7,
			'customers_info_date_of_last_logon' => '2014-10-05 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2014-06-26 15:10:02',
			'customers_info_date_account_closed' => '2016-06-26 15:10:02',
			'customers_info_date_account_last_modified' => '2016-06-26 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
		array(
			'customers_info_id' => 8,
			'customers_info_date_of_last_logon' => '2017-01-24 15:10:02',
			'customers_info_number_of_logons' => 2,
			'customers_info_date_account_created' => '2017-01-24 15:10:02',
			'customers_info_date_account_closed' => null,
			'customers_info_date_account_last_modified' => '2017-01-24 15:10:02',
			'customers_info_source_id' => 1,
			'global_product_notifications' => 1,
			'IP_signup' => '1.2.3.4',
			'IP_lastlogon' => '5.6.7.8',
			'IP_cc_update' => '9.8.7.6',
			'IP_addressbook_update' => '5.4.3.2'
		),
	);

}
