<?php
/**
 * SearchIndexShell
 */
App::uses('AppShell', 'Console/Command');

/**
 * Class: SearchIndexShell
 *
 * @see AppShell
 */
class SearchIndexShell extends AppShell {

	/**
	 * Models to load.
	 *
	 * @var array
	 */
	public $uses = [
		'SearchIndex',
		'Customer',
	];

	/**
	 * getOptionParser
	 *
	 * Define command line options for automatic processing and enforcement.
	 *
	 * @codeCoverageIgnore Cake core
	 * @access	public
	 * @return	mixed
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description('Rebuilds search indexes');
		return $parser;
	}

	/**
	 * Rebuilds the customer search index
	 *
	 * @return void
	 */
	public function customer() {
		// @codeCoverageIgnoreStart
		if (!getenv('APP_ENV')) {
			ini_set('memory_limit', '256M');
		}
		// @codeCoverageIgnoreEnd
		$options = [
			'fields' => [
				'Customer.customers_id',
				'Customer.billing_id',
				'Customer.customers_firstname',
				'Customer.customers_lastname',
				'Customer.customers_email_address',
			],
			'contain' => [
				'AuthorizedName',
			],
		];
		$customers = $this->Customer->find('all', $options);
		foreach ($customers as $customer) {
			$id = $customer['Customer']['customers_id'];
			unset($customer['Customer']['customers_id']);
			$search = join('. ', $customer['Customer']);
			if (!empty($customer['AuthorizedName'])) {
				foreach ($customer['AuthorizedName'] as $authorizedName) {
					$search .= '. ';
					unset($authorizedName['authorized_names_id'], $authorizedName['customers_id']);
					$search .= join('. ', $authorizedName);
				}
			}
			$data['SearchIndex'] = [
				'association_key' => $id,
				'model' => 'Customer',
				'data' => $search,
			];
			if ($exists = $this->SearchIndex->findByAssociationKey($id, 'id')) {
				$data['SearchIndex']['id'] = $exists['SearchIndex']['id'];
			}
			$this->SearchIndex->save($data);
			$this->SearchIndex->clear();
		}
		$this->_out('Customer search index rebuilt.', 'info');
	}
}
