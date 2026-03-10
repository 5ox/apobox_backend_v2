<?php
App::uses('AppModel', 'Model');
/**
 * AffiliateLink Model
 *
 */
class AffiliateLink extends AppModel {

	/**
	 * Display field
	 *
	 * @var string
	 */
	public $displayField = 'title';

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
		'title' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Please provide a link title',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'url' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Please provide a link',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'url' => array(
				'rule' => array('url'),
				'message' => 'Please provide the link as a valid url',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'enabled' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
