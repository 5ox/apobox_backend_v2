<?php

App::uses('AppController', 'Controller');

/**
 * CustomPackageRequestsController
 *
 * @property	CustomPackageRequest $CustomPackageRequest
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class CustomPackageRequestsController extends AppController {

	/**
	 * components
	 *
	 * @var array
	 */
	public $components = array(
		'Paginator',
	);

	/**
	 * manager_index
	 *
	 * @return void
	 */
	public function manager_index() {
		$search = $this->request->query('q');
		$fromThePast = $this->request->query('from_the_past');
		if (!isset($fromThePast)) {
			$fromThePast = Configure::read('Search.date.default');
		}

		$showStatus = $this->request->query('showStatus');

		$this->Paginator->settings = array(
			'contain' => array('Customer', 'Order' => array('OrderStatus')),
			'order' => 'CustomPackageRequest.order_add_date DESC',
			'conditions' => $this->_getConditions($search, $fromThePast, $showStatus),
		);
		$this->set('requests', $this->paginate());
		$statusFilterOptions = $this->CustomPackageRequest->packageStatuses;
		$this->set(compact('fromThePast', 'search', 'statusFilterOptions', 'showStatus'));
	}

	/**
	 * Employee wrapper for manager_index
	 *
	 * @return void
	 */
	public function employee_index() {
		$this->manager_index();
		$this->render('manager_index');
	}

	/**
	 * _getConditions
	 *
	 * @param mixed $query The query
	 * @param string $timeframe Option to search by `order_add_date`
	 * @param string $showStatus Option to search by `package_status`
	 * @return array
	 */
	protected function _getConditions($query, $timeframe, $showStatus) {
		$conditions = array();

		$terms = (!empty($query) ? explode(' ', $query) : array());
		$fieldRegex = array(
			'CustomPackageRequest.billing_id LIKE' => '/^[a-z]{0,2}[\d]{0,4}/i',
			'CustomPackageRequest.orders_id LIKE' => '/^[\d]{0,15}/i',
			'CustomPackageRequest.tracking_id LIKE' => '/[a-z\d]+/i',
		);

		$i = 0;
		foreach ($terms as $term) {
			foreach ($fieldRegex as $field => $regex) {
				if (preg_match($regex, $term)) {
					$conditions[$i]['OR'][] = array($field => '%' . $term . '%');
				}
			}
			$i++;
		}

		if (!empty($timeframe)) {
			$conditions[0]['AND'] = array(
				'CustomPackageRequest.order_add_date >= ' => date_create($timeframe)->format('Y-m-d H:i:s')
			);
		}

		if (!empty($showStatus)) {
			$conditions[0]['AND'] = array(
				'CustomPackageRequest.package_status' => $showStatus
			);
		}

		return $conditions;
	}

	/**
	 * add method
	 *
	 * @return mixed
	 */
	public function add() {
		if ($this->request->is('post')) {
			// ensure users can only add custom package requests for themselves
			$this->request->data['CustomPackageRequest']['customers_id'] = $this->Auth->user('customers_id');
			$this->request->data['CustomPackageRequest']['package_status'] = 1;

			$this->CustomPackageRequest->create();
			if ($this->CustomPackageRequest->save($this->request->data)) {
				$this->Flash->set(__('Your custom package request has been created.'));
				return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
			} else {
				$this->Flash->set(__('The custom package request could not be saved. Please, try again.'));
			}
		}
		$allowedFields = $this->_setAllowedFields([]);
		$this->set(compact('allowedFields'));
	}

	/**
	 * Form for managers to add new custom package requests.
	 *
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_add() {
		if (!isset($this->request->params['customerId']) || !$customer = $this->CustomPackageRequest->Customer->findByCustomersId($this->request->params['customerId'])) {
			throw new NotFoundException('The customer for the package request was not found.');
		}

		if ($this->request->is('post')) {
			$this->CustomPackageRequest->create();
			if ($this->CustomPackageRequest->save($this->request->data)) {
				$this->Flash->set(__('The custom package request has been created.'));
				return $this->redirect(array('controller' => 'custom_package_requests', 'action' => 'index'));
			} else {
				$this->Flash->set(__('The custom package request could not be saved. Please, try again.'));
			}
		}

		$allowedFields = $this->_setAllowedFields([]);
		$this->set(compact('allowedFields', 'customer'));
		$this->set('packageStatuses', $this->CustomPackageRequest->packageStatuses);
		return $this->render('add');
	}

	/**
	 * Employee wrapper for manager_add
	 *
	 * @return void
	 */
	public function employee_add() {
		return $this->manager_add();
	}

	/**
	 * edit
	 *
	 * @param mixed $id The CustomPackageRequest id
	 * @param mixed $customerId The customer id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function edit($id = null, $customerId = null) {
		$this->request->allowMethod(array('get', 'put'));
		$this->CustomPackageRequest->id = $id;

		$request = $this->CustomPackageRequest->find('first', [
			'contain' => ['Customer'],
			'conditions' => ['custom_orders_id' => $id],
		]);
		if (empty($request) || !$this->_userIsAuthorized($request)) {
			throw new NotFoundException('The custom package request was not found.');
		}

		$allowedFields = $this->_setAllowedFields($request);
		$this->set(compact('allowedFields'));

		if ($this->request->is('put')) {
			if (!$this->CustomPackageRequest->save($this->request->data, true, $allowedFields)) {
				$this->Flash->set('Custom package request could not be updated.');
			} else {
				$this->Flash->set('Custom package request was successfully updated!');
				$redirect = array('controller' => 'customers', 'action' => 'account');
				if ($this->_userIsManager() || $this->_userIsEmployee()) {
					if ($customerId) {
						$redirect = array('controller' => 'orders', 'action' => 'add', $customerId);
					} else {
						$redirect = array('controller' => 'custom_package_requests', 'action' => 'index');
					}
				}

				return $this->redirect($redirect);
			}
		}

		if (empty($this->request->data)) {
			$this->request->data = $request;
		}
	}

	/**
	 * Return a list of fields that can be edited depending on various
	 * properties. Admins can edit more fields, requests assigned to orders
	 * only allow instructions to be changed by customers.
	 *
	 * @param array $request The request
	 * @return array
	 */
	protected function _setAllowedFields($request) {
		$allowedFields = array(
			'tracking_id',
			'package_repack',
			'insurance_coverage',
			'mail_class',
			'instructions',
			'insurance',
		);

		if ($this->_userIsManager()) {
			$allowedFields = array_merge($allowedFields, array(
				'package_status',
				'orders_id',
			));
			return $allowedFields;
		}

		if (!empty($request['CustomPackageRequest']['orders_id'])) {
			return $allowedFields = array(
				'instructions',
			);
		}

		return $allowedFields;
	}

	/**
	 * manager_edit
	 *
	 * @param mixed $id The CustomPackageRequest id
	 * @param mixed $customerId The customer id
	 * @return mixed
	 */
	public function manager_edit($id, $customerId = null) {
		$this->set('packageStatuses', $this->CustomPackageRequest->packageStatuses);
		$this->edit($id, $customerId);
		return $this->render('edit');
	}

	/**
	 * Employee wrapper for manager_edit
	 *
	 * @param mixed $id The CustomPackageRequest id
	 * @param mixed $customerId The customer id
	 * @return mixed
	 */
	public function employee_edit($id, $customerId = null) {
		return $this->manager_edit($id, $customerId);
	}

	/**
	 * delete
	 *
	 * @param mixed $id The CustomPackageRequest id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function delete($id = null) {
		$this->request->allowMethod(array('delete'));
		$this->CustomPackageRequest->id = $id;

		$request = $this->CustomPackageRequest->read();
		if (empty($request) || !$this->_userIsAuthorized($request)) {
			throw new NotFoundException('The custom package request was not found.');
		}

		$redirect = array('controller' => 'customers', 'action' => 'account');
		if ($this->_userIsManager()) {
			$redirect = array('controller' => 'custom_package_requests', 'action' => 'index');
		}

		if (!$this->CustomPackageRequest->trackingIdNotInOrder($request['CustomPackageRequest'])) {
			$this->Flash->set('The custom package request could not be deleted because it\'s associated with an order.');
			return $this->redirect($redirect);
		}

		if ($this->CustomPackageRequest->delete($id, false)) {
			$this->Flash->set('The custom package request was successfully deleted!');
		} else {
			$this->Flash->set('The custom package request could not be deleted.');
		}
		return $this->redirect($redirect);
	}

	/**
	 * manager_delete
	 *
	 * @param mixed $id The CustomPackageRequest id
	 * @return mixed
	 */
	public function manager_delete($id = null) {
		return $this->delete($id);
	}

	/**
	 * Returns true if logged in user is authorized
	 *
	 * @param mixed $request The request
	 * @return bool
	 */
	protected function _userIsAuthorized($request) {
		return ($this->_userIsOwner($request) || $this->_userIsManager() || $this->_userIsEmployee());
	}

	/**
	 * Returns true if provided request is owned by logged in user
	 *
	 * @param mixed $request The request
	 * @return bool
	 */
	protected function _userIsOwner($request) {
		if (empty($request['CustomPackageRequest']['customers_id'])) {
			return false;
		}
		if (!$this->Auth->user('customers_id')) {
			return false;
		}
		return $request['CustomPackageRequest']['customers_id'] == $this->Auth->user('customers_id');
	}
}
