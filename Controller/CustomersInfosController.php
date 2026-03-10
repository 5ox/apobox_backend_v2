<?php
/**
 * CustomersInfos
 */

App::uses('AppController', 'Controller');

/**
 * CustomersInfos Controller
 *
 * @property	CustomersInfo	$CustomersInfo
 */
class CustomersInfosController extends AppController {

	/**
	 * Report for new customer totals.
	 *
	 * @return void
	 */
	public function manager_report() {
		$this->request->allowMethod('get', 'post');

		$validIntervals = $this->CustomersInfo->_validIntervals;
		$validSortFields = $this->CustomersInfo->_validSortFields;
		$this->set(compact(
			'validIntervals',
			'validSortFields'
		));

		$data = !empty($this->request->data) ? $this->request->data : $this->request->query;
		if (!empty($data)) {
			$this->request->data = $data;
			$interval = !empty($data['interval']) ? $data['interval'] : 'day';
			$results = $this->CustomersInfo->findCustomerTotalsReport($data);
			$this->set(compact(
				'results',
				'interval'
			));
		}
	}
}
