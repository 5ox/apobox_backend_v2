<?php
App::uses('TrackingsController', 'Controller');

/**
 * TrackingsController Test Case
 *
 */
class TrackingsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
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
		'app.tracking',
	);

	public function setUp() {
		parent::setUp();
		$this->Tracking = ClassRegistry::init('Tracking');
	}

	public function tearDown() {
		unset($this->Tracking);
		parent::tearDown();
	}

	/**
	 * Confirm that the expected view variables are set with no query
	 *
	 * @return	void
	 */
	public function testManagerSearchNoQuery() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'trackings',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
	}

	/**
	 * Confirm that the expected view variables are set with a query
	 *
	 * @return	void
	 */
	public function testManagerSearchWithQuery() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = '/manager/scans?q=123&from_the_past=-7+days';

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
	}

	/**
	 * Confirm the method does not accept POST requests
	 *
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testManagerSearchPOST() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'trackings',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * Confirm the method does not accept PUT requests
	 *
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testManagerSearchPUT() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'trackings',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 * Confirm the method does not accept DELETE requests
	 *
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testManagerSearchDELETE() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'trackings',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Confirm that if getConditions() returns an empty array, the view var
	 * `results` is set to bool false.
	 *
	 * @return void
	 */
	public function testManagerSearchEmptyConditions() {
		$userId = 1;
		$search = 'canary';
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'trackings',
			'action' => 'search',
			'manager' => true,
			'?' => ['q' => $search],
		]);

		$Trackings = $this->generate('Trackings', [
			'methods' => ['getConditions'],
		]);
		$Trackings
			->expects($this->once())
			->method('getConditions')
			->with($search)
			->will($this->returnValue([]));

		$this->testAction($url, ['method' => 'get']);

		$this->assertArrayHasKey('results', $this->vars);
		$this->assertFalse($this->vars['results']);
	}

	/**
	 * Confirm that the expected view variables are set with no query
	 *
	 * @return	void
	 */
	public function testEmployeeSearchNoQuery() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'trackings',
			'action' => 'search',
			'employee' => true,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
	}

	/**
	 * testAddNotAuthed
	 *
	 * @return	void
	 */
	public function testAddNotAuthed() {
		$result = $this->testAction('/manager/scan', array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	public function testAddGet() {
		$userId = 1;
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan', array('method' => 'get'));

		$countAfter = $this->Tracking->find('count');
		$this->assertEquals($countBefore, $countAfter);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	public function testAddSuccess() {
		$userId = 1;
		$data = array(
			'Tracking' => array(
				'tracking_id' => '12392193849385938',
			),
		);
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan', array('data' => $data));

		$countAfter = $this->Tracking->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testAddWithComments() {
		$userId = 1;
		$trackingId = '12392193849385938';
		$comments = 'Some comments about this package and why an exception occurred.';
		$data = array(
			'add_exception' => 1,
			'Tracking' => array(
				'tracking_id' => $trackingId,
				'comments' => $comments,
			),
		);
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan', array('data' => $data));

		$newRecord = $this->Tracking->findByTrackingId($trackingId);
		$countAfter = $this->Tracking->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
		$this->assertEquals($comments, $newRecord['Tracking']['comments']);
	}

	public function testAddWithCommentsWithoutCheckboxValue() {
		$userId = 1;
		$trackingId = '12392193849385938';
		$comments = 'Some comments about this package and why an exception occurred.';
		$data = array(
			'add_exception' => 0,
			'Tracking' => array(
				'tracking_id' => $trackingId,
				'comments' => $comments,
			),
		);
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan', array('data' => $data));

		$newRecord = $this->Tracking->findByTrackingId($trackingId);
		$countAfter = $this->Tracking->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
		$this->assertEquals('', $newRecord['Tracking']['comments'], 'Tracking.comments should be empty because data[\'add_exception\'] is not 1');
	}

	public function testAddWithEmptyData() {
		$userId = 1;
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan');

		$countAfter = $this->Tracking->find('count');
		$this->assertEquals($countBefore, $countAfter);
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected.');
	}

	public function testAddDuplicate() {
		$userId = 1;
		$data = array(
			'Tracking' => array(
				'tracking_id' => '123456789',
			),
		);
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/manager/scan', array('data' => $data));

		$countAfter = $this->Tracking->find('count');
		$this->assertEquals(($countBefore), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testDeleteSuccess() {
		$userId = 1;
		$scan = '123456789';
		$this->setupManagerAuth($userId);
		$countBefore = $this->Tracking->find('count');
		$this->testAction('/manager/scan/delete/' . $scan, array('method' => 'delete'));
		$countAfter = $this->Tracking->find('count');

		$this->assertEquals(($countBefore-1), $countAfter);
	}

	/**
	 * Confirm that if the record doesn't exist, the expected exception is thrown
	 * and a redirect does not occur.
	 *
	 * @return void
	 */
	public function testDeleteNotFound() {
		$userId = 1;
		$scan = '9999999999';
		$this->setupManagerAuth($userId);

		$this->expectException(
			'NotFoundException',
			'Invalid tracking',
			'Should throw an exception if not found'
		);

		$this->testAction('/manager/scan/delete/' . $scan, ['method' => 'delete']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 * Confirm that when a delete fails due to Tracking::delete() failing
	 * the expected flash message is displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testDeleteDeleteFails() {
		$userId = 1;
		$this->setupManagerAuth($userId);

		$Trackings = $this->generate('Trackings', [
			'components' => [
				'Flash' => ['set'],
			],
			'models' => [
				'Tracking' => ['exists', 'delete'],
			],
		]);

		$Trackings->Tracking
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Trackings->Tracking
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$Trackings->Flash
			->expects($this->once())
			->method('set')
			->with('The scan could not be deleted. Please, try again.');

		$this->testAction('/manager/scan/delete/1', ['method' => 'delete']);

		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm that when a save fails a flash message is shown.
	 *
	 * @return	void
	 */
	public function testAddFailure() {
		$userId = 1;
		$data = array(
			'Tracking' => array(
				'tracking_id' => '12392193849385938',
			),
		);

		$this->setupManagerAuth($userId);

		$Trackings = $this->generate('Trackings', array(
			'models' => array(
				'Tracking' => array('save'),
			),
			'components' => array(
				'Flash',
			),
		));
		$Trackings->Tracking
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Trackings->Flash
			->expects($this->any())
			->method('set');

		$result = $this->testAction('/manager/scan', array('data' => $data));
	}

	/**
	 * Confirm that an employee level admin can add a tracking request.
	 *
	 * @return void
	 */
	public function testEmployeeAdd() {
		$userId = 2;
		$data = [
			'Tracking' => [
				'tracking_id' => '12392193849385938',
			],
		];
		$this->setupManagerAuth($userId);

		$countBefore = $this->Tracking->find('count');

		$result = $this->testAction('/employee/scan', ['data' => $data]);

		$countAfter = $this->Tracking->find('count');
		$this->assertEquals(($countBefore+1), $countAfter, 'Record should have been created');
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	/**
	 * Confirm that an exception is thrown when the tracking id is invalid
	 *
	 * @return void
	 */
	public function testEditInvalidId() {
		$userId = 1;
		$trackingId = '12392193849385938';
		$this->setupManagerAuth($userId);
		$this->setExpectedException('NotFoundException', 'Invalid scan id');
		$result = $this->testAction('/manager/scan/edit/' . $trackingId, array('method' => 'get'));
	}

	/**
	 * Confirm that the expected methods are called and that `comments` is updated.
	 *
	 * @return void
	 */
	public function testEditSuccess() {
		$userId = 1;
		$trackingId = '123456789';
		$comments = 'Some comments about this package and why an exception occurred.';
		$data = array(
			'Tracking' => array(
				'tracking_id' => $trackingId,
				'comments' => $comments,
			),
		);
		$this->setupManagerAuth($userId);

		$before = $this->Tracking->findByTrackingId($trackingId);
		$this->assertEmpty($before['Tracking']['comments']);

		$Trackings = $this->generate('Trackings', array(
			'components' => array(
				'Flash',
			),
		));
		$Trackings->Flash
			->expects($this->once())
			->method('set');

		$result = $this->testAction('/manager/scan/edit/' . $trackingId, array(
			'method' => 'post',
			'data' => $data,
		));

		$after = $this->Tracking->findByTrackingId($trackingId);
		$this->assertNotEmpty($after['Tracking']['comments']);
		$this->assertEquals($data['Tracking']['comments'], $after['Tracking']['comments']);
		$this->assertEquals($data['Tracking']['tracking_id'], $after['Tracking']['tracking_id']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	/**
	 * Confirm that the expected methods are called and that `comments` is not updated.
	 *
	 * @return void
	 */
	public function testEditFailure() {
		$userId = 1;
		$trackingId = '123456789';
		$comments = 'Some comments about this package and why an exception occurred.';
		$data = array(
			'Tracking' => array(
				'tracking_id' => $trackingId,
				'comments' => $comments,
			),
		);
		$this->setupManagerAuth($userId);

		$before = $this->Tracking->findByTrackingId($trackingId);
		$this->assertEmpty($before['Tracking']['comments']);

		$Trackings = $this->generate('Trackings', array(
			'models' => array(
				'Tracking' => array('save'),
			),
			'components' => array(
				'Flash',
			),
		));
		$Trackings->Tracking
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Trackings->Flash
			->expects($this->once())
			->method('set');

		$result = $this->testAction('/manager/scan/edit/' . $trackingId, array(
			'method' => 'post',
			'data' => $data,
		));

		$after = $this->Tracking->findByTrackingId($trackingId);
		$this->assertEmpty($after['Tracking']['comments']);
	}

	/**
	 * Confirm that when the method is requested with an HTTP GET request the
	 * save method is not called and the expected controller request data is
	 * set.
	 *
	 * @return void
	 */
	public function testEditGet() {
		$userId = 1;
		$trackingId = '123456789';
		$findResult = 'canary';
		$this->setupManagerAuth($userId);

		$Trackings = $this->generate('Trackings', [
			'models' => [
				'Tracking' => ['exists', 'find', 'save'],
			],
		]);

		$Trackings->Tracking
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Trackings->Tracking
			->expects($this->once())
			->method('find')
			->will($this->returnValue($findResult));
		$Trackings->Tracking
			->expects($this->never())
			->method('save');

		$result = $this->testAction('/manager/scan/edit/' . $trackingId, ['method' => 'get']);
		$this->assertSame(
			$findResult,
			$Trackings->data,
			'$findResult should match the set request->data'
		);
	}

	/**
	 * Confirm that the expected methods are called and that `comments` is updated
	 * when accessed by an employee level admin user.
	 *
	 * @return void
	 */
	public function testEmployeeEditSuccess() {
		$userId = 2;
		$trackingId = '123456789';
		$comments = 'Some comments about this package and why an exception occurred.';
		$data = [
			'Tracking' => [
				'tracking_id' => $trackingId,
				'comments' => $comments,
			],
		];
		$this->setupManagerAuth($userId);

		$before = $this->Tracking->findByTrackingId($trackingId);
		$this->assertEmpty($before['Tracking']['comments']);

		$Trackings = $this->generate('Trackings', [
			'components' => [
				'Flash',
			],
		]);
		$Trackings->Flash
			->expects($this->once())
			->method('set')
			->with('The scan has been updated.');

		$result = $this->testAction('/employee/scan/edit/' . $trackingId, [
			'method' => 'post',
			'data' => $data,
		]);

		$after = $this->Tracking->findByTrackingId($trackingId);
		$this->assertNotEmpty($after['Tracking']['comments']);
		$this->assertEquals($data['Tracking']['comments'], $after['Tracking']['comments']);
		$this->assertEquals($data['Tracking']['tracking_id'], $after['Tracking']['tracking_id']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
	}

	protected function setupManagerAuth($userId) {
		Configure::write('Security.admin.ips', false);

		$Requests = $this->generate('Trackings', array(
			'components' => array(
				'Auth' => array('user', 'login'),
			)
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

		return $Requests;
	}
}
