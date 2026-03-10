<?php
/**
 * Reports
 */

App::uses('AppController', 'Controller');

/**
 * Reports Controller
 *
 */
class ReportsController extends AppController {

	/**
	 * Models
	 *
	 * @var	array
	 */
	public $uses = [
		'Customer',
		'CustomersInfo',
		'Order',
	];

	/**
	 * Report index.
	 *
	 * @return void
	 */
	public function manager_index() {
		$query = [
			'interval' => 'month',
			'from_date' => date_create('first day of 7 months ago')->format('Y-m-d 00:00:00'),
			'to_date' => date_create('last day of this month')->format('Y-m-d 23:59:59'),
			'orders_status' => '',
			'sort' => 'date_purchased',
			'direction' => 'asc'
		];

		$demoQuery = [
			'field' => 'ShippingAddress.entry_postcode',
			'from_date' => DateTime::createFromFormat('Y-m-d', '2006-11-29')->format('Y-m-d'),
			'to_date' => (new DateTime())->format('Y-m-d'),
			'limit' => 5,
		];

		$salesChartData = $this->Order->findOrderTotalsReport($query);
		$signupChartData = $this->CustomersInfo->findCustomerTotalsReport($query);
		$demoChartData = $this->Customer->findCustomerTotalsReport($demoQuery);
		$statusCounts = $this->Order->findTotalsPerStatus();
		$this->set(compact('salesChartData', 'signupChartData', 'demoChartData', 'statusCounts'));
	}
}
