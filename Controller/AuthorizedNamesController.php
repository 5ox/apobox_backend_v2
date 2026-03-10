<?php
App::uses('AppController', 'Controller');
App::uses('ApoboxPasswordHasher', 'Controller/Component/Auth');

/**
 * Class: AuthorizedNamesController
 *
 * @see AppController
 */
class AuthorizedNamesController extends AppController {

	/**
	 * components
	 *
	 * @var array
	 */
	public $components = array(
		'Paginator'
	);

	/**
	 * add
	 *
	 * @return mixed
	 */
	public function add() {
		if ($this->request->is('post')) {
			$this->AuthorizedName->create();
			$data = $this->request->data;
			$data['AuthorizedName']['customers_id'] = $this->Auth->user('customers_id');

			if ($this->AuthorizedName->save($data)) {
				$this->Flash->set(__('The authorized name has been saved.'));
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'account',
					'#' => 'authorized_names'
				));
			} else {
				$this->Flash->set(__('The authorized name could not be saved. Please, try again.'));
			}
		}
	}

	/**
	 * edit
	 *
	 * @param mixed $id An AuthorizedName id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function edit($id = null) {
		if (!$this->AuthorizedName->exists($id)) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		$this->AuthorizedName->id = $id;
		$authorizedName = $this->AuthorizedName->read();

		if ($authorizedName['AuthorizedName']['customers_id'] != $this->Auth->user('customers_id')) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		if ($this->request->is(array('post', 'put'))) {
			$data = $this->request->data;
			$data['AuthorizedName']['customers_id'] = $this->Auth->user('customers_id');
			if ($this->AuthorizedName->save($data)) {
				$this->Flash->set(__('The authorized name has been saved.'));
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'account',
					'#' => 'authorized_names'
				));
			} else {
				$this->Flash->set(__('The authorized name could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $authorizedName;
		}
	}

	/**
	 * delete
	 *
	 * @param mixed $id An AuthorizedName id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function delete($id = null) {
		$this->AuthorizedName->id = $id;
		if (!$this->AuthorizedName->exists()) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		$authorizedName = $this->AuthorizedName->read();

		if ($authorizedName['AuthorizedName']['customers_id'] != $this->Auth->user('customers_id')) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		$this->request->allowMethod('get', 'delete');
		if ($this->AuthorizedName->delete()) {
			$this->Flash->set(__('The authorized name has been deleted.'));
		} else {
			$this->Flash->set(__('The authorized name could not be deleted. Please, try again.'));
		}
		return $this->redirect(array(
			'controller' => 'customers',
			'action' => 'account',
			'#' => 'authorized_names'
		));
	}

	/**
	 * manager_add
	 *
	 * @param mixed $customerId A customer id
	 * @return void
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 */
	public function manager_add($customerId = null) {
		if (!($this->request->is('get') || $this->request->is('post'))) {
			throw new MethodNotAllowedException('Method must be GET or POST');
		}

		$this->AuthorizedName->Customer->id = $customerId;

		if (!$this->AuthorizedName->Customer->exists()) {
			throw new NotFoundException('The requested customer was not found.');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$data['AuthorizedName']['customers_id'] = $customerId;

			$this->AuthorizedName->create();
			if ($this->AuthorizedName->save($data)) {
				$this->Flash->set(__('The authorized name has been saved.'));
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $customerId
				));
			} else {
				$this->Flash->set(__('The authorized name could not be saved. Please, try again.'));
			}
		}
	}

	/**
	 * manager_edit
	 *
	 * @param mixed $id An AuthorizedName id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_edit($id = null) {
		$this->AuthorizedName->id = $id;
		if (!$this->AuthorizedName->exists()) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		$authorizedName = $this->AuthorizedName->read();

		if ($this->request->is(array('post', 'put'))) {
			$data = $this->request->data;
			$data['AuthorizedName']['authorized_names_id'] = $authorizedName['AuthorizedName']['authorized_names_id'];
			$data['AuthorizedName']['customers_id'] = $authorizedName['AuthorizedName']['customers_id'];

			if ($this->AuthorizedName->save($data)) {
				$this->Flash->set(__('The authorized name has been saved.'));
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $authorizedName['AuthorizedName']['customers_id']
				));
			} else {
				$this->Flash->set(__('The authorized name could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $authorizedName;
		}
	}

	/**
	 * manager_delete
	 *
	 * @param mixed $id An AuthorizedName id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_delete($id = null) {
		$this->AuthorizedName->id = $id;
		if (!$this->AuthorizedName->exists()) {
			throw new NotFoundException(__('Invalid authorized name'));
		}

		$authorizedName = $this->AuthorizedName->read('customers_id');

		$this->request->allowMethod('delete', 'get');
		if ($this->AuthorizedName->delete()) {
			$this->Flash->set(__('The authorized name has been deleted.'));
		} else {
			$this->Flash->set(__('The authorized name could not be deleted. Please, try again.'));
		}
		return $this->redirect(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $authorizedName['AuthorizedName']['customers_id']
		));
	}
}
