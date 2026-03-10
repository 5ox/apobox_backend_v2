<?php
/**
 * AddressFixture
 *
 */
class AddressFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'address_book';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
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
		'entry_country_id' => array('type' => 'integer', 'null' => false, 'default' => '', 'unsigned' => false),
		'entry_zone_id' => array('type' => 'integer', 'null' => false, 'default' => '', 'unsigned' => false),
		'entry_basename' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'address_book_id', 'unique' => 1)
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
			'address_book_id' => 1,
			'customers_id' => 1,
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Customer 1 Address 1 Company',
			'entry_firstname' => 'Lorem ipsum dolor sit amet',
			'entry_lastname' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => 'Lorem ipsum dolor sit amet',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_state' => 'Lorem ipsum dolor sit amet',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 2,
			'customers_id' => 2,
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_firstname' => 'Lorem ipsum dolor sit amet',
			'entry_lastname' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => '1st Address',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_state' => 'Lorem ipsum dolor sit amet',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 3,
			'customers_id' => 2,
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_firstname' => 'Lorem ipsum dolor sit amet',
			'entry_lastname' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => '2nd Address',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_state' => 'Lorem ipsum dolor sit amet',
			'entry_country_id' => 1,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 4,
			'customers_id' => 1,
			'entry_gender' => '2',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 7,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 5,
			'customers_id' => 1,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s Shipping Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 9,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 6,
			'customers_id' => 1,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s Emergency Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 9,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 7,
			'customers_id' => 1,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s 2nd Shipping Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 9,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 8,
			'customers_id' => 1,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s 2nd Emergency Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 9,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 9,
			'customers_id' => 1,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s 2nd Default Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'A City',
			'entry_state' => '',
			'entry_zone_id' => 52,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 10,
			'customers_id' => 7,
			'entry_gender' => '0',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => 'Customer 1s 2nd Default Address',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 9,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 11,
			'customers_id' => 4,
			'entry_gender' => '2',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 7,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'address_book_id' => 12,
			'customers_id' => 5,
			'entry_gender' => '2',
			'entry_company' => '',
			'entry_firstname' => 'First',
			'entry_lastname' => 'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_state' => '',
			'entry_zone_id' => 7,
			'entry_country_id' => 163,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		),
	);

}
