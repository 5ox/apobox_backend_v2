<?php
/**
 * Country
 */

App::uses('AppModel', 'Model');

/**
 * Country Model
 *
 * @property	Address	$Address
 * @property	ShippingAddress	$ShippingAddress
 */
class Country extends AppModel {

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'countries_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'countries_name';

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = array();

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
		'Zone' => array(
			'className' => 'Zone',
			'foreignKey' => 'zone_country_id',
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
	);

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * Finds all countries that have zones.
	 *
	 * @return void
	 */
	public function findWithZones() {
		$countryIds = $this->Zone->find('list', ['fields' => ['zone_country_id', 'zone_country_id']]);
		unset ($countryIds[250]);
		$options = [
			'conditions' => [
				$this->alias . '.countries_id' => $countryIds,
			],
			'order' => [
				$this->alias . '.countries_name' => 'asc',
			],
		];
		return $this->find('list', $options);
	}
}
