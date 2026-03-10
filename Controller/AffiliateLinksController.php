<?php
App::uses('AppController', 'Controller');
/**
 * AffiliateLinks Controller
 *
 * @property AffiliateLink $AffiliateLink
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 * @property FlashComponent $Flash
 */
class AffiliateLinksController extends AppController {

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array('Paginator', 'Session', 'Flash');

	/**
	 * manager_index method
	 *
	 * @return void
	 */
	public function manager_index() {
		$this->AffiliateLink->recursive = 0;
		$this->set('affiliateLinks', $this->Paginator->paginate());
	}

	/**
	 * manager_add method
	 *
	 * @return void
	 */
	public function manager_add() {
		if ($this->request->is('post')) {
			$this->AffiliateLink->create();
			if ($this->AffiliateLink->save($this->request->data)) {
				$this->Flash->success(__('The affiliate link has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The affiliate link could not be saved. Please, try again.'));
			}
		}
	}

	/**
	 * manager_edit method
	 *
	 * @param string $id AffiliateLink ID
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_edit($id = null) {
		if (!$this->AffiliateLink->exists($id)) {
			throw new NotFoundException(__('Invalid affiliate link'));
		}

		if ($this->request->is(array('post', 'put'))) {
			if ($this->AffiliateLink->save($this->request->data)) {
				$this->Flash->success(__('The affiliate link has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The affiliate link could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AffiliateLink.' . $this->AffiliateLink->primaryKey => $id));
			$this->request->data = $this->AffiliateLink->find('first', $options);
		}
	}

	/**
	 * manager_delete method
	 *
	 * @param string $id AffiliateLink ID
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_delete($id = null) {
		$this->AffiliateLink->id = $id;
		if (!$this->AffiliateLink->exists()) {
			throw new NotFoundException(__('Invalid affiliate link'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AffiliateLink->delete()) {
			$this->Flash->success(__('The affiliate link has been deleted.'));
		} else {
			$this->Flash->error(__('The affiliate link could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
