<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::uses('AppEmail', 'Lib/Network/Email');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	protected $formAuthConfig = [
		'Form' => [
			'userModel' => 'Customer',
			'passwordHasher' => 'Apobox',
			'fields' => [
				'username' => 'customers_email_address',
				'password' => 'customers_password',
			],
			'contain' => [],
			'scope' => [
				'Customer.is_active' => 1,
			],
		],
	];

	/**
	 * Components to load for all controllers.
	 *
	 * @var array
	 */
	public $components = array(
		'Cookie',
		'Session',
		'Flash' => array('className' => 'AppFlash'),
		'DebugKit.Toolbar',
		'Paginator',
		'RequestHandler',
		'Auth' => array(
			'authError' => false,
			'loginRedirect' => array('controller' => 'customers', 'action' => 'account'),
			'logoutRedirect' => '/login',
			'loginAction' => array(
				'controller' => 'customers',
				'action' => 'login',
				'plugin' => false,
			),
			'authorize' => array(
				'Controller'
			),
			'flash' => array(
				'element' => 'flash_bootstrap',
				'params' => null,
				'key' => 'auth'
			),
		),
	);

	/**
	 * Helpers list to load for all controllers
	 *
	 * @var array
	 */
	public $helpers = array(
		'Session',
		'Flash',
		'Html',
		'TB' => array('className' => 'TwitterBootstrap'),
		'Form' => array('className' => 'AppForm'),
	);

	/**
	 * Allows controllers to change auth access without having to override
	 * the entire beforeFilter.
	 *
	 * @access	public
	 * @return	void
	 */
	public function appAuth() {
		$this->Auth->allow(array('display'));
	}

	/**
	 * beforeFilter
	 *
	 * @access	public
	 * @return	void
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->authenticate = $this->formAuthConfig;
		$self = $this;
		$this->request->addDetector('fromWarehouse', array(
			'callback' => (function ($request) use ($self) {
				// @codeCoverageIgnoreStart
				return $self->requestIsFromWarehouse($request);
				// @codeCoverageIgnoreEnd
			})
		));

		$this->_configureRoleAuth();
		if ($this->_isAdminRoute()) {
			$this->_adminBeforeFilter();
		}

		if ($this->_isApiRoute()) {
			$this->_apiBeforeFilter();
		}

		$this->appAuth();
	}

	/**
	 * Load data used by all views, such the current logged in user (to
	 * avoid the use of AuthComponent::user() in views.) Conditionals
	 * in views should look like this:
	 *
	 * if ($u && $u['role'] == 'student') {
	 * 	//do something
	 * }
	 *
	 * @access	public
	 * @return	void
	 */
	public function beforeRender() {
		parent::beforeRender();
		$this->set('u', $this->Auth->user()); // Will be null when not logged in.
	}

	/**
	 * Used by Auth component to check authorization. Checks a series of
	 * methods that can return true to allow access.
	 *
	 * @param mixed $user A $user array or null
	 * @return bool
	 */
	public function isAuthorized($user = null) {
		return (
			$this->_isLoggingOut()
			|| $this->_isRoleAuthorized()
			|| $this->_isCustomerAuthorized()
		);
	}

	/**
	 * Returns true if accessing ANY logout method.
	 *
	 * @return bool
	 */
	protected function _isLoggingOut() {
		return ($this->action === 'logout');
	}

	/**
	 * Returns true if accessing a prefixed route and it matches the user's role.
	 *
	 * @return bool
	 */
	protected function _isRoleAuthorized() {
		return (
			!empty($this->request->prefix)
			&& $this->Auth->user('role') === $this->request->prefix
		);
	}

	/**
	 * Handle automatic redirecting of admin routes between different roles.
	 *
	 * @return bool
	 */
	protected function _redirectSharedAdminRoutes() {
		if (
			empty($this->request->prefix)
			|| empty($this->Auth->user('role'))
			|| $this->Auth->user('role') === $this->request->prefix
		) {
			return false;
		}

		$prefix = array_filter(Configure::read('Routing.prefixes'), function ($value) {
			return $value != 'api' && $value != $this->request->prefix;
		});
		$url = str_replace($this->request->prefix, reset($prefix), $this->request->here);
		return $this->redirect($url);
	}

	/**
	 * Returns true if not accessing a prefixed route and the user has no role.
	 *
	 * @return bool
	 */
	protected function _isCustomerAuthorized() {
		$role = $this->Auth->user('role');
		return (
			empty($this->request->prefix)
			&& empty($role)
		);
	}

	/**
	 * _configureRoleAuth
	 * Overrides Auth actions based on role.
	 *
	 * @return void
	 */
	protected function _configureRoleAuth() {
		$role = $this->Auth->user('role');
		if ($role) {
			$this->Auth->loginRedirect = array(
				'controller' => 'admins',
				'action' => 'index',
				$this->Auth->user('role') => true,
			);
			$this->Auth->loginAction = array(
				'controller' => 'admins',
				'action' => 'login',
				'prefix' => false
			);
		}
	}

	/**
	 * Returns true if accessing an admin route.
	 *
	 * Currently:
	 * - All prefixed routes
	 * - All `admins` controller actions
	 *
	 * @return bool
	 */
	protected function _isAdminRoute() {
		return (
			!empty($this->request->prefix)
			|| $this->request->params['controller'] === 'admins'
		);
	}

	/**
	 * Returns true if accessing an api route.
	 *
	 * @return bool
	 */
	protected function _isApiRoute() {
		return (
			($this->request->prefix == 'api') ||
			!empty($this->request->params['api'])
		);
	}

	/**
	 * Instantiates and returns an instance of the application's email
	 * handler class, AppEmail.
	 *
	 * @param string $config The name of the CakeEmail config class to use.
	 * @return AppEmail Instance of the subclassed CakeEmail class.
	 */
	public function emailFactory($config = null) {
		return new AppEmail($config);
	}

	/**
	 * requestIsFromWarehouse
	 *
	 * @param CakeRequest $request A cake request
	 * @return bool
	 */
	public function requestIsFromWarehouse(CakeRequest $request) {
		$ips = Configure::read('Security.admin.ips');
		if (empty($ips)) {
			return true;
		}

		return false;
	}

	/**
	 * Sets the layout and runs the manager beforeFilter if accessing an admin
	 * route.
	 *
	 * Method runs in the beforeFilter when hitting an admin route
	 *
	 * @return void
	 * @throws ForbiddenException
	 */
	protected function _adminBeforeFilter() {
		$this->_redirectSharedAdminRoutes();
		$this->layout = 'manager';
		$this->set('isManager', $this->_userIsManager());
		$this->set('isEmployee', $this->_userIsEmployee());
		if (!$this->request->is('fromWarehouse')) {
			throw new ForbiddenException('You can\'t access that outside from this location.');
		}
	}

	/**
	 * Returns true if the user is a manager.
	 *
	 * @return bool
	 */
	protected function _userIsManager() {
		$role = $this->Auth->user('role');
		return (
			!empty($role)
			&& $role === 'manager'
		);
	}

	/**
	 * Returns true if the user is a employee.
	 *
	 * @return bool
	 */
	protected function _userIsEmployee() {
		$role = $this->Auth->user('role');
		return (
			!empty($role)
			&& $role === 'employee'
		);
	}

	/**
	 * Called in beforeFilter() if api prefix is truthy
	 *
	 * @return void
	 * @throws BadRequestException
	 */
	protected function _apiBeforeFilter() {
		$this->Auth->authenticate = array('Token' => array(
			'userModel' => 'Admin',
			'scope' => array(
				'Admin.role' => 'api',
			),
			'contain' => array(),
		));
		$this->viewClass = 'Serializers.EmberDataSerializer';
		$this->renderAs = 'json';
		if (!$this->request->accepts('application/vnd.api+json')) {
			$this->viewClass = 'View';
			$this->renderAs = 'html';
			throw new BadRequestException(
				'When accessing an API endpoint, the consumer must accept the "application/vnd.api+json" content type and set the appropriate header(s).'
			);
		}
		// Deserialize incomming JSON data (HTTP POST or PATCH)
		// This is a 1:1 conversion of input JSON to a PHP array
		$this->request->addDetector(
			'patch',
			array('env' => 'REQUEST_METHOD', 'value' => 'PATCH')
		);
		if ($this->request->is('post') || $this->request->is('patch')) {
			$this->request->data = $this->request->input('json_decode', true);
			if ($this->request->data == null) {
				throw new BadRequestException('Invalid JSON');
			}
		}
	}

	/**
	 * Initializes a json response and parses incoming json data.
	 *
	 * @return void
	 * @throws BadRequestException
	 */
	protected function initJsonResponse() {
		if (!$this->request->accepts('application/vnd.api+json')) {
			throw new BadRequestException('Method not implemented');
		}

		$this->viewClass = 'Serializers.EmberDataSerializer';
		$this->renderAs = 'json';
		// Deserialize incomming JSON data
		// This is a 1:1 conversion of input JSON to a PHP array
		if ($this->request->input('json_decode', true)) {
			$this->request->data = $this->request->input('json_decode', true);
		}
	}

	/**
	 * Checks that the type is properly set for the model and returns the data
	 * in a format usable by the controller methods.
	 *
	 * @param string $model The model name.
	 * @param array $allowedModels A list of valid models.
	 * @return array The JSON API formatted data.
	 * @throws BadRequestException
	 */
	protected function checkAndSetDataFromJson($model, $allowedModels = null) {
		if ($allowedModels && !in_array($model, $allowedModels)) {
			throw new BadRequestException('Invalid address model ' . $model . ' specified');
		}

		if (Inflector::classify(Hash::get($this->request->data, 'data.type')) != $model) {
			throw new BadRequestException('Invalid type set, must set type ' . $model . ' for this endpoint');
		}

		return [$model => Hash::get($this->request->data, 'data.attributes')];
	}

	/**
	 * Instantiates and returns an instance of Queue.QueuedTask
	 *
	 * @return object A QueuedTask object from the Queue plugin
	 * @codeCoverageIgnore It's just a wrapper
	 */
	public function taskFactory() {
		return ClassRegistry::init('Queue.QueuedTask');
	}

	/**
	 * Semi-generic method to write log entries for all BaseSerializerException
	 * errors.
	 *
	 * @param string $msg The exception error message thrown
	 * @param string $model The model to log data for
	 * @param string $log The configured log type to write to
	 * @param mixed $data A string or array of error data/validation errors to include
	 * @return bool The result of CakeLog::write()
	 */
	protected function logBaseSerializerException($msg, $model, $log, $data = []) {
		if (isset($data['Customer']['customers_password'])) {
			unset($data['Customer']['customers_password']);
		}
		if (isset($data['Customer']['password_confirm'])) {
			unset($data['Customer']['password_confirm']);
		}
		$data = isset($data[$model]) ? $data[$model] : $data;

		return $this->log('BaseSerializerException: ' . $msg . ' data: ' . json_encode($data), $log);
	}

	/**
	 * Get and return the AffiliateLinks used in emails.
	 *
	 * @return array
	 */
	protected function getAffiliates() {
		$this->loadModel('AffiliateLink');
		return $this->AffiliateLink->find('all', [
			'conditions' => [
				'enabled' => 1,
			],
			'recursive' => -1,
		]);
	}
}
