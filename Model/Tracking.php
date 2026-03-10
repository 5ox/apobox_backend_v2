<?php
/**
 * Tracking
 */

App::uses('AppModel', 'Model');

/**
 * Tracking Model
 *
 */
class Tracking extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'tracking';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'tracking_id';

	/**
	 * Display field
	 *
	 * @var	string
	 */
	public $displayField = 'tracking_id';

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
	public $hasMany = array();

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var	array
	 */
	public $hasAndBelongsToMany = array();

	/**
	 * order
	 *
	 * @var string
	 */
	public $order = 'created DESC';
}
