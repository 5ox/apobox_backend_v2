<?php
/**
 * Zone
 */

App::uses('AppModel', 'Model');

/**
 * Zone Model
 *
 * @property	Address	$Address
 * @property	ShippingAddress	$ShippingAddress
 */
class Zone extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'zone_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'zone_name';

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = [
		'Country' => [
			'className' => 'Country',
			'foreignKey' => 'zone_country_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
	];

	/**
	 * hasOne associations
	 *
	 * @var	array
	 */
	public $hasOne = array();

	/**
	 * hasMany associations
	 *
	 * @var	array
	 */
	public $hasMany = array(
		'Address' => array(
			'className' => 'Address',
			'foreignKey' => 'entry_zone_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		),
		'ShippingAddress' => array(
			'className' => 'ShippingAddress',
			'foreignKey' => 'entry_zone_id',
		),
	);

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * Finds all zones sorted under thier respective countries
	 *
	 * @return void
	 */
	public function findZonesWithCountries() {
		$options = [
			'contain' => [
				'Country' => [
					'order' => [
						'countries_name' => 'asc',
						'zone_name' => 'asc',
					],
				],
			],
			'fields' => [
				'Zone.zone_id',
				'Zone.zone_name',
				'Country.countries_name',
			],
		];
		$zones = $this->find('all', $options);

		return Hash::combine($zones, '{n}.Zone.zone_id', '{n}.Zone.zone_name', '{n}.Country.countries_name');
	}
}
