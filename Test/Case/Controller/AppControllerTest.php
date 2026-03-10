<?php
App::uses('AppController', 'Controller');
App::uses('AppSession', 'Controller/Component');

/**
 * TestAppController to access protected helper methods for direct testing.
 */
class TestAppController extends AppController {
	public function checkAndSetDataFromJson($model, $allowedModels = null) {
		return parent::checkAndSetDataFromJson($model, $allowedModels);
	}
	public function _adminBeforeFilter() {
		return parent::_adminBeforeFilter();
	}
	public function initJsonResponse() {
		return parent::initJsonResponse();
	}
	public function _redirectSharedAdminRoutes() {
		return parent::_redirectSharedAdminRoutes();
	}
	public function logBaseSerializerException($msg, $model, $log, $data = []) {
		return parent::logBaseSerializerException($msg, $model, $log, $data);
	}
}

/**
 * AppController Test Case
 *
 */
class AppControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
	);

	/**
	 * Confirm the admin logic is run when the prefix is set to `manager` or
	 * `employee`. Current logic is:
	 *
	 * - Layout is set to `manager`
	 * - A fromWarehouse check is performed
	 * - isManager flag gets set
	 *
	 * @dataProvider beforeFilterProvider
	 * @return void
	 */
	public function testBeforeFilterSetsManagerLogic($prefix, $warehouseCheck, $checkManager, $isManager, $layout) {
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));

		if ($warehouseCheck) {
			$App->request->expects($this->once())
				->method('is')
				->with('fromWarehouse')
				->will($this->returnValue(true));
		}

		$App->request->prefix = $prefix;
		$App->beforeFilter();
		if ($checkManager) {
			$this->assertArrayHasKey('isManager', $App->viewVars);
			if ($isManager) {
				$this->assertTrue($App->viewVars['isManager']);
			} else {
				$this->assertFalse($App->viewVars['isManager']);
			}
		}
		$this->assertEquals($layout, $App->layout);
	}

	public function beforeFilterProvider() {
		return array(
			array('manager', true, true, true, 'manager'),
			array('employee', true, true, false, 'manager'),
			array(null, false, false, true, 'default'),

		);
	}

	/**
	 * Confirm that viewClass and renderAs are set correctly.
	 *
	 * @return void
	 */
	public function testBeforeFilterApiLogic() {
		$prefix = 'api';
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));
		$App->request->expects($this->exactly(2))
			->method('is')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(true));

		$this->setExpectedException('BadRequestException', 'Invalid JSON');

		$App->request->prefix = $prefix;
		$App->beforeFilter();

		$this->assertEquals('Serializers.EmberDataSerializer', $App->viewClass, 'Failed to set correct view class');
		$this->assertEquals('json', $App->renderAs, 'Failed to set correct rendeAs value');
	}

	/**
	 *
	 */
	public function testBeforeFilterApiLogicWithBadHeadersThrowsException() {
		$prefix = 'api';
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));
		$App->request->expects($this->once())
			->method('is')
			->with('fromWarehouse')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(false));
		$this->setExpectedException('BadRequestException');

		$App->request->prefix = $prefix;
		$App->beforeFilter();
	}

	/**
	 *
	 */
	public function testBeforeFilterApiLogicWithBadHeaders() {
		$prefix = 'api';
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));
		$App->request->expects($this->once())
			->method('is')
			->with('fromWarehouse')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(false));

		$App->request->prefix = $prefix;
		try {
			$App->beforeFilter();
		} catch (BadRequestException $e) {
			$this->assertEquals('View', $App->viewClass, 'Failed to set correct view class');
			$this->assertEquals('html', $App->renderAs, 'Failed to set correct rendeAs value');
		}
	}

	/**
	 * Confirm that necessary universal view vars are present.
	 *
	 * @return void
	 */
	public function testBeforeRender() {
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
		));
		$App->Auth->staticExpects($this->once())
			->method('user')
			->will($this->returnValue('canary'));

		$App->beforeRender();

		$this->assertEquals('canary', $App->viewVars['u']);
	}

	/**
	 * Provide pairs of [Routing.prefixes, URL params, User.role, expected boolean result,
	 * PHPUnit assertion message] to testIsAuthorized().
	 *
	 * @return void
	 */
	public function provideIsAuthorizedArgs() {
		return array(
			array(
				array('employee', 'manager', 'api'),
				'manager',
				null,
				false,
				'Manager prefixed route should fail for empty role.',
			),
			array(
				array('employee', 'manager', 'api'),
				'employee',
				null,
				false,
				'Employee prefixed route should fail for empty role.',
			),
			array(
				array('employee', 'manager', 'api'),
				'api',
				null,
				false,
				'Api prefixed route should fail for empty role.',
			),
			array(
				array('employee', 'manager', 'api'),
				null,
				'manager',
				false,
				'Non-prefixed route should fail for manager.',
			),
			array(
				array('employee', 'manager', 'api'),
				null,
				'employee',
				false,
				'Non-prefixed route should fail for employee.',
			),
			array(
				array('employee', 'manager', 'api'),
				null,
				'api',
				false,
				'Non-prefixed route should fail for api users.',
			),
			array(
				array('employee', 'manager', 'api'),
				null,
				null,
				true,
				'Non-prefixed route should pass for empty roles.',
			),
			array(
				array('employee', 'manager', 'api'),
				'manager',
				'employee',
				false,
				'Manager prefixed route should fail for employee.',
			),
			array(
				array('employee', 'manager', 'api'),
				'employee',
				'manager',
				false,
				'Employee prefixed route should fail for manager.',
			),
			array(
				array('employee', 'manager', 'api'),
				'employee',
				'employee',
				true,
				'Employee prefixed route should pass for employee.',
			),
			array(
				array('employee', 'manager', 'api'),
				'manager',
				'manager',
				true,
				'Manager prefixed route should pass for manager.',
			),
			array(
				array('employee', 'manager', 'api'),
				'api',
				'api',
				true,
				'Api prefixed route should pass for api user.',
			),
			array(
				array('employee', 'manager', 'api'),
				'api',
				'employee',
				false,
				'Api prefixed route should pass for employee.',
			),
			array(
				array('employee', 'manager', 'api'),
				'api',
				'manager',
				false,
				'Api prefixed route should pass for manager.',
			),
		);
	}

	/**
	 * Confirm that routes with various prefixes are allowed or blocked
	 * appropriately.
	 *
	 * @dataProvider provideIsAuthorizedArgs
	 * @param	array	$prefixes	Array of routing prefixes to write into Configure before the test.
	 * @param	string	$prefix		String prefix of the CakeRequest.
	 * @param	string	$role		The currently-logged-in user's `role` value.
	 * @param	bool	$expected	Whether the test should return true or false.
	 * @param	string	$msg		The message to echo if the assertion fails.
	 * @return	void
	 */
	public function testIsAuthorizedRoles($prefixes, $prefix, $role, $expected, $msg = '') {
		$prefixBackup = Configure::read('Routing.prefixes'); // Backup config'd prefixes.

		Configure::write('Routing.prefixes', $prefixes);
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
		));
		$App->request->prefix = $prefix;
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($role));

		$this->assertEquals($expected, $App->isAuthorized(), $msg);

		Configure::write('Routing.prefixes', $prefixBackup); // Restore config'd prefixes.
	}

	/**
	 * test emailFactory without passing a config
	 *
	 * @return void
	 */
	public function testEmailFactoryNoConfigPassed() {
		$App = $this->generate('TestApp');
		$this->assertInstanceOf("AppEmail", $App->emailFactory());
	}

	/**
	 * test emailFactory with passing a config
	 *
	 * @return void
	 */
	public function testEmailFactoryConfigPassed() {
		$App = $this->generate('TestApp');
		$this->assertInstanceOf("AppEmail", $App->emailFactory('default'));
	}

	/**
	 * Confirm that _apiBeforeFilter (called from beforeFilter) sets
	 * $this->request->data to decoded JSON if the HTTP method is post or
	 * patch.
	 *
	 * @return void
	 */
	public function testBeforeFilterApiLogicValidJson() {
		$prefix = 'api';
		$data = array(
			'data' => array(
				'foo' => 'bar',
			),
		);
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));
		$App->request->expects($this->exactly(2))
			->method('is')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('input')
			->with('json_decode', true)
			->will($this->returnValue($data));

		$App->request->prefix = $prefix;
		$App->beforeFilter();
		$this->assertEquals($data, $App->request->data);
	}

	/**
	 * Confirm that _apiBeforeFilter (called from beforeFilter) throws an
	 * exception with bad JSON.
	 *
	 * @return void
	 */
	public function testBeforeFilterApiLogicInvalidJson() {
		$prefix = 'api';
		$App = $this->generate('TestApp', array(
			'components' => array('Auth'),
			'_configureRoleAuth'
		));
		$App->request = $this->getMock('CakeRequest');
		$App->expects($this->any())
			->method('_configureRoleAuth');
		$App->Auth->staticExpects($this->any())
			->method('user')
			->with('role')
			->will($this->returnValue($prefix));
		$App->request->expects($this->exactly(2))
			->method('is')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(true));
		$App->request->expects($this->once())
			->method('input')
			->with('json_decode', true)
			->will($this->returnValue(null));

		$this->setExpectedException('BadRequestException', 'Invalid JSON');

		$App->request->prefix = $prefix;
		$App->beforeFilter();
	}

	/**
	 * Confirm that if a request is not an API JSON request the expected
	 * exception is thrown.
	 *
	 * @return void
	 */
	public function testInitJsonResponseInvalid() {
		$App = $this->generate('TestApp');
		$App->request = $this->getMockBuilder('CakeRequest')
			->setMethods(['accepts'])
			->getMock();

		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(false));

		$this->setExpectedException('BadRequestException', 'Method not implemented');
		$result = $App->initJsonResponse();
	}

	/**
	 * Confirm the expected JSON response controller properties are set.
	 *
	 * @return void
	 */
	public function testInitJsonResponseValid() {
		$App = $this->generate('TestApp');
		$App->request = $this->getMockBuilder('CakeRequest')
			->setMethods(['accepts', 'input'])
			->getMock();

		$App->request->expects($this->once())
			->method('accepts')
			->with('application/vnd.api+json')
			->will($this->returnValue(true));
		$App->request->expects($this->exactly(2))
			->method('input')
			->with('json_decode', true)
			->will($this->returnValue('canary'));

		$result = $App->initJsonResponse();
		$this->assertSame('Serializers.EmberDataSerializer', $App->viewClass);
		$this->assertSame('json', $App->renderAs);
		$this->assertSame('canary', $App->request->data);
	}

	public function testCheckAndSetDataFromJsonSuccess() {
		$App = $this->generate('TestApp');
		$App->request = $this->getMock('CakeRequest');
		$App->request->data = ['data' => [
			'type' => 'some_models',
			'attributes' => [
				'name' => 'Bob',
			],
		]];
		$result = $App->checkAndSetDataFromJson('SomeModel');
		$this->assertSame(['SomeModel' => ['name' => 'Bob']], $result);
	}

	public function testCheckAndSetDataFromJsonWithWrongDataType() {
		$this->setExpectedException(
			'BadRequestException',
			'Invalid type set, must set type SomeModel for this endpoint'
		);
		$App = $this->generate('TestApp');
		$App->request = $this->getMock('CakeRequest');
		$App->request->data = ['data' => [
			'type' => 'wrong_data_type',
			'attributes' => [
				'name' => 'Bob',
			],
		]];
		$result = $App->checkAndSetDataFromJson('SomeModel');
	}

	public function testCheckAndSetDataFromJsonWithInvalidModel() {
		$this->setExpectedException(
			'BadRequestException',
			'Invalid address model BadModel specified'
		);

		$App = $this->generate('TestApp');
		$App->checkAndSetDataFromJson('BadModel', ['GoodModel1', 'GoodMode2']);

	}

	/**
	 * Confirm if the configure var `Security.admin.ips` is empty the method
	 * returns true.
	 *
	 * @return void
	 */
	public function testRequestIsFromWarehouseValid() {
		Configure::delete('Security.admin.ips');
		$App = $this->generate('TestApp');
		$App->request = $this->getMock('CakeRequest');
		$result = $App->requestIsFromWarehouse($App->request);
		$this->assertTrue($result);
	}

	/**
	 * Confirm if the configure var `Security.admin.ips` is not empty the method
	 * returns false.
	 *
	 * @return void
	 */
	public function testRequestIsFromWarehouseInvalid() {
		Configure::write('Security.admin.ips', 'foo');
		$App = $this->generate('TestApp');
		$App->request = $this->getMock('CakeRequest');
		$result = $App->requestIsFromWarehouse($App->request);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that if a request is determined NOT to be `fromWarehouse` the
	 * expected exception is thrown.
	 *
	 * @return void
	 */
	public function testAdminBeforeFilterDenied() {
		$App = $this->generate('TestApp');
		$App->request = $this->getMockBuilder('CakeRequest')
			->setMethods(['is'])
			->getMock();

		$App->request->expects($this->once())
			->method('is')
			->with('fromWarehouse')
			->will($this->returnValue(false));

		$this->setExpectedException('ForbiddenException',  "You can't access that outside from this location.");
		$result = $App->_adminBeforeFilter();
	}

	/**
	 * Confirm if request prefix is empty the method returns false.
	 *
	 * @return void
	 */
	public function testRedirectSharedAdminRouteInvalid() {
		$App = $this->generate('TestApp');
		$App->request = $this->getMock('CakeRequest');
		$result = $App->_redirectSharedAdminRoutes();
		$this->assertFalse($result);
	}

	/**
	 * Confirm the method can return a response object if the correct criteria
	 * are met.
	 *
	 * @return void
	 */
	public function testRedirectSharedAdminRouteValid() {
		$role = 'manager';
		$App = $this->generate('TestApp', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$App->request = $this->getMock('CakeRequest');
		$App->request->prefix = 'manager';

		$App->Auth->staticExpects($this->exactly(2))
			->method('user')
			->with('role')
			->will($this->returnValue('employee'));

		$result = $App->_redirectSharedAdminRoutes();
		$this->assertInternalType('object', $result);
	}

	/**
	 * Confirm if a customer's password or password confirmation is set in
	 * the data array, it's not logged.
	 *
	 * @return void
	 */
	public function testLogBaseSerializerExceptionUnsetsPasswords() {
		$App = $this->generate('TestApp', [
			'methods' => ['log'],
		]);

		$msg = 'Error message foo bar.';
		$model = 'Customer';
		$log = 'customers';
		$data['Customer'] = [
			'customers_password' => 'secret',
			'password_confirm' => 'secret',
			'customers_email_address' => 'foo@bar.org',
		];

		$App->expects($this->once())
			->method('log')
			->with(
				$this->identicalTo(
					'BaseSerializerException: ' . $msg . ' data: {"customers_email_address":"foo@bar.org"}'
				),
				$this->identicalTo($log)
			)
			->will($this->returnValue(true));

		$result = $App->logBaseSerializerException($msg, $model, $log, $data);
		$this->assertTrue($result);
	}

	/**
	 * Confirm if a model key is not set the data array contains the expected
	 * values.
	 *
	 * @return void
	 */
	public function testLogBaseSerializerExceptionWithoutSetModel() {
		$App = $this->generate('TestApp', [
			'methods' => ['log'],
		]);

		$msg = 'Error message foo bar.';
		$model = 'Customer';
		$log = 'customers';
		$data = ['orders_id' => 'foobar'];

		$App->expects($this->once())
			->method('log')
			->with(
				$this->identicalTo('BaseSerializerException: ' . $msg . ' data: {"orders_id":"foobar"}'),
				$this->identicalTo($log)
			)
			->will($this->returnValue(true));

		$result = $App->logBaseSerializerException($msg, $model, $log, $data);
		$this->assertTrue($result);
	}
}
