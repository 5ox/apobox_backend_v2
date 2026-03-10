<?php
App::uses('CustomersController', 'Controller');
App::uses('Router', 'Routing');

/**
 * TestCustomersController to access protected methods for direct testing.
 */
class TestCustomersController extends CustomersController {
	public function autoWrapFullnameOrEmail($search) {
		return parent::autoWrapFullnameOrEmail($search);
	}

	public function _sendDefaultEmail($id, $subject, $body) {
		return parent::_sendDefaultEmail($id, $subject, $body);
	}

	public function checkHash() {
		return parent::checkHash();
	}
}

/**
 * CustomersController Test Case
 *
 */
class CustomersControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.address',
		'app.affiliate_link',
		'app.country',
		'app.customer',
		'app.customers_info',
		'app.custom_order',
		'app.zone',
		'app.order',
		'app.order_status',
		'app.order_status_history',
		'app.order_total',
		'app.password_request',
		'app.admin',
		'app.insurance',
		'app.authorized_name',
		'app.customer_reminder',
		'app.search_index',
		'app.queued_task',
	);

	/**
	 * Set debug to 0 so DebugKit tests are not run.
	 *
	 * @return void
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 *
	 * @return	void
	 */
	public function testLogin() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'login',
		));
		$Customers = $this->generate('Customers', [
			'components' => [
				'Auth' => ['login'],
				'Activity' => ['record'],
			]
		]);
		$Customers->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(true));
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('login');

		$result = $this->testAction($url, array('method' => 'post'));

		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testLoginGET() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'login',
		));
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'login')
			)
		));
		$Customers->Auth
			->expects($this->never())
			->method('login');

		$result = $this->testAction($url, array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 * @return	void
	 */
	public function testLoginAuthFails() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'login',
		));
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'login')
			)
		));
		$Customers->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(false));

		$result = $this->testAction($url, array('method' => 'post'));
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');

	}

	/**
	 * Test that json login success returns a json customer array.
	 */
	public function testLoginJsonSuccess() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'login',
		));
		$customer = [
			'customers_id' => '123',
			'customers_email_address' => 'test.user99@example.com',
			'customers_password' => 'password',
		];
		$data = json_encode([
			'data' => [
				'type' => 'customers',
				'attributes' => $customer,
			],
		]);
		$valueMap = [
			['role', false],
			[null, $customer],
		];
		$Customers = $this->generate('Customers', [
			'components' => [
				'Auth' => ['user', 'login'],
				'Activity' => ['record'],
			]
		]);
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValueMap($valueMap));
		$Customers->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(true));
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('login');

		$result = $this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
		$result = json_decode($result,1);

		$this->assertSame('customers', $result['data']['type']);
		$this->assertNotEmpty($result['data']['attributes']['customers_email_address']);
	}

	/**
	 * Test that json login failure will throw an exception.
	 *
	 * @expectedException BaseSerializerException
	 */
	public function testLoginJsonFails() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'login',
		]);
		$data = json_encode([
			'data' => [
				'type' => 'customers',
			],
		]);
		$Customers = $this->generate('Customers', [
			'components' => [
				'Auth' => ['login']
			],
			'methods' => [
				'logBaseSerializerException',
			],
		]);
		$Customers->Auth
			->expects($this->once())
			->method('login')
			->will($this->returnValue(false));
		$Customers->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->identicalTo('Your email address or password was incorrect.'),
				$this->identicalTo('Customer'),
				$this->identicalTo('login-error'),
				$this->anything()
			);

		$this->testAction($url, [
			'data' => $data,
			'method' => 'post'
		]);
	}

	/**
	 *
	 * @return	void
	 */
	public function testLogout() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'logout',
		));
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('logout')
			)
		));
		$Customers->Auth
			->expects($this->once())
			->method('logout')
			->will($this->returnValue(true));

		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testLogoutAuthFails() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'logout',
		));
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'logout')
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
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Customers->Auth
			->expects($this->once())
			->method('logout')
			->will($this->returnValue(false));

		$result = $this->testAction($url, array('method' => 'get'));
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}
	/**
	 * Confirm that the expected view vars are set.
	 *
	 * @return	void
	 */
	public function testAccountPage() {
		$userId = 1;

		$Customers = $this->setupAuth($userId);
		$result = $this->testAction('/account');

		$this->assertEqual($userId, $this->vars['customer']['Customer']['customers_id']);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayHasKey('orders', $this->vars);
		$this->assertArrayHasKey('requests', $this->vars);
		$this->assertArrayHasKey('showViewAllLink', $this->vars);
		$this->assertArrayHasKey('awaitingPayments', $this->vars);
		$this->assertArrayHasKey('AuthorizedName', $this->vars['customer'], 'Customer should contain AuthorizedName');
		$this->assertTrue((count($this->vars['orders']) < 6), 'Should have no more than 5 orders');
		$this->assertGreaterThan(
			strtotime($this->vars['orders'][3]['Order']['date_purchased']),
			strtotime($this->vars['orders'][0]['Order']['date_purchased']),
			'Orders should be ordered newest first.'
		);
		$this->assertContains(8, Hash::extract($this->vars['orders'], '{n}.Order.orders_id'));
		$this->assertNotContains(2, Hash::extract($this->vars['orders'], '{n}.Order.orders_id'));
		$this->assertContains(3, Hash::extract($this->vars['requests'], '{n}.CustomPackageRequest.custom_orders_id'));
		$this->assertNotContains(1, Hash::extract($this->vars['requests'], '{n}.CustomPackageRequest.custom_orders_id'));
		$this->assertInternalType('bool', $this->vars['showViewAllLink']);
		$this->assertArrayHasKey('OrderTotal', $this->vars['orders'][0], '$orders should contain at least one OrderTotal.');
		$this->assertArrayHasKey('CustomPackageRequest', $this->vars['orders'][0], '$orders should contain at least one CustomPackageRequest.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['orders'][0], '$orders should contain at least one OrderStatus.');
		$this->assertArrayHasKey('OrderStatusHistory', $this->vars['orders'][0], '$orders should contain at least one OrderStatusHistory.');
		$this->assertArrayHasKey('insuranceFee', $this->vars);
		$this->assertSame('1.75', $this->vars['insuranceFee']);
	}

	public function testAccountRendered() {
		$userId = 2;
		$Customers = $this->setupAuth($userId);
		$result = $this->testAction('/account', array('return' => 'view'));
		$this->assertContains('tracking_id_with_no_order', $result, 'Pending custom package requests should be displayed');
	}

	/**
	 * Test account page will redirect to account_incomplete if user does not
	 * have a default address.
	 *
	 * @return	void
	 */
	public function testAccountRedirectsIfIncomplete() {
		$userId = 8;
		$Customers = $this->setupAuth($userId);
		$result = $this->testAction('/account');
		$this->assertStringEndsWith('/account-incomplete', $this->headers['Location']);
	}

	/**
	 * Test not logged in.
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testAccountNotAuth() {
		$result = $this->testAction('/account');
	}

	/**
	 * Test that account_incomplete will render for incomplete users.
	 */
	public function testAccountIncompleteRendered() {
		$userId = 8;
		$Customers = $this->setupAuth($userId);
		$result = $this->testAction('/customers/account-incomplete', ['method' => 'get']);
		$this->assertFalse(isset($this->headers['Location']));
		$this->assertArrayHasKey('Address', $Customers->request->data);
		$this->assertArrayHasKey('entry_firstname', $Customers->request->data['Address']);
	}

	/**
	 * Test that account_incomplete will render for incomplete users.
	 */
	public function testAccountIncompletePostSuccess() {
		$userId = 8;
		$Customers = $this->setupAuth($userId);
		$newCity = 'Newtown';
		$data = array(
			'Address' => array(
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => $newCity,
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
		);

		$beforeRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$beforeCount = $Customers->Customer->Address->find('count');

		$result = $this->testAction('/customers/account-incomplete', ['data' => $data]);

		$afterCount = $Customers->Customer->Address->find('count');
		$afterRecord = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));

		$this->assertEquals(($beforeCount + 1), $afterCount,
			'Exactly one record should be created, ' . ($afterCount - $beforeCount) . ' were created.'
		);
		$this->assertSame(null, $beforeRecord['Customer']['customers_default_address_id'],
			'customer previous default address should be null'
		);
		$this->assertSame('0', $beforeRecord['Customer']['customers_shipping_address_id'],
			'customer previous shipping address should be 0'
		);
		$this->assertGreaterThan(0, $afterRecord['Customer']['customers_default_address_id']);
		$this->assertGreaterThan(0, $afterRecord['Customer']['customers_shipping_address_id']);
		$this->assertNotEqual(null, $afterRecord['Customer']['customers_default_address_id'],
			'customer new default address should not be null'
		);
		$this->assertEquals($newCity, $afterRecord['DefaultAddress']['entry_city'],
			'customer new default city should be ' . $newCity
		);
	}

	/**
	 * Test that account_incomplete will post an error if the address save
	 * fails and will not attempt to update the customer.
	 */
	public function testAccountIncompleteAddressSaveFails() {
		$userId = 8;
		$newCity = '';
		$data = array(
			'Address' => array(
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => $newCity,
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
		);

		$mockOptions = [
			'components' => [
				'Flash' => ['set'],
			],
		];
		$Customers = $this->setupAuth($userId, $mockOptions);
		$Customers->Flash
			->expects($this->once())
			->method('set')
			->with('There were errors with your input, please try again.');

		$beforeRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$beforeCount = $Customers->Customer->Address->find('count');

		$result = $this->testAction('/customers/account-incomplete', ['data' => $data]);

		$afterCount = $Customers->Customer->Address->find('count');
		$afterRecord = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));

		$this->assertEquals(($beforeCount), $afterCount,
			'No address records should be created.'
		);
		$this->assertSame(null, $beforeRecord['Customer']['customers_default_address_id'],
			'customer previous default address should be null'
		);
		$this->assertSame(null, $afterRecord['Customer']['customers_default_address_id'],
			'customer default address should still be null'
		);
	}

	/**
	 * Test that account_incomplete will post an error if the customer save
	 * fails.
	 */
	public function testAccountIncompleteCustomerSaveFails() {
		$userId = 8;
		$newCity = 'Newtown';
		$data = array(
			'Address' => array(
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => $newCity,
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
		);

		$mockOptions = [
			'components' => [
				'Flash' => ['set'],
			],
		];
		$Customers = $this->setupAuth($userId, $mockOptions);
		$Customers->Customer = $this->getMockForModel('Customer', array('save'));
		$Customers->Customer
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Customers->Flash
			->expects($this->once())
			->method('set')
			->with('There were errors with your input, please try again.');

		$beforeRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$beforeCount = $Customers->Customer->Address->find('count');

		$result = $this->testAction('/customers/account-incomplete', ['data' => $data]);

		$afterCount = $Customers->Customer->Address->find('count');
		$afterRecord = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));

		$this->assertEquals(($beforeCount), $afterCount,
			'No address records should be created.'
		);
		$this->assertSame(null, $beforeRecord['Customer']['customers_default_address_id'],
			'customer previous default address should be null'
		);
		$this->assertSame(null, $afterRecord['Customer']['customers_default_address_id'],
			'customer default address should still be null'
		);
	}

	/**
	 * Test account_incomplete page will redirect to account if user has a
	 * default address.
	 *
	 * @return	void
	 */
	public function testAccountIncompleteRedirectsIfComplete() {
		$userId = 1;
		$Customers = $this->setupAuth($userId);
		$result = $this->testAction('/customers/account-incomplete');
		$this->assertStringEndsWith('/account', $this->headers['Location']);
	}

	/**
	 * Testing user account is "complete"
	 *
	 * @return	void
	 */
	public function testAlmostFinishedNotNeeded() {
		$userId = 1;
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));

		$Customers = $this->setupAuth($userId);
		$result = $this->testAction($url);

		$this->assertStringEndsWith('/account', $this->headers['Location']);
	}

	public function testAlmostFinishedInitialViewIncomplete() {
		$userId = 2;
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));
		$Customers = $this->setupAuth($userId);

		$result = $this->testAction($url, array('method' => 'get'));

		$this->assertEqual($userId, $this->vars['customer']['customers_id'], 'customers_id is empty or does not match');
		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('Customer', $Customers->request->data, 'Customer array not set in request data');
		$this->assertArrayHasKey('Address', $Customers->request->data, 'Address array not set in request data');
	}

	public function testAlmostFinishedPostExisting() {
		$userId = 2;
		$addressId = 3;
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));
		$data = array(
			'Customer' => array(
				'customers_default_address_id' => $addressId,
				'customers_id' => $userId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'The Tester',
				'cc_number' => '4111111111111111',
				'cc_expires_month' => '3',
				'cc_expires_year' => date('Y') + 1,
				'cc_cvv' => '123'
			),
			'CustomersInfo' => array(
				'source_id' => 0,
			),
		);

		$Customers = $this->setupAuth($userId);

		$existingRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$this->assertEqual(2, $existingRecord['Customer']['customers_default_address_id'],
			'customer existing default address should be 2'
		);
		$result = $this->testAction($url, array('data' => $data));
		$savedRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$this->assertEqual(3, $savedRecord['Customer']['customers_default_address_id'],
			'customer new default address should be 3'
		);
	}

	public function testAlmostFinishedPostNew() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));
		$userId = 2;
		$defaultAddressId = 2;
		$newCity = 'Newtown';
		$data = array(
			'Address' => array(
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => $newCity,
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
			'Customer' => array(
				'customers_default_address_id' => 'new',
				'customers_id' => $userId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'The Tester',
				'cc_number' => '4111111111111111',
				'cc_expires_month' => '3',
				'cc_expires_year' => date('Y') + 1,
				'cc_cvv' => '123'
			),
			'CustomersInfo' => array(
				'source_id' => 0,
			),
		);

		$Customers = $this->setupAuth($userId);

		$existingRecord = $Customers->Customer->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$beforeCount = $Customers->Customer->Address->find('count');

		$result = $this->testAction($url, array('data' => $data));

		$afterCount = $Customers->Customer->Address->find('count');
		$savedRecord = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));

		$this->assertEquals(($beforeCount + 1), $afterCount,
			'Exactly one record should be created, ' . ($afterCount - $beforeCount) . ' were created.'
		);
		$this->assertEqual($defaultAddressId, $existingRecord['Customer']['customers_default_address_id'],
			'customer existing default address should be 2'
		);
		$this->assertGreaterThan(0, $savedRecord['Customer']['customers_default_address_id']);
		$this->assertNotEqual($defaultAddressId, $savedRecord['Customer']['customers_default_address_id'],
			'customer new default address should not be ' . $defaultAddressId
		);
		$this->assertEquals($newCity, $savedRecord['DefaultAddress']['entry_city'],
			'customer new default city should be ' . $newCity
		);
	}

	public function testAlmostFinishedPostNewMissingCity() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));
		$userId = 2;
		$addressId = 2;
		$newCity = '';
		$data =array(
			'Address' => array(
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => $newCity,
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
			'Customer' => array(
				'customers_default_address_id' => '',
				'customers_id' => $userId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'The Tester',
				'cc_number' => '4111111111111111',
				'cc_expires_month' => '3',
				'cc_expires_year' => '18',
				'cc_cvv' => '123'
			)
		);

		$Customers = $this->setupAuth($userId);

		$this->testAction($url, array('data' => $data));
		$record = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));
		$this->assertEqual($addressId, $record['Customer']['customers_default_address_id'],
			'customer default address should not have changed'
		);
		$this->assertTrue(empty($this->headers['Location']), 'page should not redirect');
		$this->assertEqual($userId, $this->vars['customer']['customers_id'], 'customers_id is empty or does not match');
		$this->assertArrayHasKey('Customer', $Customers->request->data, 'Customer array not set in request data');
		$this->assertArrayHasKey('Address', $Customers->request->data, 'Address array not set in request data');
	}

	public function testAlmostFinishedAddressNotCreatedWhenCCValidationFails() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'almost_finished'));
		$userId = 2;
		$addressId = 2;
		$data =array(
			'Address' => array( // Should validate
				'entry_firstname' => 'Thomas',
				'entry_lastname' => 'Thetest',
				'entry_street_address' => '444 Newroad Rd',
				'entry_suburb' => '',
				'entry_city' => 'City',
				'entry_state' => 'IN',
				'entry_postcode' => '34533',
				'entry_country_id' => '223',
				'entry_zone_id' => '1',
				'entry_basename' => ''
			),
			'Customer' => array( // Should fail validation and not save
				'customers_default_address_id' => '',
				'customers_id' => $userId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'The Tester',
				'cc_number' => 'asfoankdb', // Not a card number
				'cc_expires_month' => '3',
				'cc_expires_year' => '18',
				'cc_cvv' => '123'
			)
		);

		$Customers = $this->setupAuth($userId);
		$beforeCount = $Customers->Customer->Address->find('count');

		$this->testAction($url, array('data' => $data));

		$afterCount = $Customers->Customer->Address->find('count');
		$record = $Customers->Customer->find('first', array(
			'contain' => array('DefaultAddress'),
			'conditions' => array('Customer.customers_id' => $userId),
		));
		$this->assertEqual($addressId, $record['Customer']['customers_default_address_id'],
			'customer default address should not have changed'
		);
		$this->assertTrue(empty($this->headers['Location']), 'page should not redirect');
		$this->assertEqual($userId, $this->vars['customer']['customers_id'], 'customers_id is empty or does not match');
		$this->assertArrayHasKey('Customer', $Customers->request->data, 'Customer array not set in request data');
		$this->assertArrayHasKey('Address', $Customers->request->data, 'Address array not set in request data');
		$this->assertEqual($beforeCount, $afterCount);
	}

	/**
	 * Confirm that after saving a customer address but saving of customer data
	 * fails, the just saved address is deleted and a flash message is displayed.
	 *
	 * @return void
	 */
	public function testAlmostFinishedSaveCustomerFails() {
		$userId = 2;
		$url = Router::url(['controller' => 'customers', 'action' => 'almost_finished']);
		$this->setupAuth($userId);
		$customer = ['Customer' => [
			'customers_id' => $userId,
			'customers_default_address_id' => 'new',
		]];

		$Controller = $this->generate('Customers', [
			'components' => [
				'Flash' => ['set'],
			],
			'models' => [
				'Customer' => ['find', 'set', 'save'],
				'Address' => ['find', 'save', 'getInsertID', 'delete'],
				'Zone' => ['find'],
			],
		]);

		$Controller->Customer
			->expects($this->once())
			->method('find')
			->will($this->returnValue($customer));
		$Controller->Customer
			->expects($this->once())
			->method('set');
		$Controller->Customer
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$Controller->Customer->Address
			->expects($this->any())
			->method('find')
			->will($this->returnValue('address canary'));
		$Controller->Customer->Address
			->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$Controller->Customer->Address
			->expects($this->once())
			->method('getInsertID')
			->will($this->returnValue('addressID'));
		$Controller->Customer->Address
			->expects($this->once())
			->method('delete')
			->with('addressID')
			->will($this->returnValue(true));

		$Controller->Customer->Address->Zone
			->expects($this->once())
			->method('find')
			->will($this->returnValue('zone canary'));

		$Controller->Flash
			->expects($this->once())
			->method('set')
			->with('There were errors with your input, please try again.');

		$this->testAction($url, ['data' => $customer]);
	}

	/**
	 * Test bad method aruments 404
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testEditPartialBadArgument() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'no_such_partial_name'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array('method' => 'get'));
	}

	/**
	 * Test that bad methods are handled
	 *
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testEditPartialBadMethod() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'my_info'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * Test that we can update apropriate user and is redirected.
	 *
	 * @return	void
	 */
	public function testEditPartialUpdatingCustomer() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'my_info'));
		$userId = 1;
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'customers_email_address' => 'person@domain.com'
		));

		$Customers = $this->setupAuth($userId);
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('edit', $userId);
		$Customers->CustomerReminder
			->expects($this->once())
			->method('clearRecord')
			->with($userId, 'my_info');
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$result = $Customers->Customer->findByCustomersId(1);

		$this->assertEqual('person@domain.com', $result['Customer']['customers_email_address']);
		$this->assertStringEndsWith('/account#my-info', $this->headers['Location']);
	}

	/**
	 * Test that updating can fail and does not redirect.
	 *
	 * @return	void
	 */
	public function testEditPartialUpdatingShippingInsuranceAmount() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'shipping'));
		$userId = 1;
		$insuranceAmount = '456.78';
		$expectedInsuranceFee = '6.70';
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'insurance_amount' => $insuranceAmount,
		));

		$Customers = $this->setupAuth($userId);
		$Customers->CustomerReminder
			->expects($this->once())
			->method('clearRecord')
			->with($userId, 'shipping');
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId(1);

		$this->assertEqual($record['Customer']['insurance_amount'], $insuranceAmount);
		$this->assertEqual($record['Customer']['insurance_fee'], $expectedInsuranceFee);
		$this->assertStringEndsWith('/account#shipping', $this->headers['Location']);
	}

	/**
	 * Test that updating payment does not display card information
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfo() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;
		$expirationYear = (new DateTime('+ 1 year'))->format('y');
		$data = array('Customer' => array(
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '05',
			'cc_expires_year' => $expirationYear,
			'cc_cvv' => '123',
		));

		$Customers = $this->setupAuth($userId);
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('edit', $userId, 'payment_info');
		$Customers->CustomerReminder
			->expects($this->once())
			->method('clearRecord')
			->with($userId, 'payment_info');
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$Customers->Customer->maskCreditCardNumberAfterFind = false;
		$record = $Customers->Customer->findByCustomersId(1);

		$this->assertEqual($record['Customer']['cc_firstname'], 'First');
		$this->assertEqual($record['Customer']['cc_lastname'], 'Last');
		$this->assertEqual($record['Customer']['cc_number'], 'XXXXXXXXXXXX4242');
		$this->assertEqual($record['Customer']['cc_expires_month'], '05');
		$this->assertEqual($record['Customer']['cc_expires_year'], $expirationYear);
		$this->assertStringEndsWith('/account#payment-info', $this->headers['Location']);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfoValidationFailsFieldsRetained() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;
		$validFirstName = 'Valid';
		$validLastName = 'Valid Last Name';
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'cc_firstname' => $validFirstName,
			'cc_lastname' => $validLastName,
			'cc_number' => '',
			'cc_expires_month' => '05',
			'cc_expires_year' => '19',
			'cc_cvv' => 'asd',
		));

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$Customers->Customer->maskCreditCardNumberAfterFind = false;
		$record = $Customers->Customer->findByCustomersId(1);

		// If validation fail we still want the name on the credit card to carry through
		$this->assertEqual($Customers->request->data['Customer']['cc_firstname'], $validFirstName);
		$this->assertEqual($Customers->request->data['Customer']['cc_lastname'], $validLastName);
		$this->assertArrayNotHasKey('cc_number', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('cc_expires_month', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('cc_expires_year', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('cc_cvv', $Customers->request->data['Customer']);
		$this->assertTrue(empty($this->headers['Location']));
	}

	/**
	 * Test that updating payment does not display card information
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfoGet() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array(
			'method' => 'get'
		));

		$this->assertArrayHasKey('inputs', $this->vars);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayNotHasKey('cc_number', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_number_encrypted', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_expires_month', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_expires_year', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_cvv', $this->vars['customer']['Customer']);
		$this->assertArrayHasKey('partial', $this->vars);
	}


	/**
	 * Test that GETting payment_info without PayPal key secret redirects
	 * @expectedException InternalErrorException
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfoGetWithoutPayPalConfigured() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);

		Configure::delete('PayPal.clientId');
		Configure::delete('PayPal.clientSecret');

		$this->testAction($url, array(
			'method' => 'get'
		));

	}

	/**
	 *
	 * Test that GETting payment_info with PayPal key/secret
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfoGetWithPayPalConfigured() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);

		$this->testAction($url, array(
			'method' => 'get'
		));

		$this->assertArrayHasKey('inputs', $this->vars);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayNotHasKey('cc_number', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_number_encrypted', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_expires_month', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_expires_year', $this->vars['customer']['Customer']);
		$this->assertArrayNotHasKey('cc_cvv', $this->vars['customer']['Customer']);
		$this->assertArrayHasKey('partial', $this->vars);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 * Test that POSTing to payment_info without PayPal key secret
	 * @expectedException InternalErrorException
	 *
	 * @return	void
	 */
	public function testEditPartialPaymentInfoPOSTWithoutPayPalConfigured() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'payment_info'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);

		Configure::delete('PayPal.clientId');
		Configure::delete('PayPal.clientSecret');

		$this->testAction($url, array(
			'method' => 'put'
		));

	}

	/**
	 *
	 * @return	void
	 */
	public function testEditPartialUpdatingShippingInsuranceAmountFails() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'shipping'));
		$userId = 1;
		$insuranceAmount = '10000'; // too much insurance coverage
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'insurance_amount' => $insuranceAmount,
		));

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId(1);

		$this->assertEqual($record['Customer']['insurance_amount'], '50.00');
		$this->assertEqual($record['Customer']['insurance_fee'], '1.65');
		$this->assertTrue(empty($this->headers['Location']));
	}

	/**
	 *
	 * @return	void
	 */
	public function testEditPartialUpdatingCustomerFails() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'my_info'));
		$userId = 1;
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'customers_email_address' => ''
		));

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$this->assertTrue(empty($this->headers['Location']), 'Should not redirect on failed save');
		$this->assertArrayHasKey('partial', $this->vars);
		$this->assertArrayHasKey('inputs', $this->vars);
	}

	/**
	 *
	 * @return	void
	 */
	public function testEditPartialEditingCustomerName() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'my_info'));
		$userId = 1;
		$newFirst = 'NewFirstName';
		$newLast = 'NewLastName';
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'customers_firstname' => $newFirst,
			'customers_lastname' => $newLast,
			'customers_email_address' => 'test@test.com',
		));

		$Customers = $this->setupAuth($userId);
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('edit', $userId);
		$Customers->CustomerReminder
			->expects($this->once())
			->method('clearRecord')
			->with($userId, 'my_info');
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$result = $Customers->Customer->findByCustomersId(1);

		$this->assertNotEqual($newFirst, $result['Customer']['customers_firstname']);
		$this->assertNotEqual($newLast, $result['Customer']['customers_lastname']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertTrue(!empty($this->headers['Location']));
	}

	/**
	 * Test that updating an addresses can fail
	 *
	 * @return	void
	 */
	public function testEditPartialUpdatingCustomersShippingAddressFails() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'addresses'));
		$userId = 1;
		$data = array('Customer' => array(
			'customers_id' => $userId,
			'customers_shipping_address_id' => 999 //not a shipping address
		));

		$Customers = $this->setupAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$result = $Customers->Customer->findByCustomersId(1);

		$this->assertArrayHasKey('partial', $this->vars);
		$this->assertArrayHasKey('inputs', $this->vars);
		$this->assertArrayHasKey('customersDefaultAddresses', $this->vars);
		$this->assertArrayHasKey('customersShippingAddresses', $this->vars);
		$this->assertArrayHasKey('customersEmergencyAddresses', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'Should not redirect on failed save');
	}

	/**
	 * Test that a GET request has varibles set
	 *
	 * @return	void
	 */
	public function testEditPartialSetsVars() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'edit_partial', 'partial' => 'addresses'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('inputs', $this->vars);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertArrayHasKey('partial', $this->vars);
		$this->assertArrayHasKey('customersDefaultAddresses', $this->vars);
		$this->assertArrayHasKey('customersShippingAddresses', $this->vars);
		$this->assertArrayHasKey('customersEmergencyAddresses', $this->vars);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersDefaultAddresses'])
		);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersEmergencyAddresses'])
		);
	}

	/**
	 *
	 */
	public function testEmployeeView() {
		$userId = 2;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'employee' => true,
		));
		$Customers = $this->setupEmployeeAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('customer', $this->vars, '$customer is expected to be defined.');
		$this->assertArrayHasKey('Customer', $this->vars['customer']);
		$this->assertArrayHasKey('Address', $this->vars['customer'], 'Customer should contain Address.');
		$this->assertArrayHasKey('DefaultAddress', $this->vars['customer'], 'Customer should contain DefaultAddress.');
		$this->assertArrayHasKey('ShippingAddress', $this->vars['customer'], 'Customer should contain ShippingAddress.');
		$this->assertArrayHasKey('EmergencyAddress', $this->vars['customer'], 'Customer should contain EmergencyAddress.');
		$this->assertArrayHasKey('orders', $this->vars, '$orders is expected to be defined.');
		$this->assertArrayHasKey('customRequests', $this->vars, '$customRequests is expected to be defined.');
		$this->assertArrayHasKey('AuthorizedName', $this->vars['customer'], 'Customer should contain AuthorizedName.');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testEmployeeViewPOST() {
		$userId = 2;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'employee' => true
		));
		$Customers = $this->setupEmployeeAuth($userId);

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testEmployeeViewPUT() {
		$userId = 2;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'employee' => true
		));
		$Customers = $this->setupEmployeeAuth($userId);

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testEmployeeViewDELETE() {
		$userId = 2;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'employee' => true
		));
		$Customers = $this->setupEmployeeAuth($userId);

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfo() {
		$userId = 1;
		$customerId = 1;
		$expirationYear = (new DateTime('+ 1 year'))->format('y');
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '05',
			'cc_expires_year' => $expirationYear,
			'cc_cvv' => '123',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$Customers->Customer->maskCreditCardNumberAfterFind = false;
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertEquals('First', $record['Customer']['cc_firstname']);
		$this->assertEquals('Last', $record['Customer']['cc_lastname']);
		$this->assertEquals('XXXXXXXXXXXX4242', $record['Customer']['cc_number']);
		$this->assertEquals('05', $record['Customer']['cc_expires_month']);
		$this->assertEquals($expirationYear, $record['Customer']['cc_expires_year']);
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 *
	 * @expectedException InternalErrorException
	 * @return	void
	 */
	public function testManagerEditPaymentInfoWithoutConfigSet() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));

		$Customers = $this->setupManagerAuth($userId);
		Configure::delete('PayPal.clientId');
		Configure::delete('PayPal.clientSecret');
		$this->testAction($url, array('method' => 'get'));

	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfoInvalidData() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '01',
			'cc_expires_year' => '15',
			'cc_cvv' => '123',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$Customers->Customer->maskCreditCardNumberAfterFind = false;
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals('First', $record['Customer']['cc_firstname']);
		$this->assertNotEquals('Last', $record['Customer']['cc_lastname']);
		$this->assertNotEquals('XXXXXXXXXXXX4242', $record['Customer']['cc_number']);
		$this->assertNotEquals('05', $record['Customer']['cc_expires_month']);
		$this->assertNotEquals('19', $record['Customer']['cc_expires_year']);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 *
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfoNonexistantCustomer() {
		$userId = 1;
		$customerId = 'A'; // Not a valid customer ID
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '01',
			'cc_expires_year' => '15',
			'cc_cvv' => '123',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfoPOST() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '01',
			'cc_expires_year' => '15',
			'cc_cvv' => '123',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfoDELETE() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditPaymentInfoOnlyUpdatesAllowedFields() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '01',
			'cc_expires_year' => '15',
			'cc_cvv' => '123',
			'customers_email_address' => 'not.updated@example.com',
		));

		$this->testAction($url, array('method' => 'put'));
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals('not.updated@example.com', $record['Customer']['customers_email_address']);
		$this->assertNotEquals('First', $record['Customer']['cc_firstname']);
		$this->assertNotEquals('Last', $record['Customer']['cc_lastname']);
		$this->assertNotEquals('XXXXXXXXXXXX4242', $record['Customer']['cc_number']);
		$this->assertNotEquals('05', $record['Customer']['cc_expires_month']);
		$this->assertNotEquals('19', $record['Customer']['cc_expires_year']);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfo() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_firstname' => 'Joe',
			'customers_lastname' => 'Tester',
			'customers_email_address' => 'updated@example.com',
			'customers_telephone' => '1235551234',
			'customers_fax' => '1235556789',
			'backup_email_address' => 'backup@example.com',
			'invoicing_authorized' => 0,
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertEquals('Joe', $record['Customer']['customers_firstname']);
		$this->assertEquals('Tester', $record['Customer']['customers_lastname']);
		$this->assertEquals('updated@example.com', $record['Customer']['customers_email_address']);
		$this->assertEquals('1235551234', $record['Customer']['customers_telephone']);
		$this->assertEquals('1235556789', $record['Customer']['customers_fax']);
		$this->assertEquals('backup@example.com', $record['Customer']['backup_email_address']);
		$this->assertEquals(0, $record['Customer']['invoicing_authorized']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfoOnlyUpdatesAllowedFields() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);
		$data = array('Customer' => array(
			'customers_id' => $customerId,
			'cc_firstname' => 'NotUpdated',
			'customers_email_address' => 'updated@example.com',
			'customers_firstname' => 'Joe',
			'customers_lastname' => 'Tester',
		));

		$this->testAction($url, array('method' => 'put', 'data' => $data));
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals('NotUpdated', $record['Customer']['cc_firstname']);
		$this->assertEquals('Joe', $record['Customer']['customers_firstname']);
		$this->assertEquals('Tester', $record['Customer']['customers_lastname']);
		$this->assertEquals('updated@example.com', $record['Customer']['customers_email_address']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 *
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfoNonexistantCustomer() {
		$userId = 1;
		$customerId = 'A'; // Not a valid customer ID
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => '1', // even if we supply a differnt id
			'customers_email_address' => 'updated@example.com',
			'customers_telephone' => '1235551234',
			'customers_fax' => '1235556789',
			'backup_email_address' => 'backup@example.com',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfoInvalidData() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_email_address' => 'notAnEmail Address',
			'customers_telephone' => '1235551234',
			'customers_fax' => '1235556789',
			'backup_email_address' => 'backup@example.com',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals('updated@example.com', $record['Customer']['customers_email_address'], 'Customer should not update');
		$this->assertNotEquals('1235551234', $record['Customer']['customers_telephone'], 'Customer should not update');
		$this->assertNotEquals('1235556789', $record['Customer']['customers_fax'], 'Customer should not update');
		$this->assertNotEquals('backup@example.com', $record['Customer']['backup_email_address'], 'Customer should not update');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfoPOST() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_firstname' => 'First',
			'customers_lastname' => 'Last',
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditContactInfoDELETE() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Confirm that the expected view vars are set.
	 *
	 * @return void
	 */
	public function testManagerView() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('customer', $this->vars, '$customer is expected to be defined.');
		$this->assertArrayHasKey('Customer', $this->vars['customer']);
		$this->assertArrayHasKey('Address', $this->vars['customer'], 'Customer should contain Address.');
		$this->assertArrayHasKey('DefaultAddress', $this->vars['customer'], 'Customer should contain DefaultAddress.');
		$this->assertArrayHasKey('ShippingAddress', $this->vars['customer'], 'Customer should contain ShippingAddress.');
		$this->assertArrayHasKey('EmergencyAddress', $this->vars['customer'], 'Customer should contain EmergencyAddress.');
		$this->assertArrayHasKey('orders', $this->vars, '$orders is expected to be defined.');
		$this->assertArrayHasKey('customRequests', $this->vars, '$customRequests is expected to be defined.');
		$this->assertArrayHasKey('AuthorizedName', $this->vars['customer'], 'Customer should contain AuthorizedName.');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
		$this->assertArrayHasKey('userIsManager', $this->vars, '$userIsManager is expected to be defined.');
		$this->assertArrayHasKey('partialSignup', $this->vars, '$partialSignup is expected to be defined.');
		$this->assertArrayHasKey('OrderTotal', $this->vars['orders'][0], '$orders should contain at least one OrderTotal.');
		$this->assertArrayHasKey('Customer', $this->vars['orders'][0], '$orders should contain at least one Customer.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['orders'][0], '$orders should contain at least one OrderStatus.');
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerViewPOST() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerViewPUT() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerViewDELETE() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Confirm that when an inactive customer is viewed the date the account
	 * was closed is set as a view variable.
	 *
	 * @return void
	 */
	public function testManagerViewCustomerNotActive() {
		$userId = 1;
		$customerId = 7;
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		]);
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('closed', $Customers->viewVars);
		$this->assertRegExp(
			'/\d{2}\/\d{2}\/\d{4}/',
			$Customers->viewVars['closed']
		);
	}

	/**
	 * Confirm that when an invalid customer id is attempted to be viewed the
	 * expected exception is thrown.
	 *
	 * @return void
	 */
	public function testManagerViewCustomerNotFound() {
		$userId = 1;
		$customerId = 99999;
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		]);
		$Customers = $this->setupManagerAuth($userId);

		$this->setExpectedException('NotFoundException', 'Customer not found');
		$this->testAction($url, ['method' => 'get']);
	}

	/**
	 * Test that a GET request has varibles set
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesGET() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('customersDefaultAddresses', $this->vars);
		$this->assertArrayHasKey('customersShippingAddresses', $this->vars);
		$this->assertArrayHasKey('customersEmergencyAddresses', $this->vars);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersDefaultAddresses'])
		);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersEmergencyAddresses'])
		);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddresses() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_default_address_id' => 9,
			'customers_shipping_address_id' => 7,
			'customers_emergency_address_id' => 8,
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertEquals(9, $record['Customer']['customers_default_address_id']);
		$this->assertEquals(7, $record['Customer']['customers_shipping_address_id']);
		$this->assertEquals(8, $record['Customer']['customers_emergency_address_id']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesOnlyUpdatesAllowedFields() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);
		$data = array('Customer' => array(
			'customers_email_address' => 'not.updated@example.com',
			'customers_default_address_id' => 9,
		));

		$this->testAction($url, array('method' => 'put', 'data' => $data));
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertEquals(9, $record['Customer']['customers_default_address_id'], 'Should still update allowed fields.');
		$this->assertNotEquals('updated@example.com', $record['Customer']['customers_email_address'], 'Should not update other fields.');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesShippingAndEmergencyEqual() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$Customers = $this->setupManagerAuth($userId);
		$data = array('Customer' => array(
			'customers_shipping_address_id' => 7,
			'customers_emergency_address_id' => 7,
		));

		$this->testAction($url, array('method' => 'put', 'data' => $data));
		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals(7, $record['Customer']['customers_shipping_address_id']);
		$this->assertNotEquals(7, $record['Customer']['customers_emergency_address_id']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 *
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesInfoNonexistantCustomer() {
		$userId = 1;
		$customerId = 'A'; // Not a valid customer ID
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_id' => '1', // even if we supply a differnt id
			'customers_default_address_id' => 9,
			'customers_shipping_address_id' => 7,
			'customers_emergency_address_id' => 8,
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesInfoInvalidData() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_default_address_id' => 2, // Does not belong to customer 1
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'put'
		));

		$record = $Customers->Customer->findByCustomersId($customerId);

		$this->assertNotEquals(2, $record['Customer']['customers_default_address_id'], 'Should not be able to save another customer\'s address.');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesInfoPOST() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));
		$data = array('Customer' => array(
			'customers_default_address_id' => 9,
			'customers_shipping_address_id' => 7,
			'customers_emergency_address_id' => 8,
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerEditDefaultAddressesInfoDELETE() {
		$userId = 1;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'manager' => true
		));

		$Customers = $this->setupManagerAuth($userId);
		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Test that a GET request has varibles set as an employee level user
	 *
	 * @return	void
	 */
	public function testEmployeeEditDefaultAddressesGET() {
		$userId = 2;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			'id' => $customerId,
			'employee' => true
		));
		$Customers = $this->setupManagerAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('customersDefaultAddresses', $this->vars);
		$this->assertArrayHasKey('customersShippingAddresses', $this->vars);
		$this->assertArrayHasKey('customersEmergencyAddresses', $this->vars);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersDefaultAddresses'])
		);
		$this->assertTrue(
			Hash::contains($this->vars['customersShippingAddresses'], $this->vars['customersEmergencyAddresses'])
		);
	}

	/**
	 * Test GET request
	 *
	 * @return	void
	 */
	public function testChangePassword() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'change_password'));
		$userId = 1;

		$Customers = $this->setupAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayNotHasKey('Customer', $Customers->request->data);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 * Test POST request
	 *
	 * @return	void
	 */
	public function testChangePasswordSuccess() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'change_password'));
		$userId = 1;
		$Customers = $this->setupAuth($userId);
		$Customers->Auth
			->expects($this->any())
			->method('login')
			->will($this->returnValue(true));
		$currentPassword = 'oldPassword6789';
		$newPassword = 'newPassword1234';
		$data = array('Customer' => array(
			'current_password' => $currentPassword,
			'new_password' => $newPassword,
			'confirm_new_password' => $newPassword
		));
		$customerCountBefore = $Customers->Customer->find('count');

		//set the current password
		$Customers->Customer->save(
			['Customer' => [
				'customers_id' => $userId,
				'customers_password' => $currentPassword
			]],
			true,
			['customers_password']
		);
		$Customers->Customer->id = $userId;
		$passwordHashBefore = $Customers->Customer->field('customers_password');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerCountAfter = $Customers->Customer->find('count');
		$passwordHashAfter = $Customers->Customer->field('customers_password');

		$this->assertEqual($customerCountBefore, $customerCountAfter, 'A Customer was created or deleted.');
		$this->assertNotEqual($passwordHashBefore, $passwordHashAfter, 'The customer password should have changed, but did not.');
		$this->assertArrayNotHasKey('current_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('new_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('confirm_new_password', $Customers->request->data['Customer']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringEndsWith('/account', $this->headers['Location']);
	}

	/**
	 * Test POST request fails with incorrect current password
	 *
	 * @return	void
	 */
	public function testChangePasswordIncorrectCurrentPassword() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'change_password'));
		$userId = 1;
		$Customers = $this->setupAuth($userId);
		$currentPassword = 'badPassword';
		$newPassword = 'newPassword1234';
		$data = array('Customer' => array(
			'current_password' => $currentPassword,
			'new_password' => $newPassword,
			'confirm_new_password' => $newPassword
		));
		$customerCountBefore = $Customers->Customer->find('count');
		$Customers->Customer->id = $userId;
		$passwordHashBefore = $Customers->Customer->field('customers_password');

		$this->testAction($url, array(
			'method' => 'post',
			'data' => $data,
		));

		$customerCountAfter = $Customers->Customer->find('count');
		$passwordHashAfter = $Customers->Customer->field('customers_password');

		$this->assertEqual($customerCountBefore, $customerCountAfter, 'A Customer was created or deleted.');
		$this->assertEqual($passwordHashBefore, $passwordHashAfter, 'The customer password changed, but should not have.');
		$this->assertArrayNotHasKey('current_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('new_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('confirm_new_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 * Test POST request fails when password confirm does not match
	 */
	public function testChangePasswordNewPasswordsDoNotMatch() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'change_password'));
		$userId = 1;
		$Customers = $this->setupAuth($userId);
		$Customers->Auth
			->expects($this->any())
			->method('login')
			->will($this->returnValue(true));
		$currentPassword = 'password';
		$newPassword = 'newPassword1234';
		$confirmPassword = 'notTheNewPassword';
		$data = array('Customer' => array(
			'current_password' => $currentPassword,
			'new_password' => $newPassword,
			'confirm_new_password' => $confirmPassword
		));
		$customerCountBefore = $Customers->Customer->find('count');
		$Customers->Customer->id = $userId;

		//set the current password
		$Customers->Customer->save(array(
			'Customer' => array(
				'customers_id' => $userId,
				'customers_password' => $currentPassword
			)
		));

		$passwordHashBefore = $Customers->Customer->field('customers_password');
		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$customerCountAfter = $Customers->Customer->find('count');
		$passwordHashAfter = $Customers->Customer->field('customers_password');

		$this->assertEqual($customerCountBefore, $customerCountAfter, 'A Customer was created or deleted.');
		$this->assertEqual($passwordHashBefore, $passwordHashAfter, 'The customer password changed, but should not have.');
		$this->assertArrayNotHasKey('current_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('new_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('confirm_new_password', $Customers->request->data['Customer']);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 * Confirm that when saving customer data during a password change fails,
	 * the expected flash message is displayed and the expected validation
	 * errors are set.
	 *
	 * @return void
	 */
	public function testChangePasswordSaveCustomerFails() {
		$userId = 1;
		$url = Router::url(['controller' => 'customers', 'action' => 'change_password']);
		$this->setupAuth($userId);
		$data = ['Customer' => [
			'current_password' => 'password',
			'new_password' => 'new-password',
			'confirm_new_password' => 'new-password',
		]];

		$Controller = $this->generate('Customers', [
			'components' => [
				'Flash' => ['set'],
			],
			'models' => [
				'Customer' => ['field', 'save'],
			],
		]);
		$Controller->Customer->validationErrors = [
			'customers_password' => 'password',
		];

		$Controller->Customer
			->expects($this->once())
			->method('field')
			->will($this->returnValue('add18d635fceab999dedaa16aa35dc6a:5f'));
		$Controller->Customer
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Controller->Flash
			->expects($this->once())
			->method('set')
			->with('You password could not be changed.');

		$this->assertArrayNotHasKey('new_password', $Controller->Customer->validationErrors);

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$this->assertArrayHasKey('new_password', $Controller->Customer->validationErrors);
		$this->assertSame(
			$Controller->Customer->validationErrors['customers_password'],
			$Controller->Customer->validationErrors['new_password'],
			'Should match'
		);
	}

	/**
	 *
	 */
	public function testForgotPasswordSuccess() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'forgot_password'));
		$Customers = $this->generate('Customers', array(
			'methods' => array('taskFactory')
		));
		$Task = $this->getMock('Task', array('createJob'));
		$Task->expects($this->once())
			->method('createJob')
			->will($this->returnValue(true));
		$Customers->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));

		$data = array('Customer' => array(
			'email' => 'someone@example.com'
		));
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$requestCountBefore = $PasswordRequest->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');
		$request = $PasswordRequest->find('first', array(
			'order' => array('created' => 'desc')
		));

		$this->assertEqual(($requestCountBefore + 1) , $requestCountAfter, 'Should create exactly 1 password reset request.');
		$this->assertTrue(!empty($request['PasswordRequest']['customer_id']), 'Request should be saved with a `customer_id`');
		$this->assertTrue(empty($request['PasswordRequest']['user_id']), 'Request should not be saved with a `user_id`');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
		$this->assertStringEndsWith('/login', $this->headers['Location'], 'Should redirect to "/login"');
	}

	/**
	 *
	 */
	public function testForgotPasswordBadEmail() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'forgot_password'));
		$data = array('Customer' => array(
			'email' => 'noone@example.com'
		));
		$Customers = $this->generate('Customers');
		$Customers->expects($this->never())
			->method('taskFactory');
		$Customers->PasswordRequest = $this->getMockForModel('PasswordRequest');
		$requestCountBefore = $Customers->PasswordRequest->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $Customers->PasswordRequest->find('count');

		$this->assertEqual($requestCountBefore, $requestCountAfter);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 *
	 */
	public function testForgotPasswordFalisToCreateRequest() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'forgot_password'));
		$Customers = $this->generate('Customers', array(
			'models' => array(
				'PasswordRequest' => array('save')
			),
		));
		$Customers->Customer->PasswordRequest->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$Customers->expects($this->never())
			->method('taskFactory');
		$data = array('Customer' => array(
			'email' => 'someone@example.com'
		));
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$requestCountBefore = $PasswordRequest->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');

		$this->assertEqual($requestCountBefore , $requestCountAfter);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 */
	public function testForgotPasswordEmailFailsToSend() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'forgot_password'));
		$Customers = $this->generate('Customers', array(
			'methods' => array('taskFactory')
		));
		$Task = $this->getMock('Task', array('createJob'));
		$Task->expects($this->once())
			->method('createJob')
			->will($this->returnValue(false));
		$Customers->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));
		$data = array('Customer' => array(
			'email' => 'someone@example.com'
		));
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$requestCountBefore = $PasswordRequest->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');

		$this->assertEqual($requestCountBefore , $requestCountAfter, 'Should not add or remove requests');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 *
	 */
	public function testForgotPasswordGET() {
		$url = Router::url(array('controller' => 'customers', 'action' => 'forgot_password'));
		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 *
	 */
	public function testResetPasswordForCustomerWithPostData() {
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$Customer = ClassRegistry::init('Customer');
		$PasswordRequest->save(array('PasswordRequest' => array(
			'customer_id' => '1',
		)));
		$uuid = $PasswordRequest->getInsertId();
		$password = 'password';
		$data = array('Customer' => array(
			'new_password' => $password,
			'password_confirm' => $password
		));

		$PasswordRequest->deleteExpired();
		$requestCountBefore = $PasswordRequest->find('count');
		$customerCountBefore = $Customer->find('count');
		$this->testAction('/reset-password/' . $uuid, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');
		$customerCountAfter = $Customer->find('count');

		$this->assertEqual($requestCountBefore , ($requestCountAfter + 1), 'Should delete password reset request.');
		$this->assertEmpty($PasswordRequest->findById($uuid), 'Should delete the request specified by the url.');
		$this->assertEqual($customerCountBefore , $customerCountAfter, 'Should not add or delete customers.');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 *
	 * @expectedException NotFoundException
	 */
	public function testResetPasswordWithBadRequestUUID() {
		$this->testAction('/reset-password/bad-uuid', array('method' => 'get'));
	}

	/**
	 *
	 */
	public function testResetPasswordWithWithBadPasswordConfirm() {
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$Customer = ClassRegistry::init('Customer');
		$PasswordRequest->save(array('PasswordRequest' => array(
			'customer_id' => '1',
		)));
		$uuid = $PasswordRequest->getInsertId();
		$password = 'password';
		$data = array('Customer' => array(
			'new_password' => $password,
			'password_confirm' => 'notPassword'
		));

		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$PasswordRequest->deleteExpired();
		$requestCountBefore = $PasswordRequest->find('count');

		$this->testAction('/reset-password/' . $uuid, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');

		$this->assertEqual($requestCountBefore , $requestCountAfter , 'Should not add or delete password reset requests.');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 */
	public function testResetPasswordWithWithBadNewPassword() {
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$Customer = ClassRegistry::init('Customer');
		$PasswordRequest->save(array('PasswordRequest' => array(
			'customer_id' => '1',
		)));
		$uuid = $PasswordRequest->getInsertId();
		$password = 'short';
		$data = array('Customer' => array(
			'new_password' => $password,
			'password_confirm' => $password
		));

		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$PasswordRequest->deleteExpired();
		$requestCountBefore = $PasswordRequest->find('count');

		$this->testAction('/reset-password/' . $uuid, array('method' => 'post', 'data' => $data));
		$requestCountAfter = $PasswordRequest->find('count');

		$this->assertEqual($requestCountBefore , $requestCountAfter , 'Should not add or delete password reset requests.');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
	}

	/**
	 *
	 */
	public function testResetPasswordGET() {
		$PasswordRequest = ClassRegistry::init('PasswordRequest');
		$Customer = ClassRegistry::init('Customer');
		$PasswordRequest->save(array('PasswordRequest' => array(
			'customer_id' => '1',
		)));
		$uuid = $PasswordRequest->getInsertId();
		$this->testAction('/reset-password/' . $uuid, array('method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
	}

	/**
	 *
	 */
	public function testApiView() {
		$Customers = $this->setupApiAuth();
		$id = 1;

		$contents = $this->testAction('/api/customers/' . $id, array('return' => 'contents', 'method' => 'get'));

		$decoded = json_decode($contents, true);
		$this->assertTrue($decoded !== null, 'Failed to decode response as JSON');
		$this->assertArrayHasKey('customer', $decoded);
		$this->assertArrayHasKey('id', $decoded['customer']);
		$this->assertTrue($decoded['customer']['id'] == $id);
		$this->assertArrayHasKey('customers_id', $decoded['customer']);
		$this->assertTrue($decoded['customer']['customers_id'] == $id);
	}

	/**
	 *
	 */
	public function testApiViewOrderNotExists() {
		$Customers = $this->setupApiAuth();
		$id = 999;
		$this->setExpectedException('NotFoundException');

		$this->testAction('/api/customers/' . $id, array('return' => 'contents', 'method' => 'get'));
	}

	/**
	 *
	 */
	public function testApiNotify() {
		$Customers = $this->setupApiAuth();
		$id = 1;
		$Customers->expects($this->once())
			->method('_sendDefaultEmail')
			->will($this->returnValue(true));
		$data = array('message' => array(
			'body' => 'Test email message',
			'subject' => 'Test email subject',
		));

		$this->testAction("/api/customers/{$id}/notify", array(
			'return' => 'contents',
			'method' => 'post',
			'data' => json_encode($data),
		));

		$this->assertEquals(204, $Customers->response->statusCode());
	}

	/**
	 *
	 */
	public function testApiNotifyCustomerNotExists() {
		$Customers = $this->setupApiAuth();
		$id = 999;
		$Customers->expects($this->never())
			->method('_sendDefaultEmail');
		$data = array('message' => array(
			'body' => 'Test email message',
			'subject' => 'Test email subject',
		));
		$this->setExpectedException('NotFoundException');

		$this->testAction("/api/customers/{$id}/notify", array(
			'return' => 'contents',
			'method' => 'post',
			'data' => json_encode($data),
		));

	}

	/**
	 *
	 */
	public function testApiNotifyWithoutMessageBody() {
		$Customers = $this->setupApiAuth();
		$id = 1;
		$Customers->expects($this->never())
			->method('_sendDefaultEmail');
		$data = array('message' => array(
			'subject' => 'Test email subject',
		));
		$this->setExpectedException('BadRequestException');

		$this->testAction("/api/customers/{$id}/notify", array(
			'return' => 'contents',
			'method' => 'post',
			'data' => json_encode($data),
		));
	}

	/**
	 *
	 */
	public function testApiNotifyEmailFails() {
		$Customers = $this->setupApiAuth();
		$id = 1;
		$Customers->expects($this->once())
			->method('_sendDefaultEmail')
			->will($this->returnValue(false));
		$data = array('message' => array(
			'body' => 'Test email message',
			'subject' => 'Test email subject',
		));

		$this->setExpectedException('BadRequestException');

		$this->testAction("/api/customers/{$id}/notify", array(
			'return' => 'contents',
			'method' => 'post',
			'data' => json_encode($data),
		));
	}

	public function testManagerAddresses() {
		$id = 1;
		$Customers = $this->setupManagerAuth(1);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'addresses',
			'manager' => true,
			'id' => $id,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('_serialize', $this->vars);
		$this->assertContains('addresses', $this->vars['_serialize']);
		$this->assertArrayHasKey('addresses', $this->vars);
	}

	/**
	 * Confirm that when the customer id is invalid the expected exception
	 * is thrown.
	 *
	 * @return void
	 */
	public function testManagerAddressesCustomerNotFound() {
		$id = 99999;
		$Customers = $this->setupManagerAuth(1);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'addresses',
			'manager' => true,
			'id' => $id,
		]);

		$this->setExpectedException('NotFoundException', 'Customer not found');
		$this->testAction($url, ['method' => 'get']);
	}

	public function testManagerShippingAddresses() {
		$id = 1;
		$Customers = $this->setupManagerAuth(1);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'shipping_addresses',
			'manager' => true,
			'id' => $id,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('_serialize', $this->vars);
		$this->assertContains('shippingAddresses', $this->vars['_serialize']);
		$this->assertArrayHasKey('shippingAddresses', $this->vars);
	}

	/**
	 * Confirm that when the customer id is invalid the expected exception
	 * is thrown.
	 *
	 * @return void
	 */
	public function testManagerShippingAddressesCustomerNotFound() {
		$id = 99999;
		$Customers = $this->setupManagerAuth(1);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'shipping_addresses',
			'manager' => true,
			'id' => $id,
		]);

		$this->setExpectedException('NotFoundException', 'Customer not found');
		$this->testAction($url, ['method' => 'get']);
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
			'controller' => 'customers',
			'action' => 'search',
			'manager' => true,
		));
		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
	}

	/**
	 * Confirm that a redirect happens if there is a single match for the query
	 *
	 * @return	void
	 */
	public function testManagerSearchWithQuerySingleResult() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = '/manager/customers?q=Unique';
		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
		$this->assertContains('5', $this->headers['Location'], 'Location should contain customer ID 5');
	}

	/**
	 * Confirm that the expected view variables are set with a query
	 *
	 * @return	void
	 */
	public function testManagerSearchWithQuery() {
		$userId = 1;
		$search = 'Billing';
		$expected = 2;
		$this->setupManagerAuth($userId);
		$url = '/manager/customers?q=' . strtolower($search);
		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);

		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
		$this->assertArrayHasKey('results', $this->vars, '$this->vars should contain the results key');
		$this->assertSame($expected, count($this->vars['results']), 'Should have 2 records in the results array');
		$this->assertSame(
			$expected,
			count(Hash::extract($this->vars, 'results.{n}.Customer[customers_lastname=/^' . $search .'/]')),
			'Should have 2 matching $search records'
		);
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
			'controller' => 'customers',
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
			'controller' => 'customers',
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
			'controller' => 'customers',
			'action' => 'search',
			'manager' => true,
		));
		$this->testAction($url, array('method' => 'delete'));
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
			'controller' => 'customers',
			'action' => 'search',
			'employee' => true,
		));
		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
	}

	/**
	 * setupAuth
	 *
	 * @param mixed $userId
	 */
	protected function setupAuth($userId, $options = []) {
		Configure::write('PayPal.clientId', 'ID');
		Configure::write('PayPal.clientSecret', 'SECRET');

		$defaults = [
			'components' => [
				'Auth' => ['user', 'login'],
				'Activity' => ['record'],
			]
		];
		$options = !empty($options) ? Hash::merge($options, $defaults) : $defaults;
		$Customers = $this->generate('Customers', $options);

		$user = ClassRegistry::init('Customer')->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$user = $user['Customer'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		$Customers->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		$Customers->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$Customers->CustomerReminder = $this->getMockForModel('CustomerReminder', array('clearRecord'));

		return $Customers;
	}

	/**
	 * setupEmployeeAuth
	 *
	 * @param mixed $userId
	 */
	protected function setupEmployeeAuth($userId) {
		Configure::write('PayPal.clientId', 'ID');
		Configure::write('PayPal.clientSecret', 'SECRET');
		Configure::write('Security.admin.ips', false);

		$Customers = $this->generate('Customers', array(
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
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

			$Customers->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
			$Customers->Customer->expects($this->any())
				->method('authorizeCreditCard')
				->will($this->returnValue(true));

		return $Customers;
	}

	/**
	 * setupManagerAuth
	 *
	 * @param mixed $userId
	 */
	protected function setupManagerAuth($userId) {
		Configure::write('PayPal.clientId', 'ID');
		Configure::write('PayPal.clientSecret', 'SECRET');
		Configure::write('Security.admin.ips', false);

		$Customers = $this->generate('Customers', array(
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
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

			$Customers->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
			$Customers->Customer->expects($this->any())
				->method('authorizeCreditCard')
				->will($this->returnValue(true));

		return $Customers;
	}

	/**
	 * setupApiAuth
	 *
	 */
	protected function setupApiAuth() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'login')
			),
			'methods' => array('taskFactory', '_sendDefaultEmail')
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		return $Customers;
	}

	/**
	 * Test that json add success returns a json customer array.
	 */
	public function testAddSuccess() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'add',
		));
		$customerToSave = [
			'customers_firstname' => 'Test',
			'customers_lastname' => 'User',
			'customers_email_address' => 'test.user_unique@example.com',
			'customers_password' => 'password',
		];
		$data = json_encode([
			'data' => [
				'type' => 'customers',
				'attributes' => $customerToSave,
			],
		]);
		$authResponse = $customerToSave + [
			'customers_id' => '123',
			'billing_id' => 'TU1234',
		];
		$authMap = [
			['role', false],
			[null, $authResponse],
		];
		$Customers = $this->generate('Customers', [
			'components' => [
				'Auth' => ['user', 'login'],
				'Activity',
			],
		]);
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValueMap($authMap));
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('register');

		$result = $this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
		$result = json_decode($result,1);

		$this->assertSame('customers', $result['data']['type']);
		$this->assertNotEmpty($result['data']['attributes']['customers_firstname']);
		$this->assertNotEmpty($result['data']['attributes']['billing_id']);
		$this->assertArrayNotHasKey('cc_firstname', $result['data']['attributes']);
	}

	/**
	 * Test that json login failure will throw an exception.
	 *
	 * @expectedException BaseSerializerException
	 */
	public function testAddSaveFails() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Controller = $this->generate('Customers', ['methods' => ['logBaseSerializerException']]);
		$url = Router::url([
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'add',
		]);
		$data = json_encode([
			'data' => [
				'type' => 'customers',
				'attributes' => [
					'customers_firstname' => 'Test',
					'customers_lastname' => 'User',
				],
			],
		]);

		$Controller->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->anything(),
				$this->identicalTo('Customer'),
				$this->identicalTo('customers'),
				$this->anything()
			);

		$this->testAction($url, [
			'data' => $data,
			'method' => 'post'
		]);
	}

	/**
	 * Mocks the customers controller and the emailFactory method. Tells
	 * AppEmail what to return on send.
	 *
	 * @param bool $returnValue The return value of send.
	 * @return The customer controller object.
	 */
	protected function mockEmail($returnValue) {
		$Customers = $this->generate('Customers', array(
			'methods' => array('emailFactory')
		));
		$Email = $this->getMock('AppEmail', array('send'));
		$Email->expects($this->once())
			->method('send')
			->will($this->returnValue($returnValue));
		$Customers->expects($this->once())
			->method('emailFactory')
			->will($this->returnValue($Email));

		return $Customers;
	}

	/**
	 * Confirm that when a POST request to manager_quick_order happens the
	 * expected exception is thrown.
	 *
	 * @return void
	 */
	public function testManagerQuickOrderInvalidMethod() {
		$userId = 1;
		$billingId = 'foo';
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'quick_order',
			'manager' => true,
			'?' => array('q' => $billingId),
		));

		$this->setExpectedException('MethodNotAllowedException');
		$this->testAction($url, array(
			'method' => 'post',
		));
	}

	/**
	 * Confirm that when a customer's billing id is invalid or can't be found
	 * a redirect occurs, but not to the orders/add page.
	 *
	 * @return void
	 */
	public function testManagerQuickOrderInvalidBillingId() {
		$userId = 1;
		$billingId = 'foo';
		// $billingId = 'IB1234';
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'quick_order',
			'manager' => true,
			'?' => array('q' => $billingId),
		));

		$this->testAction($url, array(
			'method' => 'get',
		));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('manager', $this->headers['Location']);
		$this->assertNotContains('orders', $this->headers['Location']);
	}

	/**
	 * Confirm that when an inactive customer's billing id is entered, the
	 * admin is not taken to the quick order page.
	 *
	 * @return void
	 */
	public function testManagerQuickOrderInactiveBillingId() {
		$userId = 1;
		$billingId = 'ZZ1234';
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'quick_order',
			'manager' => true,
			'?' => ['q' => $billingId],
		]);

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('manager', $this->headers['Location']);
		$this->assertNotContains('orders', $this->headers['Location']);
	}

	/**
	 * Confirm that when a GET request to manager_quick_order is made with a valid
	 * customer billing_id a redirect occurs to the order add page for the correct
	 * customer.
	 *
	 * @return void
	 */
	public function testManagerQuickOrderValidBillingId() {
		$userId = 1;
		$billingId = 'IB1234'; // for customers_id 2
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'quick_order',
			'manager' => true,
			'?' => array('q' => $billingId),
		));

		$this->testAction($url, array(
			'method' => 'get',
		));
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('manager/orders/add/2', $this->headers['Location']);
	}

	/**
	 * Confirm that when a GET request to employee_quick_order is made with a valid
	 * customer billing_id a redirect occurs to the order add page for the correct
	 * customer.
	 *
	 * @return void
	 */
	public function testEmployeeQuickOrderValidBillingId() {
		$userId = 2;
		$billingId = 'IB1234'; // for customers_id 2
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'quick_order',
			'employee' => true,
			'?' => ['q' => $billingId],
		]);

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect.');
		$this->assertContains('employee/orders/add/2', $this->headers['Location']);
	}

	/**
	 * Confirm that the expected methods are called and that confirm_close
	 * returns true.
	 *
	 * @return void
	 */
	public function testConfirmCloseEmailSent() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$userId = 1;

		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user'),
			),
			'methods' => array('taskFactory'),
		));
		$Task = $this->getMock('Task', array('createJob'));
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValue($userId));
		$Task->expects($this->once())
			->method('createJob')
			->will($this->returnValue(true));
		$Customers->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));

		$hash = sha1(date('Y-m-d') . $userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'confirm_close',
			'customerId' => $userId,
			'hash' => $hash,
		));
		$result = $this->testAction($url);
		$this->assertSame('success', $result);
	}

	/**
	 * Confirm that the expected methods are called and that confirm_close
	 * returns false.
	 *
	 * @return void
	 */
	public function testConfirmCloseEmailNotSent() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$userId = 1;

		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user'),
			),
			'methods' => array('taskFactory'),
		));
		$Task = $this->getMock('Task', array('createJob'));
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValue($userId));
		$Task->expects($this->once())
			->method('createJob')
			->will($this->returnValue(false));
		$Customers->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));

		$hash = sha1(date('Y-m-d') . $userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'confirm_close',
			'customerId' => $userId,
			'hash' => $hash,
		));
		$result = $this->testAction($url);
		$this->assertSame('failure', $result);
	}

	/**
	 * Confirm that the method returns an empty response if the request is
	 * not AJAX.
	 *
	 * @return void
	 */
	public function testConfirmCloseNotAjax() {
		$userId = 1;
		$hash = sha1(date('Y-m-d') . $userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'confirm_close',
			'customerId' => $userId,
			'hash' => $hash,
		]);
		$result = $this->testAction($url);
		$this->assertEmpty($result);
	}

	/**
	 * Confirm that all expected methods are called and a redirect happens.
	 *
	 * @return void
	 */
	public function testCloseAccountSuccess() {
		$userId = 1;
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'logout'),
				'Activity' => array('record'),
				'Flash' => array('set'),
			),
		));
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValue($userId));
		$Customers->Auth
			->expects($this->once())
			->method('logout');
		$Customers->Activity
			->expects($this->once())
			->method('record')
			->with('close');
		$Customers->Flash
			->expects($this->once())
			->method('set');
		$Customers->Customer = $this->getMockForModel('Customer', array('closeAccount'));
		$Customers->Customer->expects($this->once())
			->method('closeAccount')
			->will($this->returnValue(true));

		$hash = sha1(date('Y-m-d') . $userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'close_account',
			'hash' => $hash,
		));
		$result = $this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm that expected methods are called (and not called) and that a
	 * failure flash message is displayed and a redirect happens.
	 *
	 * @return void
	 */
	public function testCloseAccountFailure() {
		$userId = 1;
		$Customers = $this->generate('Customers', array(
			'components' => array(
				'Auth' => array('user', 'logout'),
				'Activity' => array('record'),
				'Flash' => array('set'),
			),
		));
		$Customers->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValue($userId));
		$Customers->Auth
			->expects($this->never())
			->method('logout');
		$Customers->Activity
			->expects($this->never())
			->method('record');
		$Customers->Flash
			->expects($this->once())
			->method('set');
		$Customers->Customer = $this->getMockForModel('Customer', array('closeAccount'));
		$Customers->Customer->expects($this->once())
			->method('closeAccount')
			->will($this->returnValue(false));

		$hash = sha1(date('Y-m-d') . $userId);
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'close_account',
			'hash' => $hash,
		));
		$result = $this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	/**
	 * Confirm a search by authorized name redirects to the expected customer page.
	 *
	 * @return	void
	 */
	public function testManagerSearchAuthorizedNameOneResult() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = '/manager/customers?q=Washington'; // 'Washington' is a unique `AuthorizedName.authorized_lastname`
		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
		$this->assertContains('2', $this->headers['Location'], 'Location should contain customer ID 2');
	}

	/**
	 * Confirm a search finds matching records both in Customer and AuthorizedName.
	 *
	 * @return void
	 */
	public function testManagerSearchAuthorizedNameMultipleResults() {
		$userId = 1;
		$search = 'SetDefaults';
		$expected = 2;
		$this->setupManagerAuth($userId);
		$url = '/manager/customers?q=' . strtolower($search);
		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect.');
		$this->assertArrayHasKey('results', $this->vars, '$this->vars should contain the results key');
		$this->assertSame($expected, count($this->vars['results']), 'Should have 2 records in the results array');
		$this->assertContains(
			$search, $this->vars['results'][0]['SearchIndex']['data'],
			'Should contain $search'
		);
		$this->assertSame(
			$search, $this->vars['results'][1]['Customer']['customers_firstname'],
			'Should match $search'
		);
	}

	/**
	 * Confirm that multiple words or email addresses are wrapped in `"` while
	 * other strings are not.
	 *
	 * @return void
	 * @dataProvider provideAuthoWrapFullnameOrEmail
	 */
	public function testAutoWrapFullnameOrEmail($search, $expected, $msg = '') {
		$Customers = $this->generate('TestCustomers');
		$result = $Customers->autoWrapFullnameOrEmail($search);
		$this->assertSame($expected, $result, $msg);
	}

	public function provideAuthoWrapFullnameOrEmail() {
		return [
			['foo', 'foo', 'should not be wrapped in quotes'],
			['foo bar', '"foo bar"', 'should be wrapped in quotes'],
			['Foo Bar Baz', '"Foo Bar Baz"', 'should be wrapped in quotes'],
			['Foo Bar Baz Boom', '"Foo Bar Baz Boom"', 'should be wrapped in quotes'],
			['foo@bar.com', '"foo@bar.com"', 'should be wrapped in quotes'],
			['12345678', '12345678', 'should not be wrapped in quotes'],
			['', '', 'should not be wrapped in quotes'],
			['"foo"', '"foo"', 'should be wrapped in quotes'],
			["foo o'bar", '"foo o\'bar"', 'should be wrapped in quotes'],
			["'foo'", "'foo'", 'should not be wrapped in quotes'],
			["'foo bar'", "\"'foo bar'\"", 'should be wrapped in quotes'],
			['foo o"bar', 'foo o"bar', 'should not be wrapped in quotes'],
			['foo `bar`', 'foo `bar`', 'should not be wrapped in quotes'],
		];
	}

	/**
	 * Confirm that when the customer id can't be found the expected exception
	 * is thrown.
	 *
	 * @return void
	 */
	public function testManagerRecentCustomerNotFound() {
		$userId = 1;
		$customerId = 99999;
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'recent',
			'id' => $customerId,
			'manager' => true,
		]);

		$this->setExpectedException('NotFoundException', 'Customer not found');
		$this->testAction($url);
	}

	/**
	 * Confirm that the expected view variables are set when the customer id
	 * is valid for a manager level admin.
	 *
	 * @return void
	 */
	public function testManagerRecentCustomerExists() {
		$userId = 1;
		$customerId = 1;
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'recent',
			'id' => $customerId,
			'manager' => true,
		]);

		$this->testAction($url);

		$vars = $Customers->viewVars;

		$this->assertArrayHasKey('orders', $vars);
		$this->assertArrayHasKey('Order', $vars['orders'][0]);
		$this->assertArrayHasKey('Customer', $vars['orders'][0]);
		$this->assertArrayHasKey('OrderStatus', $vars['orders'][0]);
	}

	/**
	 * Confirm that the expected view variables are set when the customer id
	 * is valid for an employee level admin.
	 *
	 * @return void
	 */
	public function testEmployeeRecentCustomerExists() {
		$userId = 2;
		$customerId = 1;
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'recent',
			'id' => $customerId,
			'employee' => true,
		]);

		$this->testAction($url);

		$vars = $Customers->viewVars;

		$this->assertArrayHasKey('orders', $vars);
		$this->assertArrayHasKey('Order', $vars['orders'][0]);
		$this->assertArrayHasKey('Customer', $vars['orders'][0]);
		$this->assertArrayHasKey('OrderStatus', $vars['orders'][0]);
	}

	/**
	 * Confirm that the expected methods are called when adding a default
	 * email message to the job queue.
	 *
	 * @return void
	 */
	public function testSendDefaultEmail() {
		$id = 1;

		$Customers = $this->generate('TestCustomers', [
			'methods' => 'taskFactory',
		]);
		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->disableOriginalConstructor()
			->getMock();

		$Customers->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));
		$Task->expects($this->once())
			->method('createJob')
			->with('AppEmail')
			->will($this->returnValue(true));

		$Customers->_sendDefaultEmail($id, 'subject', 'body');
	}

	/**
	 * Confirm that when a manager closes a customer account, the Activity
	 * component records a record, the expected Flash message is displayed,
	 * and a redirect to the customer view page occurs.
	 *
	 * @return void
	 */
	public function testManagerCloseAccountSuccess() {
		$userId = 1;
		$customerId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'close_account',
			'customerId' => $customerId,
			'manager' => true,
		]);

		$Customers = $this->generate('Customers', [
			'models' => [
				'Customer' => ['closeAccount'],
			],
			'components' => [
				'Activity' => ['record'],
				'Flash' => ['set'],
			],
		]);

		$Customers->Customer->expects($this->once())
			->method('closeAccount')
			->with($customerId)
			->will($this->returnValue(true));
		$Customers->Activity->expects($this->once())
			->method('record')
			->with('close', $customerId)
			->will($this->returnValue(true));
		$Customers->Flash->expects($this->once())
			->method('set')
			->with("The customer's APO Box account has been closed.");

		$this->testAction($url);

		$this->assertStringEndsWith(
			'/manager/customers/view/' . $customerId,
			$this->headers['Location'],
			'Location should contain customer ID'
		);
	}

	/**
	 * Confirm that when a manager closes a customer account but closing fails,
	 * the Activity component does not record a record, the expected Flash message,
	 * is displayed, and a redirect to the customer view page occurs.
	 *
	 * @return void
	 */
	public function testManagerCloseAccountFails() {
		$userId = 1;
		$customerId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'customers',
			'action' => 'close_account',
			'customerId' => $customerId,
			'manager' => true,
		]);

		$Customers = $this->generate('Customers', [
			'models' => [
				'Customer' => ['closeAccount'],
			],
			'components' => [
				'Activity' => ['record'],
				'Flash' => ['set'],
			],
		]);

		$Customers->Customer->expects($this->once())
			->method('closeAccount')
			->with($customerId)
			->will($this->returnValue(false));
		$Customers->Activity->expects($this->never())
			->method('record');
		$Customers->Flash->expects($this->once())
			->method('set')
			->with("There was a problem closing this customer's account.");

		$this->testAction($url);

		$this->assertStringEndsWith(
			'/manager/customers/view/' . $customerId,
			$this->headers['Location'],
			'Location should contain customer ID'
		);
	}

	/**
	 * Confirm that the manger report when accessed by GET returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerDemographicsReportGet() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'demographics_report',
			'manager' => true,
		));

		$this->testAction($url, array(
			'method' => 'get',
		));

		$this->assertArrayHasKey('options', $this->vars);
		$this->assertArrayHasKey('data', $this->vars);
		$this->assertTrue(!empty($this->vars['data']));
		$this->assertArrayHasKey('reportFields', $this->vars);
		$this->assertTrue(!empty($this->vars['reportFields']));
	}

	/**
	 * Confirm that the manger report with POSTed data returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerDemographicsReportPost() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers',
			'action' => 'demographics_report',
			'manager' => true,
		));

		$data = array(
			'limit' => 1,
		);

		$this->testAction($url, array(
			'method' => 'post',
			'data' => $data,
		));

		$this->assertArrayHasKey('options', $this->vars);
		$this->assertSame(1, $this->vars['options']['limit']);
		$this->assertArrayHasKey('data', $this->vars);
		$this->assertSame(1, count($this->vars['data']));
		$this->assertArrayHasKey('reportFields', $this->vars);
		$this->assertTrue(!empty($this->vars['reportFields']));
	}

	/**
	 * Confirm that when a `hash` key does not exist in request params the
	 * method returns false.
	 *
	 * @return void
	 */
	public function testCheckHashIsNull() {
		$Customers = $this->generate('TestCustomers', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$Customers->Auth->expects($this->never())
			->method('user');
		$result = $Customers->checkHash();
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when a `hash` key exists in request params the but does
	 * not match the expected value the method returns false.
	 *
	 * @return void
	 */
	public function testCheckHashDoesNotMatch() {
		$id = 1;
		$Customers = $this->generate('TestCustomers', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$Customers->Auth->staticExpects($this->once())
			->method('user')
			->will($this->returnValue($id));
		$Customers->request->params['hash'] = 'foo';
		$result = $Customers->checkHash();
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when a `hash` key exists in request params and matches the
	 * expected value the method returns the hash value.
	 *
	 * @return void
	 */
	public function testCheckHashMatches() {
		$id = 1;
		$Customers = $this->generate('TestCustomers', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$Customers->Auth->staticExpects($this->once())
			->method('user')
			->will($this->returnValue($id));
		$hash = sha1(date('Y-m-d') . $id);
		$Customers->request->params['hash'] = $hash;
		$result = $Customers->checkHash();
		$this->assertSame($hash, $result);
	}
}
