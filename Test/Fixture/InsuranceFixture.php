<?php
/**
 * InsuranceFixture
 *
 */
class InsuranceFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'insurance';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'insurance_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'amount_from' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '15,2', 'unsigned' => false),
		'amount_to' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '15,2', 'unsigned' => false),
		'insurance_fee' => array('type' => 'decimal', 'null' => false, 'default' => '0.00', 'length' => '15,2', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'insurance_id', 'unique' => 1)
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
			'insurance_id' => 1,
			'amount_from' => 0.01,
			'amount_to' => 50.00,
			'insurance_fee' => 1.75
		),
		array(
			'insurance_id' => 2,
			'amount_from' => 50.01,
			'amount_to' => 100.00,
			'insurance_fee' => 2.25
		),
		array(
			'insurance_id' => 3,
			'amount_from' => 100.01,
			'amount_to' => 200.00,
			'insurance_fee' => 2.75
		),
		array(
			'insurance_id' => 4,
			'amount_from' => 200.01,
			'amount_to' => 300.00,
			'insurance_fee' => 4.70
		),
		array(
			'insurance_id' => 5,
			'amount_from' => 300.01,
			'amount_to' => 400.00,
			'insurance_fee' => 5.70
		),
		array(
			'insurance_id' => 6,
			'amount_from' => 400.01,
			'amount_to' => 500.00,
			'insurance_fee' => 6.70
		),
		array(
			'insurance_id' => 7,
			'amount_from' => 500.01,
			'amount_to' => 600.00,
			'insurance_fee' => 7.70
		),
		array(
			'insurance_id' => 8,
			'amount_from' => 600.01,
			'amount_to' => 700.00,
			'insurance_fee' => 8.70
		),
		array(
			'insurance_id' => 9,
			'amount_from' => 700.01,
			'amount_to' => 800.00,
			'insurance_fee' => 9.70
		),
		array(
			'insurance_id' => 10,
			'amount_from' => 800.01,
			'amount_to' => 900.00,
			'insurance_fee' => 10.70
		),
		array(
			'insurance_id' => 11,
			'amount_from' => 900.01,
			'amount_to' => 1000.00,
			'insurance_fee' => 11.70
		),
	);

}
