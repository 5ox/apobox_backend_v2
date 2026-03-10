<?php
App::uses('CustomPackageRequestsController', 'Controller');

/**
 * TestCustomPackageRequests - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestCustomPackageRequestsController extends CustomPackageRequestsController {
	public function _getConditions($query, $timeframe, $showStatus) {
		return parent::_getConditions($query, $timeframe, $showStatus);
	}
	public function _userIsOwner($request) {
		return parent::_userIsOwner($request);
	}
}

/**
 * CustomPackageRequestsController Test Case
 *
 */
class CustomPackageRequestsControllerTest extends ControllerTestCase {

	public $fixtures = array(
		'app.address',
		'app.admin',
		'app.custom_order',
		'app.customer',
		'app.zone',
		'app.order',
		'app.order_status',
		'app.order_status_history',
		'app.password_request',
		'app.authorized_name',
		'app.search_index',
	);

	/**
	 * CustomPackageRequest model
	 */
	public $CustomPackageRequest;

	public function setUp() {
		parent::setUp();
		$this->CustomPackageRequest = ClassRegistry::init('CustomPackageRequest');
	}

	public function tearDown() {
		unset($this->CustomPackageRequest);
		parent::tearDown();
	}

	public function testAddNotAuthed() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	public function testManagerIndexNoQuery() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = '/manager/requests';
		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('requests', $this->vars);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertEquals('-60 days', $this->vars['fromThePast']);
	}

	public function testManagerIndexQuery() {
		$userId = 1;
		$searchString = '12392';
		$timeframe = '2015-01-01';
		$this->setupManagerAuth($userId);

		$url = '/manager/requests?q=' . $searchString . '&from_the_past=' . $timeframe;
		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('requests', $this->vars);
		$this->assertGreaterThan(0, $this->vars['requests']);
	}

	public function testEmployeeIndexNoQuery() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = '/employee/requests';
		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('requests', $this->vars);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertEquals('-60 days', $this->vars['fromThePast']);
	}

	public function testEmployeeIndexQuery() {
		$userId = 1;
		$searchString = '12392';
		$timeframe = '2015-01-01';
		$this->setupManagerAuth($userId);

		$url = '/employee/requests?q=' . $searchString . '&from_the_past=' . $timeframe;
		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('requests', $this->vars);
		$this->assertGreaterThan(0, $this->vars['requests']);
	}

	public function testAddGet() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 1;

		$CustomPackageRequests = $this->setupAuth($userId);
		$requestCountBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url, array('method' => 'get'));

		$requestCountAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($requestCountBefore, $requestCountAfter);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');

	}

	public function testAddSuccess() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 2;
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '11112222333344445555',
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'priority',
				'instructions' => '',
			),
		);

		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url, array('data' => $data));

		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testAddWithNonStandardBillingId() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 2;
		$this->CustomPackageRequest->Customer->save(
			['Customer' => [
				'customers_id' => $userId,
				'billing_id' => 'ABC1234',
			]],
			false,
			['billing_id']
		);
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '11112222333344445555',
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'priority',
				'instructions' => '',
			),
		);

		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url, array('data' => $data));

		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
	}

	public function testAddWithEmptyData() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 1;

		$CustomPackageRequests = $this->setupAuth($userId);
		$requestCountBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url);

		$requestCountAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($requestCountBefore, $requestCountAfter);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Test adding with bad input will fail and not redirect
	 */
	public function testAddWithBadData() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 1;
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '12392193849385938',
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'BAD INPUT',
				'instructions' => '',
			),
		);

		$CustomPackageRequests = $this->setupAuth($userId);
		$requestCountBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url, array('method' => 'post', 'data' => $data));

		$requestCountAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($requestCountBefore, $requestCountAfter, 'Record should not have been created');
		$this->assertArrayHasKey('mail_class', $this->controller->CustomPackageRequest->validationErrors, 'Mail class validation should have failed');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	public function testEditGet() {
		$orderId = 1;
		$userId = 2;
		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$orderId
		));

		$this->setupAuth($userId);
		$result = $this->testAction($url, array(
			'method' => 'get',
		));

		$this->assertNotEmpty($this->controller->request->data['CustomPackageRequest']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 * Confirm that when a custom request that is already associated with an order
	 * is edited, only the `instructions` field can be updated.
	 *
	 * @return void
	 */
	public function testEditAssociatedWithOrder() {
		$orderId = 1;
		$userId = 2;
		$countBefore = $this->CustomPackageRequest->find('count');
		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));
		$dataBefore = $data;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$orderId
		));

		$CustomPackageRequests = $this->setupAuth($userId);

		$data['CustomPackageRequest'] = [
			'instructions' => 'hi there',
			'mail_class' => 'foo',
		];
		$result = $this->testAction($url, array(
			'method' => 'put',
			'data' => $data
		));

		$countAfter = $this->CustomPackageRequest->find('count');
		$dataAfter = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));
		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertNotEquals(
			$dataBefore['CustomPackageRequest']['instructions'],
			$dataAfter['CustomPackageRequest']['instructions'],
			'Record should have changed'
		);
		$this->assertEquals(
			$dataBefore['CustomPackageRequest']['mail_class'],
			$dataAfter['CustomPackageRequest']['mail_class'],
			'Record should NOT have changed'
		);
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	/**
	 * @expectedException NotFoundException
	 */
	public function testEditNonExistentRecord() {
		$userId = 2;
		$data = $this->CustomPackageRequest->find('first');
		$id = '999999999';
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$id
		));

		$CustomPackageRequests = $this->setupAuth($userId);

		$result = $this->testAction($url, array(
			'method' => 'put',
			'data' => $data
		));
	}

	/**
	 * @expectedException NotFoundException
	 */
	public function testEditNotOwner() {
		$userId = 2;
		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('customers_id !=' => $userId)
		));
		$id = $data['CustomPackageRequest']['custom_orders_id'];
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$id
		));

		$CustomPackageRequests = $this->setupAuth($userId);

		$result = $this->testAction($url . $id, array(
			'method' => 'put',
			'data' => $data
		));
	}

	/**
	 * This will actually redirect to login, but since the code in the method
	 * still runs during tests, _userIsOwner() returns false and throws an
	 * exception.
	 *
	 * @expectedException NotFoundException
	 */
	public function testEditNotAuthed() {
		$id = 1;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$id
		));
		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that other fields besides `instructions` can be updated in a custom
	 * request if it's not associated with an order yet.
	 *
	 * @return void
	 */
	public function testEditNotAssociatedWithOrder() {
		$orderId = 5;
		$userId = 1;
		$countBefore = $this->CustomPackageRequest->find('count');
		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));
		$dataBefore = $data;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$orderId
		));

		$CustomPackageRequests = $this->setupAuth($userId);
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set');

		$data['CustomPackageRequest']['mail_class'] = 'parcel';
		$result = $this->testAction($url, array(
			'method' => 'put',
			'data' => $data
		));

		$countAfter = $this->CustomPackageRequest->find('count');
		$dataAfter = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));
		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertNotEquals(
			$dataBefore['CustomPackageRequest']['mail_class'],
			$dataAfter['CustomPackageRequest']['mail_class'],
			'Record should have changed'
		);
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	/**
	 * @expectedException MethodNotAllowedException
	 */
	public function testEditPostException() {
		$id = 1;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$id
		));
		$userId = 2;
		$CustomPackageRequests = $this->setupAuth($userId);
		$this->testAction($url, array('method' => 'post'));
	}

	public function testDeleteSuccess() {
		$id = 1;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'delete',
			$id
		));
		$userId = 2;
		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');
		$this->testAction($url, array('method' => 'delete'));
		$countAfter = $this->CustomPackageRequest->find('count');

		$this->assertEquals(($countBefore-1), $countAfter);
	}

	/**
	 * @expectedException NotFoundException
	 */
	public function testDeleteNotOwner() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'delete'
		));
		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');
		$this->testAction($url, array('method' => 'delete'));
		$countAfter = $this->CustomPackageRequest->find('count');

		$this->assertEquals($countBefore, $countAfter);
	}

	/**
	 * @expectedException MethodNotAllowedException
	 */
	public function testDeletePostException() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'delete'
		));
		$CustomPackageRequests = $this->setupAuth($userId);
		$this->testAction($url, array('method' => 'post'));
	}

	protected function setupAuth($userId) {
		$CustomPackageRequests = $this->generate('CustomPackageRequests', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Flash' => array('set'),
			),
		));

		$user = ClassRegistry::init('Customer')->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$user = $user['Customer'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$CustomPackageRequests->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		return $CustomPackageRequests;
	}

	protected function setupManagerAuth($userId) {
		Configure::write('Security.admin.ips', false);

		$Requests = $this->generate('CustomPackageRequests', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Flash' => array('set'),
			),
			'models' => array(
				'CustomPackageRequest' => array(
					'create',
					'save',
				),
			),
			'methods' => array(
				'_userIsOwner',
			),
		));

		$user = ClassRegistry::init('Admin')->find('first', array(
			'contain' => array(),
			'conditions' => array('id' => $userId),
		));
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			return (!$field) ? $user : $user[$field];
		};

		$Requests->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Requests
			->expects($this->any())
			->method('_userIsOwner')
			->will($this->returnValue(false));

		return $Requests;
	}

	/**
	 * Confirm that when the $showStatus arg is passed to _getConditions the
	 * expected query fragment is added to the result.
	 *
	 * @return void
	 */
	public function testGetConditionsShowStatus() {
		$status = 'shipped';
		$Controller = new TestCustomPackageRequestsController();
		$result = $Controller->_getConditions(array(), array(), 'shipped');
		$this->assertArrayHasKey('AND', $result[0]);
		$this->assertArrayHasKey('CustomPackageRequest.package_status', $result[0]['AND']);
		$this->assertSame($status, $result[0]['AND']['CustomPackageRequest.package_status']);
	}

	/**
	 * Confirm that the expected view vars are set when a manager visits the
	 * add page.
	 *
	 * @return void
	 */
	public function testManagerAddGet() {
		$url = '/manager/customer/1/request/add';
		$userId = 1;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayHasKey('packageStatuses', $this->vars);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when saving a custom package request fails a flash message
	 * is displayed and a redirect does happens.
	 *
	 * @return void
	 */
	public function testManagerAddPostSuccess() {
		$url = '/manager/customer/1/request/add';
		$userId = 1;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('create')
			->will($this->returnValue(true));
		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set');

		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));
		$this->assertArrayHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when saving a custom package request fails a flash message
	 * is displayed and a redirect does not happen.
	 *
	 * @return void
	 */
	public function testManagerAddPostFail() {
		$url = [
			'controller' => 'custom_package_requests',
			'action' => 'add',
			'manager' => true,
			'customerId' => 1,
		];
		$userId = 1;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('create')
			->will($this->returnValue(true));
		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set');

		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when a new record with an existing tracking id is attempted
	 * to be saved the save fails and the customer is not redirected.
	 *
	 * @return void
	 */
	public function testAddFailWithDuplicate() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 2;
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '12392193849385938',
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'priority',
				'instructions' => '',
			),
		);

		$CustomPackageRequests = $this->setupAuth($userId);
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set');
		$countBefore = $this->CustomPackageRequest->find('count');

		$result = $this->testAction($url, array('data' => $data));

		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should have not redirected');
	}

	/**
	 * Confirm that when a new record with an existing tracking id is attempted
	 * to be saved the save fails and the manager is not redirected.
	 *
	 * @return void
	 */
	public function testManagerAddPostFailDuplicate() {
		$url = [
			'controller' => 'custom_package_requests',
			'action' => 'add',
			'manager' => true,
			'customerId' => 1,
		];
		$userId = 1;
		$CustomPackageRequests = $this->setupManagerAuth($userId);
		$data = array(
			'CustomPackageRequest' => array(
				'customers_id' => 1,
				'tracking_id' => '12392193849385938',
				'package_repack' => 'yes',
				'insurance_coverage' => 12.5,
				'mail_class' => 'priority',
				'instructions' => '',
			),
		);
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set');

		$countBefore = $this->CustomPackageRequest->find('count');
		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));
		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should have not redirected');
	}

	/**
	 * Confirm the expected exception is thrown if an invalid customer ID is used
	 * when adding a custom package request.
	 *
	 * @return void
	 */
	public function testManagerAddGetInvalidCustomer() {
		$url = [
			'controller' => 'custom_package_requests',
			'action' => 'add',
			'manager' => true,
			'customerId' => 999999,
		];
		$userId = 1;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$this->setExpectedException('NotFoundException', 'The customer for the package request was not found.');
		$result = $this->testAction($url, array('method' => 'get'));
	}

	/**
	 * Confirm the record can't be saved due to the tracking_id already existing.
	 *
	 * @return void
	 */
	public function testAddFailExistingCustomRequest() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 1;
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '1234567',
			),
		);
		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');
		$result = $this->testAction($url, array('data' => $data));
		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($countBefore, $countAfter);
		$this->assertArrayHasKey('tracking_id', $this->controller->CustomPackageRequest->validationErrors, 'Tracking id validation should have failed');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * testAddFailExistingOrder
	 *
	 * @return void
	 */
	public function testAddFailExistingOrder() {
		$url = Router::url(array(
			'controller' => 'custom_package_requests',
			'action' => 'add'
		));
		$userId = 1;
		$data = array(
			'CustomPackageRequest' => array(
				'tracking_id' => '1234567890',
			),
		);
		$CustomPackageRequests = $this->setupAuth($userId);
		$countBefore = $this->CustomPackageRequest->find('count');
		$result = $this->testAction($url, array('data' => $data));
		$countAfter = $this->CustomPackageRequest->find('count');
		$this->assertEquals($countBefore, $countAfter);
		$this->assertArrayHasKey('tracking_id', $this->controller->CustomPackageRequest->validationErrors, 'Tracking id validation should have failed');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that the expected view vars are set when an employee visits the
	 * add page.
	 *
	 * @return void
	 */
	public function testEmployeeAddGet() {
		$url = '/employee/customer/1/request/add';
		$userId = 2;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayHasKey('packageStatuses', $this->vars);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when saving a custom package request fails a flash message
	 * is displayed and a redirect does happens.
	 *
	 * @return void
	 */
	public function testEmployeeAddPostSuccess() {
		$url = '/employee/customer/1/request/add';
		$userId = 2;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('create')
			->will($this->returnValue(true));
		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('The custom package request has been created.');

		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));
		$this->assertArrayHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when a manager edits a custom request and save fails, a flash
	 * message is displayed and a redirect does not happen.
	 *
	 * @return void
	 */
	public function testManagerEditSaveFails() {
		$orderId = 1;
		$userId = 1;
		$url = '/manager/requests/edit/' . $orderId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('Custom package request could not be updated.');

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when a manager edits a custom request successfully a flash message
	 * is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testManagerEditSaveSuccess() {
		$orderId = 1;
		$userId = 1;
		$url = '/manager/requests/edit/' . $orderId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('Custom package request was successfully updated!');

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertStringEndsWith('/manager/requests', $this->headers['Location']);
	}

	/**
	 * Confirm that when a manager edits a custom request with a customer id
	 * successfully a flash message is displayed and a redirect to the
	 * expected location happens.
	 *
	 * @return void
	 */
	public function testManagerEditSaveSuccessWithCustomerId() {
		$orderId = 1;
		$userId = 1;
		$customerId = 2;
		$url = '/manager/requests/edit/' . $orderId . '/' . $customerId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$data = $this->CustomPackageRequest->find('first', [
			'conditions' => ['custom_orders_id' => $orderId]
		]);

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('Custom package request was successfully updated!');

		$result = $this->testAction($url, ['method' => 'put', 'data' => $data]);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertStringEndsWith('/manager/orders/add/' . $customerId, $this->headers['Location']);
	}

	/**
	 * Confirm that when an employee edits a custom request and save fails, a flash
	 * message is displayed and a redirect does not happen.
	 *
	 * @return void
	 */
	public function testEmployeeEditSaveFails() {
		$orderId = 1;
		$userId = 2;
		$url = '/employee/requests/edit/' . $orderId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('Custom package request could not be updated.');

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 * Confirm that when an employee edits a custom request successfully a flash message
	 * is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testEmployeeEditSaveSuccess() {
		$orderId = 1;
		$userId = 2;
		$url = '/employee/requests/edit/' . $orderId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$data = $this->CustomPackageRequest->find('first', array(
			'conditions' => array('custom_orders_id' => $orderId)
		));

		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('Custom package request was successfully updated!');

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that when a manager deletes a custom request the record is removed
	 * and a redirect happens.
	 *
	 * @return void
	 */
	public function testManagerDeleteSuccess() {
		$orderId = 1;
		$userId = 1;
		$url = '/manager/requests/delete/' . $orderId;
		$CustomPackageRequests = $this->setupManagerAuth($userId);

		$countBefore = $this->CustomPackageRequest->find('count');
		$this->testAction($url, array('method' => 'delete'));
		$countAfter = $this->CustomPackageRequest->find('count');

		$this->assertEquals(($countBefore - 1), $countAfter);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that when a custom request is not associated with the expected order,
	 * a flash message is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testManagerDeleteNotInOrder() {
		Configure::write('Security.admin.ips', false);
		$orderId = 1;
		$userId = 1;
		$url = '/manager/requests/delete/' . $orderId;
		$CustomPackageRequests = $this->generate('CustomPackageRequests', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Flash' => array('set'),
			),
			'models' => array(
				'CustomPackageRequest' => array(
					'create',
					'save',
					'trackingIdNotInOrder',
				),
			),
			'methods' => array(
				'_userIsOwner',
			),
		));

		$user = ClassRegistry::init('Admin')->find('first', array(
			'contain' => array(),
			'conditions' => array('id' => $userId),
		));
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			return (!$field) ? $user : $user[$field];
		};

		$CustomPackageRequests->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$CustomPackageRequests
			->expects($this->any())
			->method('_userIsOwner')
			->will($this->returnValue(false));
		$CustomPackageRequests->CustomPackageRequest
			->expects($this->once())
			->method('trackingIdNotInOrder')
			->will($this->returnValue(false));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('The custom package request could not be deleted because it\'s associated with an order.');

		$this->testAction($url, array('method' => 'delete'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that when a manager attempts to delete a custom request and the
	 * delete fails, the expected flash message is displayed and a redirect
	 * happens.
	 *
	 * @return void
	 */
	public function testManagerDeleteFails() {
		Configure::write('Security.admin.ips', false);
		$orderId = 1;
		$userId = 1;
		$url = '/manager/requests/delete/' . $orderId;
		$CustomPackageRequests = $this->generate('CustomPackageRequests', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Flash' => array('set'),
			),
			'models' => array(
				'CustomPackageRequest' => array(
					'delete',
				),
			),
			'methods' => array(
				'_userIsOwner',
			),
		));

		$user = ClassRegistry::init('Admin')->find('first', array(
			'contain' => array(),
			'conditions' => array('id' => $userId),
		));
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			return (!$field) ? $user : $user[$field];
		};

		$CustomPackageRequests->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$CustomPackageRequests
			->expects($this->any())
			->method('delete')
			->will($this->returnValue(false));
		$CustomPackageRequests->Flash
			->expects($this->once())
			->method('set')
			->with('The custom package request could not be deleted.');

		$this->testAction($url, array('method' => 'delete'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that if the `customers_id` key is not set in the $request param
	 * the method returns false.
	 *
	 * @return void
	 */
	public function testUserIsOwnerMissingId() {
		$request = [];
		$Controller = new TestCustomPackageRequestsController();
		$result = $Controller->_userIsOwner($request);
		$this->assertFalse($result);
	}

	/**
	 * Confirm if the Auth::user call does not return a user the method returns
	 * false.
	 *
	 * @return void
	 */
	public function testUserIsOwnerAuthFails() {
		$request['CustomPackageRequest']['customers_id'] = 1;
		$Controller = $this->generate('TestCustomPackageRequests', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$Controller->Auth->staticExpects($this->once())
			->method('user')
			->with('customers_id')
			->will($this->returnValue(false));

		$result = $Controller->_userIsOwner($request);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that if the supplied `customers_id` param matches the Auth::user
	 * result, the method returns true.
	 *
	 * @return void
	 */
	public function testUserIsOwnerSuccess() {
		$customerId = 1;
		$request['CustomPackageRequest']['customers_id'] = $customerId;
		$Controller = $this->generate('TestCustomPackageRequests', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$Controller->Auth->staticExpects($this->exactly(2))
			->method('user')
			->with('customers_id')
			->will($this->returnValue($customerId));

		$result = $Controller->_userIsOwner($request);
		$this->assertTrue($result);
	}
}
