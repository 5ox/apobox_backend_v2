<?php 
class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $address_book = array(
		'address_book_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'customers_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'entry_gender' => array('type' => 'string', 'null' => false, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_company' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_firstname' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_lastname' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_street_address' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_suburb' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_postcode' => array('type' => 'string', 'null' => false, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_city' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_state' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_country_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'entry_zone_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'entry_basename' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'address_book_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $customers = array(
		'customers_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'billing_id' => array('type' => 'string', 'null' => false, 'length' => 8, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_gender' => array('type' => 'string', 'null' => false, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_firstname' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_lastname' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_dob' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'customers_email_address' => array('type' => 'string', 'null' => false, 'length' => 96, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_default_address_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'customers_shipping_address_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'customers_emergency_address_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'customers_telephone' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_fax' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_password' => array('type' => 'string', 'null' => false, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_newsletter' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_referral_id' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_referral_points' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'cc_firstname' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_lastname' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_number' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_number_encrypted' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_expires_month' => array('type' => 'string', 'null' => false, 'length' => 2, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_expires_year' => array('type' => 'string', 'null' => false, 'length' => 2, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cc_cvv' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'insurance_amount' => array('type' => 'decimal', 'null' => false, 'default' => '50.00', 'length' => '15,2', 'unsigned' => false),
		'insurance_fee' => array('type' => 'decimal', 'null' => false, 'default' => '1.65', 'length' => '15,2', 'unsigned' => false),
		'backup_email_address' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'customers_referral_referred' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'referral_status' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 1, 'unsigned' => false),
		'default_postal_type' => array('type' => 'string', 'null' => false, 'default' => 'apobox_direct', 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'billing_type' => array('type' => 'string', 'null' => false, 'default' => 'cc', 'length' => 15, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'invoicing_authorized' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'editable_max_amount' => array('type' => 'decimal', 'null' => true, 'default' => null, 'length' => '15,2', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'customers_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

}
