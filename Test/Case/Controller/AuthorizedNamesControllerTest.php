<?php
App::uses('AuthorizedNamesController', 'Controller');

/**
 * AuthorizedNamesController Test Case
 *
 */
class AuthorizedNamesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.admin',
		'app.authorized_name',
		'app.customer',
		'app.search_index',
	);

	/**
	 * AuthorizedName model
	 */
	public $AuthorizedName;

	public function setUp() {
		parent::setUp();
		$this->AuthorizedName = ClassRegistry::init('AuthorizedName');
	}

	public function tearDown() {
		unset($this->AuthorizedName);
		parent::tearDown();
	}

	public function testAddGet() {
		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add'
		));

		$userId = 1;

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'get'));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testAddSuccess() {
		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add'
		));

		$userId = 1;

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => 'Jane',
				'authorized_lastname' => 'Doe'
			)
		);

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('data' => $data));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore+1, $countAfter, 'Record should have been created');

		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testAddWithEmptyData() {
		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add'
		));

		$userId = 1;

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testAddWithInvalidData() {
		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add'
		));

		$userId = 1;

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => '',
				'authorized_lastname' => 'Doe'
			)
		);

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'post', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertArrayHasKey('authorized_firstname', $this->controller->AuthorizedName->validationErrors, 'First name validation should have failed');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not have redirected');
	}

	public function testEditGet() {
		$authorizedNameId = 1;
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'authorized_names_id' => $authorizedNameId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $authorizedNameId
		));

		$this->setupAuth($userId);

		$result = $this->testAction($url, array('method' => 'get'));

		$this->assertNotEmpty($this->controller->request->data['AuthorizedName']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testEditSuccess() {
		$authorizedNameId = 1;
		$userId = 1;

		$beforeCount = $this->AuthorizedName->find('count');

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'authorized_names_id' => $authorizedNameId
			)
		));

		$dataBefore = $data;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $authorizedNameId
		));

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$data['AuthorizedName']['authorized_firstname'] = 'Test';

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count');

		$dataAfter = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'authorized_names_id' => $authorizedNameId
			)
		));

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertNotEquals(
			$dataBefore['AuthorizedName']['authorized_firstname'],
			$dataAfter['AuthorizedName']['authorized_firstname'],
			'Record should have changed'
		);

		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testEditNonExistentRecord() {
		$userId = 1;
		$authorizedNameId = 1;
		$invalidAuthorizedNameId = '9999999';

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $invalidAuthorizedNameId
		));

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'authorized_names_id' => $authorizedNameId
			)
		));

		$Authorizednames = $this->setupAuth($userId);

		$this->expectException('NotFoundException');

		$this->testAction($url, array('method' => 'put', 'data' => $data));
	}

	public function testEditNotOwner() {
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id !=' => $userId
			)
		));

		$id = $data['AuthorizedName']['authorized_names_id'];

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $id
		));

		$Authorizednames = $this->setupAuth($userId);

		$this->expectException('NotFoundException');

		$this->testAction($url, array('method' => 'put', 'data' => $data));
	}

	public function testEditNotAuthed() {
		$id = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $id
		));

		$this->expectException('NotFoundException');

		$this->testAction($url, array('method' => 'get'));
	}

	public function testEditWithInvalidData() {
		$userId = 1;
		$authorizedNameId = 1;

		$countBefore = $this->AuthorizedName->find('count');

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $authorizedNameId
		));

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $userId
			)
		));

		$dataBefore = $data;

		$data['AuthorizedName']['authorized_firstname'] = '';

		$AuthorizedNames = $this->setupAuth($userId);

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count');

		$dataAfter = $data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $userId
			)
		));

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertEquals(
			$dataBefore['AuthorizedName']['authorized_firstname'],
			$dataAfter['AuthorizedName']['authorized_firstname'],
			'Record should not have changed'
		);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not have redirected');
	}

	public function testDeleteSuccess() {
		$authorizedNameId = 1;
		$userId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $authorizedNameId
		));

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$this->testAction($url, array('method' => 'delete'));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore-1, $countAfter, 'Should have deleted the record');
	}

	public function testDeleteViaGetSuccess() {
		$authorizedNameId = 1;
		$userId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $authorizedNameId
		));

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$this->testAction($url, array('method' => 'get'));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore-1, $countAfter, 'Should have deleted the record');
	}

	public function testDeleteViaPostException() {
		$authorizedNameId = 1;
		$userId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $authorizedNameId
		));

		$AuthorizedNames = $this->setupAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$this->expectException('MethodNotAllowedException');

		$this->testAction($url, array('method' => 'post'));
	}

	public function testDeleteNotOwner() {
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id !=' => $userId
			)
		));

		$authorizedNameId = $data['AuthorizedName']['authorized_names_id'];

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $authorizedNameId
		));

		$AuthorizedNames = $this->setupAuth($userId);

		$this->expectException('NotFoundException');

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Confirm that when an AuthorizedName cannot be found by $id, the expected
	 * exception is thrown.
	 *
	 * @return void
	 */
	public function testDeleteNotExists() {
		$userId = 1;

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => 999999999
		]);

		$AuthorizedNames = $this->setupAuth($userId);

		$this->expectException(
			'NotFoundException',
			'Invalid authorized name',
			'should throw an exception if $id does not exist'
		);

		$this->testAction($url, ['method' => 'delete']);
	}

	/**
	 * Confirm that when a delete fails due to AuthorizedName::delete() failing
	 * the expected flash message is displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testDeleteDeleteFails() {
		$authorizedNameId = 1;
		$userId = 1;
		$authorizedName = ['AuthorizedName' => ['customers_id' => $userId]];
		$this->setupAuth($userId);

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $authorizedNameId
		]);

		$Controller = $this->generate('AuthorizedNames', [
			'components' => [
				'Auth' => ['user', 'login'],
				'Flash' => ['set'],
			],
			'models' => [
				'AuthorizedName' => ['exists', 'read', 'delete'],
			],
		]);

		$Controller->AuthorizedName
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Controller->AuthorizedName
			->expects($this->once())
			->method('read')
			->will($this->returnValue($authorizedName));
		$Controller->AuthorizedName
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$Controller->Flash
			->expects($this->once())
			->method('set')
			->with('The authorized name could not be deleted. Please, try again.')
			->will($this->returnValue(false));

		$this->testAction($url, ['method' => 'delete']);

		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testManagerAddSuccess() {
		$userId = 1;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		));

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => 'Jane',
				'authorized_lastname' => 'Doe'
			)
		);

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$result = $this->testAction($url, array('data' => $data));

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->assertEquals($countBefore+1, $countAfter, 'Record should have been created');

		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testManagerAddGet() {
		$userId = 1;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'get'));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testManagerAddWithEmptyData() {
		$userId = 1;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'post', 'data' => array()));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testManagerAddWithInvalidData() {
		$userId = 1;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		));

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => '',
				'authorized_lastname' => 'Doe'
			)
		);

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count');

		$result = $this->testAction($url, array('method' => 'post', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count');

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertArrayHasKey('authorized_firstname', $this->controller->AuthorizedName->validationErrors, 'First name validation should have failed');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not have redirected');
	}

	/**
	 * Confirm that when the HTTP method is not GET or POST, the expected
	 * exception is thrown and a redirect does not occur.
	 *
	 * @return void
	 */
	public function testManagerAddInvalidMethod() {
		$userId = 1;
		$customerId = 1;

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		]);

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$this->expectException(
			'MethodNotAllowedException',
			'Method must be GET or POST',
			'Should throw an exception if HTTP method is not GET or POST'
		);

		$result = $this->testAction($url, ['method' => 'delete']);

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 * Confirm that when a customer does not exist when adding an AuthorizedName
	 * the expected exception is thrown and a redirect does not occur.
	 *
	 * @return void
	 */
	public function testManagerAddInvalidCustomer() {
		$userId = 1;
		$customerId = 99999999;

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true
		]);

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$this->expectException(
			'NotFoundException',
			'The requested customer was not found.',
			'Should throw an exception if the customer does not exists'
		);

		$result = $this->testAction($url, ['method' => 'get']);

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testManagerEditGet() {
		$userId = 1;
		$customerId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$this->setupAdminAuth($userId);

		$result = $this->testAction($url, array('method' => 'get'));

		$this->assertNotEmpty($this->controller->request->data['AuthorizedName']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	public function testManagerEditSuccess() {
		$customerId = 1;
		$userId = 1;

		$beforeCount = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$dataBefore = $data;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$data['AuthorizedName']['authorized_firstname'] = 'Test';

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$dataAfter = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'authorized_names_id' => $data['AuthorizedName']['authorized_names_id']
			)
		));

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');

		$this->assertNotEquals(
			$dataBefore['AuthorizedName']['authorized_firstname'],
			$dataAfter['AuthorizedName']['authorized_firstname'],
			'Record should have changed'
		);

		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testManagerEditNonExistentRecord() {
		$userId = 1;
		$customerId = 1;
		$invalidAuthorizedNameId = '9999999';

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $invalidAuthorizedNameId,
			'manager' => true
		));

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$Authorizednames = $this->setupAdminAuth($userId);

		$this->expectException('NotFoundException');

		$this->testAction($url, array('method' => 'put', 'data' => $data));
	}

	public function testManagerEditNotAuthed() {
		$id = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $id,
			'manager' => true
		));

		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should have redirected');
	}

	public function testManagerEditWithInvalidData() {
		$userId = 1;
		$customerId = 1;

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $userId
			)
		));

		$dataBefore = $data;

		$data['AuthorizedName']['authorized_firstname'] = '';

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$result = $this->testAction($url, array('method' => 'put', 'data' => $data));

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$dataAfter = $data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $userId
			)
		));

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created');
		$this->assertEquals(
			$dataBefore['AuthorizedName']['authorized_firstname'],
			$dataAfter['AuthorizedName']['authorized_firstname'],
			'Record should not have changed'
		);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not have redirected');
	}

	public function testManagerDeleteSuccess() {
		$customerId = 1;
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->testAction($url, array('method' => 'delete'));

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->assertEquals($countBefore-1, $countAfter, 'Should have deleted the record');
	}

	public function testManagerDeleteViaGetSuccess() {
		$customerId = 1;
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->testAction($url, array('method' => 'get'));

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->assertEquals($countBefore-1, $countAfter, 'Should have deleted the record');
	}

	public function testManagerDeleteViaPostException() {
		$customerId = 1;
		$userId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'manager' => true
		));

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$this->expectException('MethodNotAllowedException');

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * Confirm that when an AuthorizedName does not exist when attempting to
	 * delete it the expected exception is thrown and a redirect does not occur.
	 *
	 * @return void
	 */
	public function testManagerDeleteNotExists() {
		$customerId = 1;
		$userId = 1;

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => 99999999,
			'manager' => true
		]);

		$AuthorizedNames = $this->setupAdminAuth($userId);

		$this->expectException(
			'NotFoundException',
			'Invalid authorized name',
			'Should throw an exception if the AuthorizedName does not exist'
		);

		$this->testAction($url, ['method' => 'post']);

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 * Confirm that when a delete fails due to AuthorizedName::delete() failing
	 * the expected flash message is displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testManagerDeleteDeleteFails() {
		$customerId = 1;
		$userId = 1;
		$authorizedName = ['AuthorizedName' => ['customers_id' => $customerId]];
		$this->setupAdminAuth($userId);

		$url = Router::url([
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => 1,
			'manager' => true
		]);

		$Controller = $this->generate('AuthorizedNames', [
			'components' => [
				'Flash' => ['set'],
			],
			'models' => [
				'AuthorizedName' => ['exists', 'read', 'delete'],
			],
		]);

		$Controller->AuthorizedName
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$Controller->AuthorizedName
			->expects($this->once())
			->method('read')
			->will($this->returnValue($authorizedName));
		$Controller->AuthorizedName
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$Controller->Flash
			->expects($this->once())
			->method('set')
			->with('The authorized name could not be deleted. Please, try again.');

		$this->testAction($url, ['method' => 'delete']);

		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	public function testEmployeeCannotAddGet() {
		$userId = 2;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'employee' => true
		));

		$this->setupAdminAuth($userId);

		$this->expectException('MissingActionException');
		$result = $this->testAction($url, array('method' => 'get'));
	}

	public function testEmployeeCannotAddPost() {
		$userId = 2;
		$customerId = 1;

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'add',
			'customerId' => $customerId,
			'employee' => true
		));

		$this->setupAdminAuth($userId);

		$this->expectException('MissingActionException');
		$result = $this->testAction($url, array('method' => 'post'));
	}

	public function testEmployeeCannotEdit() {
		$userId = 2;
		$customerId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'employee' => true
		));

		$this->setupAdminAuth($userId);

		$this->expectException('MissingActionException');
		$result = $this->testAction($url, array('method' => 'get'));
	}

	public function testEmployeeCannotDelete() {
		$userId = 2;
		$customerId = 1;

		$data = $this->AuthorizedName->find('first', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$url = Router::url(array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			'id' => $data['AuthorizedName']['authorized_names_id'],
			'employee' => true
		));

		$this->setupAdminAuth($userId);

		$this->expectException('MissingActionException');
		$result = $this->testAction($url, array('method' => 'get'));
	}

	protected function setupAuth($userId) {
		$AuthorizedNames = $this->generate('AuthorizedNames', array(
			'components' => array(
				'Auth' => array('user', 'login')
			)
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

		$AuthorizedNames->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		return $AuthorizedNames;
	}

	protected function setupAdminAuth($userId) {
		Configure::write('Security.admin.ips', false);

		$AuthorizedNames = $this->generate('AuthorizedNames', array(
			'components' => array(
				'Auth' => array('user', 'login')
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

		$AuthorizedNames->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		return $AuthorizedNames;
	}

}
