<?php
/**
 * CustomersInfo
 */

App::uses('AppModel', 'Model');

/**
 * CustomersInfo Model
 *
 * @property	Customer	$Customer
 */
class CustomersInfo extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'customers_info';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'customers_info_id';

	/**
	 * Validation rules
	 *
	 * @var	array
	 */
	public $validate = [
		'customers_info_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'The field customers_info_id must be numeric.',
			],
		],
		'customers_info_date_of_last_logon' => [
			'datetime' => [
				'rule' => ['datetime'],
				'message' => 'The field customers_info_date_of_last_logon must be datetime.',
			],
		],
		'customers_info_number_of_logons' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'The field customers_info_number_of_logons must be numeric',
			],
		],
		'customers_info_date_account_created' => [
			'datetime' => [
				'rule' => ['datetime'],
				'message' => 'The field customers_info_date_account_created` must be datetime.',
			],
		],
		'customers_info_date_account_last_modified' => [
			'datetime' => [
				'rule' => ['datetime'],
				'message' => 'The field customers_info_date_account_last_modified must be datetime.',
			],
		],
		'customers_info_source_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'The field customers_info_source_id must be numeric.',
			],
		],
		'global_product_notifications' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'The field global_product_notifications must be numeric.',
			],
		],
		'IP_signup' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				'message' => 'The field IP_signup must not be blank.',
			],
		],
		'IP_lastlogon' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				'message' => 'The field IP_lastlogon must not be blank.',
			],
		],
		'IP_cc_update' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				'message' => 'The field IP_cc_update must not be blank.',
			],
		],
		'IP_addressbook_update' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				'message' => 'The field IP_addressbook_update must not be blank.',
			],
		],
	];

	/**
	 * belongsTo associations
	 *
	 * @var	array
	 */
	public $belongsTo = [
		'Customer' => [
			'className' => 'Customer',
			'foreignKey' => 'customers_info_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		],
	];

	/**
	 * Valid fields a report can be sorted by.
	 *
	 * @var array
	 */
	protected $_validSortFields = [
		'customers_info_date_account_created' => 'Date',
		'total' => 'Total',
	];

	/**
	 * Valid intervals available for customer totals report.
	 *
	 * @var array
	 */
	protected $_validIntervals = [
		'year' => 'Yearly',
		'month' => 'Monthly',
		'week' => 'Weekly',
		'day' => 'Daily',
	];

	/**
	 * Provides data for a report based on various conditions.
	 *
	 * @param array $data Request data for the report
	 * @return array $combinedResults The report results
	 */
	public function findCustomerTotalsReport($data) {
		$interval = (!empty($data['interval']) && array_key_exists($data['interval'], $this->_validIntervals) ?
			strtoupper($data['interval']) : 'DAY');

		$dateField = 'customers_info_date_account_created';

		$sortField = (!empty($data['sort']) && array_key_exists($data['sort'], $this->_validSortFields) ?
			$data['sort'] : $dateField);

		$sortDirection = (!empty($data['direction']) ? $data['direction'] : 'asc');

		$toDate = $this->deconstruct('customers_info_date_account_created', $data['to_date']);
		$fromDate = $this->deconstruct('customers_info_date_account_created', $data['from_date']);
		$fromYear = DateTime::createFromFormat('Y-m-d H:i:s', $fromDate)->format('Y');
		$toYear = DateTime::createFromFormat('Y-m-d H:i:s', $toDate)->format('Y');

		switch ($interval) {
			case 'DAY':
				$dateGroup = '%Y-%m-%d';
				$dateSuffix = ($toYear != $fromYear ? ' \'%y' : '');
				$dateString = '%b %e' . $dateSuffix;
				break;
			case 'WEEK':
				$dateGroup = '%Y-%v';
				$dateString = 'Week %v \'%y';
				break;
			case 'MONTH':
				$dateGroup = '%Y-%m';
				$dateString = '%b \'%y';
				break;
			case 'YEAR':
				$dateGroup = '%Y';
				$dateString = '%Y';
				break;
		}

		$options = [
			'conditions' => [
				$dateField . ' >=' => $fromDate,
				$dateField . ' <=' => $toDate,
			],
			'fields' => [
				'DATE_FORMAT(' . $dateField . ', "' . $dateGroup . '") AS date_group',
				'DATE_FORMAT(' . $dateField . ', "' . $dateString . '") AS date_string',
				'COUNT(DISTINCT(customers_info_id)) AS total',
			],
			'group' => [
				'date_group',
			],
			'order' => [
				$sortField => $sortDirection,
			],
			'contain' => [],
		];

		$results = $this->find('all', $options);

		$combinedResults = Hash::map($results, '{n}', function ($data) {
			return [
				'date' => $data[0]['date_string'],
				'date_string' => $data[0]['date_string'],
				'total' => $data[0]['total'],
			];
		});

		return $combinedResults;
	}

	/**
	 * Adds a record for customers without a customers_info record consisting of
	 * the customer id and the account created date.
	 *
	 * @param array $customers an array of [customers_id] => [created]
	 * @return array An array of customer ids that could not be saved or empty array if no errors
	 */
	public function updateAccountCreated($customers) {
		$result = [];
		$data = [];
		foreach ($customers as $id => $date) {
			$data['CustomersInfo'] = [
				'customers_info_id' => $id,
				'customers_info_date_account_created' => $date,
			];
			if (!$this->save($data, true)) {
				$result[] = $id;
			}
			$this->clear();
		}
		return $result;
	}
}
