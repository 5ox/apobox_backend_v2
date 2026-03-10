<?php
/**
 * Admins
 */

App::uses('AppController', 'Controller');

/**
 * Admins Controller
 *
 * @property	Admin	$Admin
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class AdminsController extends AppController {

	/**
	 * Models
	 *
	 * @var	array
	 */
	//public $uses = array();

	/**
	 * Components
	 *
	 * @var	array
	 */
	public $components = array(
		'Paginator',
	);

	/**
	 * Helpers
	 *
	 * @var	array
	 */
	//public $helpers = array();

	/**
	 * beforeFilter
	 *
	 * @return void
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(['login', 'login_google']);
		$this->configureAdminAuthComponent();
	}

	/**
	 * Universal method for loggin in users in the warehouse
	 *
	 * @return mixed
	 */
	public function login() {
		$this->layout = 'manager';
		if (!Configure::read('OAuth2.legacyLogin')) {
			return;
		}
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$this->Flash->set('You have been logged in.');
				return $this->redirect(array(
					'controller' => 'admins',
					'action' => 'index',
					$this->Auth->user('role') => true,
				));
			}
			$this->Flash->set('Your email address or password was incorrect');
			unset($this->request->data['Admin']['password']);
		}
	}

	/**
	 * Convenience logout method for users in the Warehouse
	 *
	 * @return mixed
	 */
	public function logout() {
		if ($this->Auth->logout()) {
			$this->Flash->set('You have been logged out.');
			return $this->redirect('/admin/login');
		}
	}

	/**
	 * Google OAuth2 login based on `Admin.email`. An admin user must have an
	 * existing account created via manager admin with an email address that
	 * matches their Google account. If a match is found, the user is logged
	 * in.
	 *
	 * @return mixed
	 */
	public function login_google() {
		$this->autoRender = false;

		// user denied authorization or another remote error
		if ($error = $this->request->query('error')) {
			return $this->googleError('Error: ' . $error);
		}

		$provider = $this->initProvider(Configure::read('OAuth2.Google'));

		// `code` is not present, user is attempting to authorize
		if (!$this->request->query('code')) {
			$authUrl = $provider->getAuthorizationUrl();
			$this->Session->write('OAuth2.state', $provider->getState());
			return $this->redirect($authUrl);
		}

		// stored session `state` and returned query `state` do not match
		if ($this->request->query('state') !== $this->Session->read('OAuth2.state')) {
			return $this->googleError('Error: invalid authorization state.');
		}

		// attempt to get a token and resource owner (user) details
		try {
			$token = $provider->getAccessToken('authorization_code', [
				'code' => $this->request->query('code')
			]);
			$user = $provider->getResourceOwner($token);
		} catch (Exception $e) {
			return $this->googleError('Token Error: ' . $e->getMessage());
		}

		// log the user in if the resource owner email matches our value
		$admin = $this->Admin->findByEmail($user->getEmail());
		if ($admin) {
			unset($admin['Admin']['password']);
			$this->Auth->login($admin['Admin']);
			$this->Flash->set('You have been logged in.');
			return $this->redirect([
				'controller' => 'admins',
				'action' => 'index',
				$admin['Admin']['role'] => true,
			]);
		}

		if (Configure::read('OAuth2.logFailedAttemps')) {
			$this->log(
				'AdminsController::login_google: Attempted Google login by ' . $user->getEmail(),
				'login-error'
			);
		}

		return $this->googleError("You're email address is not authorized or could not be found.");
	}

	/**
	 * Set flash, delete session and redirect for Google login
	 *
	 * @param string $msg The error message
	 * @return mixed
	 */
	protected function googleError($msg) {
		$this->Flash->set($msg);
		$this->Session->delete('OAuth2');
		return $this->redirect('/admin/login');
	}

	/**
	 * Instantiate and return a Google OAuth2 provider object
	 *
	 * @param array $config The required provider configuration and credentials
	 * @return object The Google provider
	 */
	protected function initProvider($config) {
		return new League\OAuth2\Client\Provider\Google($config);
	}

	/**
	 * configureAdminAuthComponent
	 *
	 * @return void
	 */
	protected function configureAdminAuthComponent() {
		$this->Auth->authenticate = array(
			'Form' => array(
				'userModel' => 'Admin',
				'fields' => array(
					'username' => 'email'
				),
				'passwordHasher' => 'Blowfish',
			),
		);
		$this->Auth->loginRedirect = array(
			'controller' => 'admins',
			'action' => 'index',
			'plugin' => false,
		);
		$this->Auth->logoutRedirect = '/admins/login';
		$this->Auth->loginAction = array(
			'controller' => 'admins',
			'action' => 'login',
			'manager' => false,
			'employee' => false,
			'plugin' => false,
		);
	}

	/**
	 * employee_index method
	 *
	 * @return	void
	 */
	public function employee_index() {
		return $this->manager_index();
	}

	/**
	 * manager_index method
	 *
	 * @return	void
	 */
	public function manager_index() {
		$query = $this->request->query('q');
		if (!empty($query)) {
			$controller = Inflector::pluralize($this->Admin->determineModelToSearch($query));
			// remove the prefix from the tracking query
			if ($controller == 'trackings') {
				$this->request->query['q'] = substr($query, strlen(Configure::read('Tracking.prefix')));
			}
			return $this->redirect(array(
				'controller' => $controller,
				'action' => 'search',
				'?' => $this->request->query,
			));
		}

		$this->uses[] = 'Order';
		$paidManually = $this->Order->find('all', array(
			'contain' => array(
				'OrderStatus',
				'Customer',
				'OrderTotal',
			),
			'conditions' => array(
				'Order.orders_status' => 4,
			),
			'limit' => 10,
		));
		$inWarehouse = $this->Order->find('all', array(
			'contain' => array(
				'OrderStatus',
				'Customer',
				'OrderTotal',
			),
			'conditions' => array(
				'Order.orders_status' => 1,
			),
			'limit' => 10,
		));
		$orderStatuses = $this->Order->OrderStatus->find('all');

		$this->set(compact('paidManually', 'inWarehouse', 'orderStatuses'));
	}

	/**
	 * List the admins.
	 *
	 * @return	void
	 */
	public function manager_index_list() {
		$this->Admin->recursive = 0;
		$this->set('admins', $this->Paginator->paginate());
	}

	/**
	 * manager_add method
	 *
	 * @return	void
	 */
	public function manager_add() {
		if ($this->request->is('post')) {
			$this->Admin->create();
			if ($this->Admin->save($this->request->data)) {
				$this->Flash->set(__('The admin has been saved.'));
				return $this->redirect(array('action' => 'index_list'));
			} else {
				$this->Flash->set(__('The admin could not be saved. Please, try again.'));
			}
		}
	}

	/**
	 * manager_edit
	 *
	 * @param mixed $id The admin id
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_edit($id = null) {
		if (!$this->Admin->exists($id)) {
			throw new NotFoundException(__('Invalid admin'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Admin->save($this->request->data)) {
				$this->Flash->set(__('The admin has been saved.'));
				return $this->redirect(array('action' => 'index_list'));
			} else {
				$this->Flash->set(__('The admin could not be saved. Please, try again.'));
			}
		} else {
			$options = array(
				'conditions' => array(
					'Admin.' . $this->Admin->primaryKey => $id
				),
				// Omit the password field
				'fields' => array(
					'Admin.id',
					'Admin.email',
					'Admin.role',
					'Admin.token',
				),
			);
			$this->request->data = $this->Admin->find('first', $options);
		}
	}

	/**
	 * manager_delete
	 *
	 * @param mixed $id The admin id
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_delete($id = null) {
		$this->Admin->id = $id;
		if (!$this->Admin->exists()) {
			throw new NotFoundException(__('Invalid admin'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Admin->delete()) {
			$this->Flash->set(__('The admin has been deleted.'));
		} else {
			$this->Flash->set(__('The admin could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index_list'));
	}
}
