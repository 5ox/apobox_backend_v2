<?php
/**
 * Trackings
 */

App::uses('AppController', 'Controller');

/**
 * Trackings Controller
 *
 * @property	Tracking	$Tracking
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class TrackingsController extends AppController {

	/**
	 * Components
	 *
	 * @var	array
	 */
	public $components = array(
		'Paginator',
	);

	/**
	 * manager_search
	 *
	 * @return void
	 */
	public function manager_search() {
		$this->request->allowMethod('get');
		$search = $this->request->query('q');
		$showStatus = null;
		$conditions = $this->getConditions($search);
		if (!empty($conditions)) {
			$fromThePast = $this->request->query('from_the_past');
			if (!empty($fromThePast)) {
				$conditions[0]['AND'] = array(
					'Tracking.timestamp >= ' => date_create($fromThePast)->format('Y-m-d H:i:s')
				);
			}
			$this->Paginator->settings['conditions'] = $conditions;
			$results = $this->paginate();
		} else {
			$results = false;
		}
		if (empty($fromThePast)) {
			$fromThePast = Configure::read('Search.date.default');
		}
		$userIsManager = $this->_userIsManager();
		$this->set(compact(
			'search',
			'results',
			'fromThePast',
			'userIsManager'
		));
	}

	/**
	 * getConditions
	 *
	 * @param array $query An array of terms to add to the search conditions
	 * @return array
	 */
	protected function getConditions($query) {
		$terms = explode(' ', $query);
		$fieldRegex = array(
			'Tracking.tracking_id LIKE' => '/^[a-z\d]{0,15}/i',
		);
		$conditions = array();

		$i = 0;
		foreach ($terms as $term) {
			foreach ($fieldRegex as $field => $regex) {
				if (preg_match($regex, $term)) {
					$perms = $this->getTermSearchPermutations($field, $term);
					foreach ($perms as $perm) {
						$conditions[$i]['OR'][] = array($field => $perm);
					}
				}
			}
			$i++;
		}

		return $conditions;
	}

	/**
	 * Accepts a string and returns an array with keys of fields and
	 * values of strings to search.
	 *
	 * @param string $field A SQL fragment
	 * @param string $term The term to search for
	 * @return array
	 */
	protected function getTermSearchPermutations($field, $term) {
		$perms = array();
		$perms[] = '%' . $term . '%';
		return $perms;
	}

	/**
	 * Uses the `manager_search()` method
	 *
	 * @return mixed
	 */
	public function employee_search() {
		$this->manager_search();
		return $this->render('manager_search');
	}

	/**
	 * Manager scan-in page. Ignores empty and duplicate tracking IDs.
	 *
	 * @return mixed
	 */
	public function manager_add() {
		if ($this->request->is('post')) {
			if (empty($this->request->data['Tracking']['tracking_id'])) {
				return $this->redirect(array('action' => 'add'));
			}

			$this->Tracking->create();
			$this->Tracking->set($this->request->data['Tracking']);
			if ($this->Tracking->exists()) {
				$this->Flash->set(__('Tracking ID exists.'));
				return $this->redirect(array('action' => 'add'));
			}

			$this->Tracking->set('warehouse', Configure::read('Warehouse.code'));
			// Remove the comments if they are set but the checkbox was not checked
			if (empty($this->request->data['add_exception'])) {
				$this->Tracking->set('comments', '');
			}
			if ($this->Tracking->save()) {
				$this->Flash->set(__('Tracking ID has been saved.'));
				return $this->redirect(array('action' => 'add'));
			} else {
				$this->Flash->set(__('The scan could not be saved. Please, try again.'));
			}
		}
	}

	/**
	 * employee_add
	 *
	 * @return mixed
	 */
	public function employee_add() {
		$this->manager_add();
		return $this->render('manager_add');
	}

	/**
	 * manager_delete
	 *
	 * @param mixed $id The Tracking id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_delete($id = null) {
		$this->Tracking->id = $id;
		if (!$this->Tracking->exists()) {
			throw new NotFoundException(__('Invalid tracking'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Tracking->delete()) {
			$this->Flash->set(__('The scan has been deleted.'));
		} else {
			$this->Flash->set(__('The scan could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'search'));
	}

	/**
	 * edit method
	 *
	 * @param mixed $id The Tracking id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_edit($id = null) {
		if (!$this->Tracking->exists($id)) {
			throw new NotFoundException(__('Invalid scan id'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Tracking->save($this->request->data, true, array('comments'))) {
				$this->Flash->set(__('The scan has been updated.'));
				return $this->redirect(array('action' => 'search'));
			} else {
				$this->Flash->set(__('The scan could not be updated. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Tracking.' . $this->Tracking->primaryKey => $id));
			$this->request->data = $this->Tracking->find('first', $options);
		}
	}

	/**
	 * employee_edit
	 *
	 * @param mixed $id The Tracking id
	 * @return mixed
	 */
	public function employee_edit($id = null) {
		$this->manager_edit($id);
		return $this->render('manager_edit');
	}
}
