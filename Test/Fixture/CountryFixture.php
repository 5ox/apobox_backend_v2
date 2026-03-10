<?php
/**
 * CountryFixture
 *
 */
class CountryFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'countries_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'countries_name' => array('type' => 'string', 'null' => false, 'length' => 64, 'key' => 'index', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'countries_iso_code_2' => array('type' => 'string', 'null' => false, 'length' => 2, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'countries_iso_code_3' => array('type' => 'string', 'null' => false, 'length' => 3, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'address_format_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'countries_id', 'unique' => 1),
			'IDX_COUNTRIES_NAME' => array('column' => 'countries_name', 'unique' => 0)
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
			'countries_id' => '223',
			'countries_name' => 'United States',
			'countries_iso_code_2' => 'US',
			'countries_iso_code_3' => 'USA',
			'address_format_id' => '2'
		),
		array(
			'countries_id' => '2',
			'countries_name' => 'Albania',
			'countries_iso_code_2' => 'AL',
			'countries_iso_code_3' => 'ALB',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '3',
			'countries_name' => 'Algeria',
			'countries_iso_code_2' => 'DZ',
			'countries_iso_code_3' => 'DZA',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '4',
			'countries_name' => 'American Samoa',
			'countries_iso_code_2' => 'AS',
			'countries_iso_code_3' => 'ASM',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '5',
			'countries_name' => 'Andorra',
			'countries_iso_code_2' => 'AD',
			'countries_iso_code_3' => 'AND',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '6',
			'countries_name' => 'Angola',
			'countries_iso_code_2' => 'AO',
			'countries_iso_code_3' => 'AGO',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '7',
			'countries_name' => 'Anguilla',
			'countries_iso_code_2' => 'AI',
			'countries_iso_code_3' => 'AIA',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '8',
			'countries_name' => 'Antarctica',
			'countries_iso_code_2' => 'AQ',
			'countries_iso_code_3' => 'ATA',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '9',
			'countries_name' => 'Antigua and Barbuda',
			'countries_iso_code_2' => 'AG',
			'countries_iso_code_3' => 'ATG',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '10',
			'countries_name' => 'Argentina',
			'countries_iso_code_2' => 'AR',
			'countries_iso_code_3' => 'ARG',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '163',
			'countries_name' => 'Costa Rica',
			'countries_iso_code_2' => 'CR',
			'countries_iso_code_3' => 'CRI',
			'address_format_id' => '1'
		),
		array(
			'countries_id' => '38',
			'countries_name' => 'Canada',
			'countries_iso_code_2' => 'CA',
			'countries_iso_code_3' => 'CAN',
			'address_format_id' => '1'
		),
	);

}
