<?php
App::uses('AdminsController', 'Controller');

/**
 * AdminsController Test Case
 *
 */
class AdminsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.admin',
		'app.order',
		'app.order_status',
		'app.customer',
		'app.order_total',
	);

	public function setup() {
		parent::setup();
		// Assume we don't care if the request came from
		// Within the warehouse and that this is tested elsewhere.
		Configure::write('Security.admin.ips', false);
	}

	public function testLogin() {
		Configure::write('OAuth2.legacyLogin', true);
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'admins',
			'action' => 'login',
		));
		$Admins = $this->generate('Admins', array(
			'components' => array(
				'Auth' => array('login')
			)
		));
		$Admins->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(true));

		$this->testAction($url, array('method' => 'post'));

		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	public function testLoginAuthFails() {
		Configure::write('OAuth2.legacyLogin', true);
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'admins',
			'action' => 'login',
		));
		$Admins = $this->generate('Admins', array(
			'components' => array(
				'Auth' => array('login')
			)
		));
		$Admins->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(false));

		$this->testAction($url, array('method' => 'post'));

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testLoginGET() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'admins',
			'action' => 'login',
		));
		$Admins = $this->generate('Admins', array(
			'components' => array(
				'Auth' => array('login')
			)
		));
		$Admins->Auth
			->expects($this->never())
			->method('login');

		$result = $this->testAction($url, array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testLoginLegacyDisabled() {
		Configure::delete('OAuth2.legacyLogin');
		$userId = 1;
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login',
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Auth' => ['login']
			]
		]);
		$Admins->Auth
			->expects($this->never())
			->method('login');

		$result = $this->testAction($url, ['method' => 'get']);

		$this->assertEmpty($result);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 * Confirm the logout() method redirects
	 *
	 * @return	void
	 */
	public function testLogout() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'admins',
			'action' => 'logout',
		));
		$Admins = $this->generate('Admins', array(
			'components' => array(
				'Auth' => array('logout')
			)
		));
		$Admins->Auth
			->expects($this->once())
			->method('logout')
			->will($this->returnValue(true));

		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm the logout() method redirects to login if it fails
	 *
	 * @return	void
	 */
	public function testLogoutFails() {
		$userId = 1;
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'logout',
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Auth' => ['logout']
			]
		]);
		$Admins->Auth
			->expects($this->once())
			->method('logout')
			->will($this->returnValue(false));

		$result = $this->testAction($url);
		$this->assertStringEndsWith('/admin/login', $this->headers['Location']);
	}

	/**
	 * testManagerIndex method
	 *
	 * @return	void
	 */
	public function testManagerIndex() {
		$this->setupManagerAuth(1);
		$this->testAction('/manager', array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers);
		$this->assertArrayHasKey('paidManually', $this->vars);
		$this->assertArrayHasKey('orderStatuses', $this->vars);
		$this->assertInternalType('array', $this->vars['paidManually']);
		$this->assertArrayHasKey(0, $this->vars['paidManually'], '$paidManually should contain at least one record from fixture data.');
		$this->assertArrayHasKey('Customer', $this->vars['paidManually'][0], '$paidManually should contain customer data.');
		$this->assertGreaterThan(
			strtotime($this->vars['paidManually'][1]['Order']['last_modified']),
			strtotime($this->vars['paidManually'][0]['Order']['last_modified']),
			'Paid manually should be sorted newest first'
		);
		$this->assertArrayHasKey('OrderTotal', $this->vars['paidManually'][0], '$paidManually should contain at least one OrderTotal.');
		$this->assertArrayHasKey('Customer', $this->vars['paidManually'][0], '$paidManually should contain at least one Customer.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['paidManually'][0], '$paidManually should contain at least one OrderStatus.');
		$this->assertArrayHasKey('OrderTotal', $this->vars['inWarehouse'][0], '$inWarehouse should contain at least one OrderTotal.');
		$this->assertArrayHasKey('Customer', $this->vars['inWarehouse'][0], '$inWarehouse should contain at least one Customer.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['inWarehouse'][0], '$inWarehouse should contain at least one OrderStatus.');
	}

	/**
	 * testManagerIndexWithQuery method
	 *
	 * @return	void
	 */
	public function testManagerIndexWithQuery() {
		$this->setupManagerAuth(1);
		$this->testAction('/manager?q=1', array('method' => 'get'));

		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringEndsWith('/customers?q=1', $this->headers['Location']);
	}

	/**
	 * testManagerIndexWithQueryRedirects method
	 *
	 * @dataProvider managerIndexWithQueryRedirects
	 * @return	void
	 */
	public function testManagerIndexWithQueryRedirects($query, $expected) {
		$this->setupManagerAuth(1);
		$query = urlencode($query);
		$this->testAction('/manager?q=' . $query, array('method' => 'get'));

		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertContains($expected, $this->headers['Location']);
	}

	public function managerIndexWithQueryRedirects() {
		return array(
			array('nc1234', '/manager/customers'),
			array('FL 04', '/manager/customers'),
			array('FirstName', '/manager/customers'),
			array('FL First Last', '/manager/customers'),
			array('FL1', '/manager/customers'),
			array('FL', '/manager/customers'),
			array('1234', '/manager/customers'),
			array('1Z1234', '/manager/orders'),
			array('1Z9999999999999999', '/manager/orders'),
			array('12345678999876543123', '/manager/orders'),
			array('1234567890123456789012', '/manager/orders'),
			array('1234A678999G7654f123', '/manager/orders'),
			array('12345', '/manager/orders'),
			array('S:', '/manager/scans'),
			array('s:12345', '/manager/scans'),
			array('S:12345', '/manager/scans'),
			array('S:abcdefg', '/manager/scans'),
		);
	}

	/**
	 * Confirm the employee_index() method has the same results as the
	 * manager_index() method.
	 *
	 * @return	void
	 */
	public function testEmployeeIndex() {
		$this->setupManagerAuth(2);
		$this->testAction('/employee', array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers);
		$this->assertArrayHasKey('paidManually', $this->vars);
		$this->assertInternalType('array', $this->vars['paidManually']);
		$this->assertArrayHasKey(0, $this->vars['paidManually'], '$paidManually should contain at least one record from fixture data.');
		$this->assertArrayHasKey('Customer', $this->vars['paidManually'][0], '$paidManually should contain customer data.');
		$this->assertGreaterThan(
			strtotime($this->vars['paidManually'][1]['Order']['last_modified']),
			strtotime($this->vars['paidManually'][0]['Order']['last_modified']),
			'Paid manually should be sorted newest first'
		);
	}

	/**
	 * Confirm that manager_index_list() sets the $admins variable with some
	 * data.
	 *
	 * @return	void
	 */
	public function testManagerIndexList() {
		$this->setupManagerAuth(1);
		$this->testAction('/manager/admins/index', array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers);
		$this->assertArrayHasKey('admins', $this->vars);
		$this->assertArrayHasKey(0, $this->vars['admins'], '$admins should contain at least one record from fixture data.');
	}

	/**
	 * Confirm a post request with data adds an admin user and redirects
	 *
	 * @return	void
	 */
	public function testManagerAddPostSuccess() {
		$Admins = $this->setupManagerAuth(1);
		$data = array(
			'Admin' => array(
				'password' => 'password',
				'email' => 'test@local.com',
				'role' => 'manager',
				'token' => ''
			)
		);
		$Admins->Flash
			->expects($this->once())
			->method('set');

		$Admin = ClassRegistry::init('Admin');
		$beforeCount = $Admin->find('count');
		$this->testAction('/manager/admins/add', array('method' => 'post', 'data' => $data));
		$afterCount = $Admin->find('count');
		$this->assertEquals($beforeCount + 1, $afterCount);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('/index', $this->headers['Location']);
	}

	/**
	 * Confirm a post request with data does not redirect when it fails
	 *
	 * @return	void
	 */
	public function testManagerAddPostFail() {
		$Admins = $this->setupManagerAuth(1);
		$data = array(
			'Admin' => array(
				'password' => 'password',
				'email' => '',
				'role' => 'manager',
				'token' => ''
			)
		);
		$Admins->Flash
			->expects($this->once())
			->method('set');

		$Admin = ClassRegistry::init('Admin');
		$beforeCount = $Admin->find('count');
		$this->testAction('/manager/admins/add', array('method' => 'post', 'data' => $data));
		$afterCount = $Admin->find('count');
		$this->assertEquals($beforeCount, $afterCount);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should NOT redirect.');
	}

	/**
	 * Confirm an exception is thrown if manager_edit() is not supplied an $id
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testManagerEditWithoutId() {
		$this->setupManagerAuth(1);
		$result = $this->testAction('/manager/admins/edit');
	}

	/**
	 * Confirm the manager_edit() method sets the correct data when the
	 * request is not post and an id is passed.
	 *
	 * @return	void
	 */
	public function testManagerEditNotPost() {
		$Admins = $this->setupManagerAuth(1);
		$id = 2;

		$this->testAction('/manager/admins/edit/' . $id, array('method' => 'get'));
		$this->assertArrayHasKey('Admin', $Admins->request->data);
		$this->assertEquals($id, $Admins->request->data['Admin']['id']);
	}

	/**
	 * Confirm the manager_edit() method changes data and redirects
	 *
	 * @return	void
	 */
	public function testManagerEditPost() {
		$Admins = $this->setupManagerAuth(1);
		$id = 2;
		$data = array(
			'Admin' => array(
				'id' => $id,
				'role' => 'manager',
			),
		);
		$Admins->Flash
			->expects($this->once())
			->method('set');

		$Admin = ClassRegistry::init('Admin');
		$beforeVal = $Admin->findById($id, 'role');
		$this->testAction('/manager/admins/edit/' . $id, array('method' => 'post', 'data' => $data));
		$afterVal = $Admin->findById($id, 'role');
		$this->assertNotEqual($beforeVal['Admin']['role'], $afterVal['Admin']['role']);
		$this->assertEquals($data['Admin']['role'], $afterVal['Admin']['role']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('/index', $this->headers['Location']);
	}

	/**
	 * Confirm that when saving edited data fails, the expected flash message
	 * is displayed.
	 *
	 * @return void
	 */
	public function testManagerEditSaveFails() {
		$Admins = $this->setupManagerAuth(1);
		$id = 2;
		$data = [
			'Admin' => [
				'id' => $id,
				'role' => 'manager',
			],
		];

		$Admins = $this->generate('Admins', [
			'models' => [
				'Admin' => ['exists', 'save'],
			],
			'components' => [
				'Flash',
			],
		]);

		$Admins->Admin
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Admins->Admin
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('The admin could not be saved. Please, try again.');

		$this->testAction('/manager/admins/edit/' . $id, ['method' => 'post', 'data' => $data]);
	}

	/**
	 * Confirm an exception is thrown if manager_delete() is not supplied an $id
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testManagerDeleteWithoutId() {
		$this->setupManagerAuth(1);
		$result = $this->testAction('/manager/admins/delete');
	}

	/**
	 * Confirm a post request deletes an admin user, sets a flash method, and
	 * redirects.
	 *
	 * @return	void
	 */
	public function testManagerDeleteWithId() {
		$Admins = $this->setupManagerAuth(1);
		$id = 2;
		$data = array(
			'Admin' => array(
				'id' => $id,
			)
		);
		$Admins->Flash
			->expects($this->once())
			->method('set');

		$Admin = ClassRegistry::init('Admin');
		$beforeCount = $Admin->find('count');
		$this->testAction('/manager/admins/delete/' . $id, array('method' => 'post', 'data' => $data));
		$afterCount = $Admin->find('count');
		$this->assertEquals($beforeCount - 1, $afterCount);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('/index', $this->headers['Location']);
	}

	/**
	 * Confirm that when a delete action fails, the expected flash message is
	 * displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testManagerDeleteFails() {
		$Admins = $this->setupManagerAuth(1);
		$id = 2;
		$data = [
			'Admin' => [
				'id' => $id,
			]
		];
		$Admins = $this->generate('Admins', [
			'models' => [
				'Admin' => ['exists', 'delete'],
			],
			'components' => [
				'Flash',
			],
		]);

		$Admins->Admin
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Admins->Admin
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('The admin could not be deleted. Please, try again.');

		$this->testAction('/manager/admins/delete/' . $id, ['method' => 'post', 'data' => $data]);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('/index', $this->headers['Location']);
	}

	/**
	 * Confirm that if a request with a query string containing the `error` key
	 * a flash message is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleQueryError() {
		$error = 'an error';
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
			'?' => ['error' => $error],
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Flash' => ['set'],
				'Session' => ['delete'],
			]
		]);
		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('Error: ' . $error);
		$Admins->Session
			->expects($this->once())
			->method('delete');

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm the expected methods are called, a session variable is written,
	 * and that a redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleNoCode() {
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Session' => ['write']
			],
			'methods' => [
				'initProvider',
			]
		]);
		$Provider = $this->getMockBuilder('Google')
			->disableOriginalConstructor()
			->setMethods(['getAuthorizationUrl', 'getState'])
			->getMock();

		$Admins
			->expects($this->once())
			->method('initProvider')
			->will($this->returnValue($Provider));
		$Admins->Session
			->expects($this->once())
			->method('write')
			->with('OAuth2.state');
		$Provider
			->expects($this->once())
			->method('getAuthorizationUrl')
			->will($this->returnValue('https://apobox.dev/foo/bar'));
		$Provider
			->expects($this->once())
			->method('getState')
			->will($this->returnValue('12345'));

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm that if `state` in a query string does not match a saved session
	 * `state` value, a flash message is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleMismatchState() {
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
			'?' => ['state' => 'foo', 'code' => 'bar'],
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Flash' => ['set'],
				'Session' => ['read', 'delete'],
			]
		]);
		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('Error: invalid authorization state.');
		$Admins->Session
			->expects($this->any())
			->method('read')
			->will($this->returnValue('foobar'));
		$Admins->Session
			->expects($this->once())
			->method('delete');

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm that when an exception is thrown when attempting to get an
	 * access token with getAccessToken(), the session is deleted and a
	 * redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleTokenException() {
		$error = 'an error';
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
			'?' => ['code' => 'foo', 'state' => 'bar'],
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Flash' => ['set'],
				'Session' => ['read', 'delete'],
			],
			'methods' => [
				'initProvider',
			],
		]);
		$Provider = $this->getMockBuilder('Google')
			->disableOriginalConstructor()
			->setMethods(['getAccessToken', 'getResourceOwner'])
			->getMock();

		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('Token Error: ' . $error);
		$Admins->Session
			->expects($this->any())
			->method('read')
			->will($this->returnValue('bar'));
		$Admins->Session
			->expects($this->once())
			->method('delete');
		$Admins
			->expects($this->once())
			->method('initProvider')
			->will($this->returnValue($Provider));
		$Provider
			->expects($this->once())
			->method('getAccessToken')
			->will($this->throwException(new Exception($error)));

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm that when everything goes okay with OAuth2 login but the user
	 * email address does not match a local record the session is deleted, a
	 * flash message is displayed, and a redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleUserNotFound() {
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
			'?' => ['code' => 'foo', 'state' => 'bar'],
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Flash' => ['set'],
				'Session' => ['read', 'delete'],
			],
			'methods' => [
				'initProvider',
				'log',
			],
		]);
		$Provider = $this->getMockBuilder('Google')
			->disableOriginalConstructor()
			->setMethods(['getAccessToken', 'getResourceOwner'])
			->getMock();
		$User = $this->getMockBuilder('User')
			->setMethods(['getEmail'])
			->getMock();

		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with("You're email address is not authorized or could not be found.");
		$Admins->Session
			->expects($this->any())
			->method('read')
			->will($this->returnValue('bar'));
		$Admins->Session
			->expects($this->once())
			->method('delete');
		$Admins
			->expects($this->once())
			->method('initProvider')
			->will($this->returnValue($Provider));
		$Admins
			->expects($this->once())
			->method('log')
			->with(
				$this->identicalTo('AdminsController::login_google: Attempted Google login by invalid user'),
				$this->identicalTo('login-error')
			);
		$Provider
			->expects($this->once())
			->method('getAccessToken')
			->will($this->returnValue('foo'));
		$Provider
			->expects($this->once())
			->method('getResourceOwner')
			->will($this->returnValue($User));
		$User
			->expects($this->exactly(2))
			->method('getEmail')
			->will($this->returnValue('invalid user'));

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm that when everything goes okay with OAuth2 login and the user is
	 * a valid admin user (matching email address) the user is logged in, a flash
	 * message is displayed, and a redirect happens.
	 *
	 * @return void
	 */
	public function testLoginGoogleSuccess() {
		$url = Router::url([
			'controller' => 'admins',
			'action' => 'login_google',
			'?' => ['code' => 'foo', 'state' => 'bar'],
		]);
		$Admins = $this->generate('Admins', [
			'components' => [
				'Flash' => ['set'],
				'Session' => ['read', 'delete'],
				'Auth' => ['login'],
			],
			'methods' => [
				'initProvider',
			],
		]);
		$Provider = $this->getMockBuilder('Google')
			->disableOriginalConstructor()
			->setMethods(['getAccessToken', 'getResourceOwner'])
			->getMock();
		$User = $this->getMockBuilder('User')
			->setMethods(['getEmail'])
			->getMock();

		$Admins->Flash
			->expects($this->once())
			->method('set')
			->with('You have been logged in.');
		$Admins->Session
			->expects($this->any())
			->method('read')
			->will($this->returnValue('bar'));
		$Admins->Session
			->expects($this->never())
			->method('delete');
		$Admins
			->expects($this->once())
			->method('initProvider')
			->will($this->returnValue($Provider));
		$Admins->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(true));
		$Provider
			->expects($this->once())
			->method('getAccessToken')
			->will($this->returnValue('foo'));
		$Provider
			->expects($this->once())
			->method('getResourceOwner')
			->will($this->returnValue($User));
		$User
			->expects($this->once())
			->method('getEmail')
			->will($this->returnValue('manager@example.com'));

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * setupManagerAuth
	 *
	 * @param mixed $adminId
	 */
	protected function setupManagerAuth($adminId) {
		$Admins = $this->generate('Admins', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Flash',
			)
		));

		$admin = ClassRegistry::init('Admin')->find('first', array(
			'contain' => array(),
			'conditions' => array('id' => $adminId),
		));
		$admin = $admin['Admin'];
		$authAdminSingle = function($field) use ($admin) {
			return (!$field) ? $admin : $admin[$field];
		};

		$Admins->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authAdminSingle));

		return $Admins;
	}
}
