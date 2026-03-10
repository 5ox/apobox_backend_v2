<?php
App::uses('AppSerializer', 'Serializer');

class CustomerSerializer extends Serializer {

    public $required = array(
			'customers_id',
			'billing_id',
			'customers_firstname',
			'customers_lastname',
			'customers_email_address',
			'customers_default_address_id',
			'customers_shipping_address_id',
			'customers_emergency_address_id',
			'customers_telephone',
			'customers_fax',
			'customers_newsletter',
			'customers_referral_id',
			'customers_referral_points',
			'insurance_amount',
			'insurance_fee',
			'backup_email_address',
			'customers_referral_referred',
			'referral_status',
			'default_postal_type',
			'billing_type',
			'editable_max_amount',
		);

		public $optional = array(
			'id',
		);

		public function afterSerialize($serializedData, $unserializedData) {
			if (!empty($serializedData['customer'])) {
				$serializedData['customer'] = array('id' => $serializedData['customer']['customers_id']) + $serializedData['customer'];
			}
			return $serializedData;
		}
}
