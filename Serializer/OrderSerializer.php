<?php
App::uses('AppSerializer', 'Serializer');

/**
 * Class: OrderSerializer
 *
 * @see Serializer
 */
class OrderSerializer extends Serializer {

	/**
	 * required
	 *
	 * @var array
	 */
	public $required = array(
		'orders_id',
		'customers_id',
		'customers_name',
		'customers_company',
		'customers_street_address',
		'customers_suburb',
		'customers_city',
		'customers_postcode',
		'customers_state',
		'customers_country',
		'customers_telephone',
		'customers_email_address',
		'customers_address_format_id',
		'delivery_name',
		'delivery_company',
		'delivery_street_address',
		'delivery_suburb',
		'delivery_city',
		'delivery_postcode',
		'delivery_state',
		'delivery_country',
		'delivery_address_format_id',
		'billing_name',
		'billing_company',
		'billing_street_address',
		'billing_suburb',
		'billing_city',
		'billing_postcode',
		'billing_state',
		'billing_country',
		'billing_address_format_id',
		'payment_method',
		'comments',
		'last_modified',
		'date_purchased',
		'orders_status',
		'orders_date_finished',
		'amazon_track_num',
		'ups_track_num',
		'usps_track_num',
		'usps_track_num_in',
		'fedex_track_num',
		'fedex_freight_track_num',
		'dhl_track_num',
		'currency',
		'currency_value',
		'shipping_tax',
		'billing_status',
		'qbi_imported',
		'width',
		'length',
		'depth',
		'weight_oz',
		'mail_class',
		'package_type',
		'NonMachinable',
		'OversizeRate',
		'BalloonRate',
		'insurance_coverage',
		'postage_id',
		'trans_id',
		'moved_to_invoice',
	);

	/**
	 * afterSerialize
	 *
	 * @param array $serializedData
	 * @param array $unserializedData
	 * @return array The serialized data
	 */
	public function afterSerialize($serializedData, $unserializedData) {
		if (!empty($serializedData['order'])) {
			$serializedData['data'] = array(
				'type' => 'orders',
				'id' => $serializedData['order']['orders_id'],
				'attributes' => $serializedData['order']
			);
			unset($serializedData['order']);
		}
		return $serializedData;
	}
}
