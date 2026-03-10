<?php
/**
 * OrderTotalFixture
 *
 */
class OrderTotalFixture extends CakeTestFixture {

	/**
	 * Table name
	 *
	 * @var	string
	 */
	public $table = 'orders_total';

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'orders_total_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => true, 'key' => 'primary'),
		'orders_id' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 17, 'unsigned' => true, 'key' => 'index'),
		'title' => array('type' => 'string', 'null' => false, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'text' => array('type' => 'string', 'null' => false, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'value' => array('type' => 'decimal', 'null' => false, 'default' => '0.0000', 'length' => '15,4', 'unsigned' => false),
		'class' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'sort_order' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'orders_total_id', 'unique' => 1),
			'idx_orders_total_orders_id' => array('column' => 'orders_id', 'unique' => 0)
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
			'orders_total_id' => 1,
			'orders_id' => 1,
			'title' => 'Postage :',
			'text' => '$77.90',
			'value' => 77.9000,
			'class' => 'ot_shipping',
			'sort_order' => 1,
		),
		array(
			'orders_total_id' => 2,
			'orders_id' => 1,
			'title' => 'Storage Fees:',
			'text' => '$23.00',
			'value' => 23.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 3,
			'orders_id' => 1,
			'title' => 'Insurance :',
			'text' => '$1.75',
			'value' => 1.7500,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 41231231,
			'orders_id' => 1,
			'title' => 'APO Box Fee :',
			'text' => '$10.95',
			'value' => 10.9500,
			'class' => 'ot_fee',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 4,
			'orders_id' => 1,
			'title' => 'Subtotal :',
			'text' => '',
			'value' => 113.6000,
			'class' => 'ot_subtotal',
			'sort_order' => 5
		),
		array(
			'orders_total_id' => 5,
			'orders_id' => 1,
			'title' => 'Total :',
			'text' => '<b>$112.60</b>',
			'value' => 113.6000,
			'class' => 'ot_total',
			'sort_order' => 6
		),
		array(
			'orders_total_id' => 6,
			'orders_id' => 2,
			'title' => 'Postage :',
			'text' => '$20.00',
			'value' => 20.0000,
			'class' => 'ot_shipping',
			'sort_order' => 1
		),
		array(
			'orders_total_id' => 7,
			'orders_id' => 2,
			'title' => 'Storage Fees:',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 8,
			'orders_id' => 2,
			'title' => 'Insurance :',
			'text' => '$1.75',
			'value' => 1.7500,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 9,
			'orders_id' => 2,
			'title' => 'Subtotal :',
			'text' => '$9.95',
			'value' => 9.9500,
			'class' => 'ot_subtotal',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 10,
			'orders_id' => 2,
			'title' => 'Total :',
			'text' => '<b>$112.60</b>',
			'value' => 112.6000,
			'class' => 'ot_total',
			'sort_order' => 5
		),
		array(
			'orders_total_id' => 11,
			'orders_id' => 7,
			'title' => 'Postage :',
			'text' => '$77.90',
			'value' => 77.9000,
			'class' => 'ot_shipping',
			'sort_order' => 1
		),
		array(
			'orders_total_id' => 12,
			'orders_id' => 7,
			'title' => 'Storage Fees:',
			'text' => '$23.00',
			'value' => 23.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 13,
			'orders_id' => 7,
			'title' => 'Insurance :',
			'text' => '$1.75',
			'value' => 1.7500,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 14,
			'orders_id' => 7,
			'title' => 'APO Box Fee :',
			'text' => '$9.95',
			'value' => 9.9500,
			'class' => 'ot_fee',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 15,
			'orders_id' => 7,
			'title' => 'Subtotal :',
			'text' => '',
			'value' => 112.6000,
			'class' => 'ot_subtotal',
			'sort_order' => 5
		),
		array(
			'orders_total_id' => 16,
			'orders_id' => 7,
			'title' => 'Total :',
			'text' => '<b>$112.60</b>',
			'value' => 112.6000,
			'class' => 'ot_total',
			'sort_order' => 6
		),
		array(
			'orders_total_id' => 2222,
			'orders_id' => 1,
			'title' => 'Storage Fees:',
			'text' => '$23.00',
			'value' => 23.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 3333,
			'orders_id' => 1,
			'title' => 'Custom 1 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_1',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 4444,
			'orders_id' => 1,
			'title' => 'Custom 2 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_2',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 17,
			'orders_id' => 8,
			'title' => 'Postage :',
			'text' => '$10.00',
			'value' => 10.0000,
			'class' => 'ot_shipping',
			'sort_order' => 1,
		),
		array(
			'orders_total_id' => 18,
			'orders_id' => 8,
			'title' => 'Storage Fees:',
			'text' => '$20.00',
			'value' => 20.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 19,
			'orders_id' => 8,
			'title' => 'Insurance :',
			'text' => '$30.00',
			'value' => 30.0000,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 20,
			'orders_id' => 8,
			'title' => 'APO Box Fee :',
			'text' => '$40.00',
			'value' => 40.0000,
			'class' => 'ot_fee',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 21,
			'orders_id' => 8,
			'title' => 'Subtotal :',
			'text' => '$100.00',
			'value' => 100.0000,
			'class' => 'ot_subtotal',
			'sort_order' => 5
		),
		array(
			'orders_total_id' => 22,
			'orders_id' => 8,
			'title' => 'Total :',
			'text' => '<b>$100.00</b>',
			'value' => 100.0000,
			'class' => 'ot_total',
			'sort_order' => 6
		),
		array(
			'orders_total_id' => 23,
			'orders_id' => 8,
			'title' => 'Custom 1 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_1',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 24,
			'orders_id' => 8,
			'title' => 'Custom 2 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_2',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 25,
			'orders_id' => 9,
			'title' => 'Postage :',
			'text' => '$10.00',
			'value' => 10.0000,
			'class' => 'ot_shipping',
			'sort_order' => 1,
		),
		array(
			'orders_total_id' => 26,
			'orders_id' => 9,
			'title' => 'Storage Fees:',
			'text' => '$20.00',
			'value' => 20.0000,
			'class' => 'ot_custom',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 27,
			'orders_id' => 9,
			'title' => 'Insurance :',
			'text' => '$30.00',
			'value' => 30.0000,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 28,
			'orders_id' => 9,
			'title' => 'APO Box Fee :',
			'text' => '$40.00',
			'value' => 40.0000,
			'class' => 'ot_fee',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 29,
			'orders_id' => 9,
			'title' => 'Subtotal :',
			'text' => '$100.00',
			'value' => 100.0000,
			'class' => 'ot_subtotal',
			'sort_order' => 5
		),
		array(
			'orders_total_id' => 30,
			'orders_id' => 9,
			'title' => 'Total :',
			'text' => '<b>$100.00</b>',
			'value' => 100.0000,
			'class' => 'ot_total',
			'sort_order' => 6
		),
		array(
			'orders_total_id' => 31,
			'orders_id' => 9,
			'title' => 'Custom 1 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_1',
			'sort_order' => 2
		),
		array(
			'orders_total_id' => 32,
			'orders_id' => 9,
			'title' => 'Custom 2 :',
			'text' => '$0.00',
			'value' => 0.0000,
			'class' => 'ot_custom_2',
			'sort_order' => 2
		),

		// The following represent an "old" order missing the following rows and a 
		// different sort order:
		// * ot_custom
		// * ot_custom_1
		// * ot_custom_2
		array(
			'orders_total_id' => 33,
			'orders_id' => 5,
			'title' => 'USPS Postage :',
			'text' => '$10.00',
			'value' => 10.0000,
			'class' => 'ot_shipping',
			'sort_order' => 2,
		),
		array(
			'orders_total_id' => 34,
			'orders_id' => 5,
			'title' => 'Insurance :',
			'text' => '$1.75',
			'value' => 1.7500,
			'class' => 'ot_insurance',
			'sort_order' => 3
		),
		array(
			'orders_total_id' => 35,
			'orders_id' => 5,
			'title' => 'Subtotal :',
			'text' => '$11.75',
			'value' => 11.7500,
			'class' => 'ot_subtotal',
			'sort_order' => 4
		),
		array(
			'orders_total_id' => 36,
			'orders_id' => 5,
			'title' => 'Total :',
			'text' => '<b>$11.75</b>',
			'value' => 11.7500,
			'class' => 'ot_total',
			'sort_order' => 6
		),
		array(
			'orders_total_id' => 37,
			'orders_id' => 5,
			'title' => 'APO Box Fee :',
			'text' => '$9.95',
			'value' => 9.9500,
			'class' => 'ot_fee',
			'sort_order' => 4
		),
	);
}
