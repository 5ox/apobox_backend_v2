<?php
App::uses('AddressesController', 'Controller');

/**
 * TestAddressesController to access protected methods for direct testing.
 */
class TestAddressesController extends AddressesController {

	public $uses = ['Address'];

	public function _setLastCreatedAddressAsCustomersAddress($customerId = null) {
		return parent::_setLastCreatedAddressAsCustomersAddress($customerId);
	}
}

/**
 * AddressesController Test Case
 *
 */
class AddressesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.address',
		'app.country',
		'app.customer',
		'app.zone',
		'app.order',
		'app.order_status',
		'app.order_status_history',
		'app.admin',
		'app.authorized_name',
		'app.search_index',
		'app.queued_task',
	);

	/**
	 * tests that an address can be created correctly without adding it to a
	 * customer's default addresses.
	 *
	 * @return	void
	 */
	public function testAdd() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$Addresses = $this->setupAuth($userId);
		$Addresses->Address->recursive = -1;
		$beforeCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $userId)
		));
		$data = array('Address' => array(
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => ''
		));

		$this->testAction('/address/add/', array('data' => $data));
		$afterCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $userId)
		));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $userId,
				'Address.entry_firstname' => $firstName,
			),
		));

		$this->assertArrayHasKey('Address', $record);
		$this->assertEqual($firstName, $record['Address']['entry_firstname']);
		$this->assertStringEndsWith('/account', ($this->headers['Location']));
		$this->assertEquals(($beforeCount+1), $afterCount, 'More than one record was created.');
	}

	/**
	 * test a GET request
	 *
	 * @return	void
	 */
	public function testAddGet() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/add/', array('method' => 'get'));

		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'Should not redirect');
	}

	/**
	 * tests that an address with bad data fails and does not redirect
	 *
	 * @return	void
	 */
	public function testAddBadData() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => '',
			'entry_lastname' =>  '',
			'entry_street_address' => '',
			'entry_suburb' => '',
			'entry_postcode' => '',
			'entry_city' => '',
			'entry_country_id' => 223,
			'entry_zone_id' => '',
			'entry_basename' => ''
		));

		$this->testAction('/address/add/', array('data' => $data));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $userId,
				'Address.entry_firstname' => $firstName,
			),
		));

		$this->assertTrue(empty($record));
		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'page should not redirect');
	}

	/**
	 * test that an address can be added as a customer's
	 * non-shipping addres and redirect to account page.
	 *
	 * @return	void
	 */
	public function testAddAndMakeCustomersBilling() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$Addresses = $this->setupAuth($userId);

		$Addresses->Address->recursive = -1;
		$beforeCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $userId)
		));
		$data = array('Address' => array(
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 1,
			'entry_basename' => '',
			'make_this_my' => 'billing'
		));

		$this->testAction('/address/add/', array('data' => $data));
		$afterCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $userId)
		));
		$addressRecord = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $userId,
				'Address.entry_firstname' => $firstName,
			),
		));
		$customerRecord = $Addresses->Address->Customer->find('first', array(
			'conditions' => array(
				'Customer.customers_id' => $userId,
			),
		));

		$this->assertArrayHasKey('Address', $addressRecord);
		$this->assertEqual($firstName, $addressRecord['Address']['entry_firstname']);
		$this->assertEqual($addressRecord['Address']['address_book_id'], $customerRecord['Customer']['customers_default_address_id']);
		$this->assertStringEndsWith('/account', ($this->headers['Location']));
		$this->assertEquals(($beforeCount+1), $afterCount, 'More than one record was created.');
	}

	/**
	 * test that an address can be added as a customer's
	 * shipping addres and redirect to account page.
	 *
	 * @return	void
	 */
	public function testAddAndMakeCustomersShipping() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => '',
			'make_this_my' => 'shipping'
		));

		$this->testAction('/address/add/', array('data' => $data));
		$addressRecord = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $userId,
				'Address.entry_firstname' => $firstName,
			),
		));
		$customerRecord = $Addresses->Address->Customer->find('first', array(
			'conditions' => array(
				'Customer.customers_id' => $userId,
			),
		));

		$this->assertArrayHasKey('Address', $addressRecord);
		$this->assertEqual($firstName, $addressRecord['Address']['entry_firstname']);
		$this->assertArrayHasKey('Customer', $customerRecord);
		$this->assertEqual($addressRecord['Address']['address_book_id'], $customerRecord['Customer']['customers_shipping_address_id'], 'Customer record was not updated.');
		$this->assertStringEndsWith('/account', ($this->headers['Location']));
	}

	/**
	 * Confirm that when an addresses is added and successfully saved but
	 * can't be updated as a default address the expected flash message is
	 * displayed and the redirect is back to 'default_addresses'.
	 *
	 * @return void
	 */
	public function testAddAndMakeCustomersAddressFailure() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$this->setupAuth($userId);

		$Controller = $this->generate('Addresses', [
			'components' => [
				'Auth' => ['user'],
				'Flash' => ['set'],
			],
			'methods' => [
				'_setLastCreatedAddressAsCustomersAddress',
			],
		]);

		$Controller->expects($this->once())
			->method('_setLastCreatedAddressAsCustomersAddress')
			->will($this->returnValue(false));
		$Controller->Flash->expects($this->exactly(2))
			->method('set')
			->with($this->logicalOr(
				'The address book has been saved.',
				'We were unable to set the new address as your shipping address'
			));

		$data = ['Address' => [
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => '',
			'make_this_my' => 'shipping'
		]];

		$this->testAction('/address/add/', ['data' => $data]);
		$this->assertStringEndsWith('/default_addresses', ($this->headers['Location']));
	}

	/**
	 * Test that a valid APO formatted address can be added.
	 */
	public function testAddJsonSuccess() {
		$userId = 1;
		$newAddress = '123 DOES NOT EXIST YET Rd';
		$Addresses = $this->mockAuthAndTask($userId, true);
		$countBefore = $Addresses->Address->find('count');
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
		));
		$data = json_encode([
			'data' => [
				'type' => 'shipping_addresses',
				'attributes' => [
					'entry_street_address' => $newAddress,
					'entry_postcode' => '12345',
					'entry_city' => 'APO',
					'entry_zone_id' => '7',
					'entry_country_id' => '1',
				],
			],
		]);

		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));

		$countAfter = $Addresses->Address->find('count');
		$addressAfter = $Addresses->Address->findByEntryStreetAddress($newAddress);
		$this->assertTrue((bool)$addressAfter);
		$this->assertSame('1', $addressAfter['Address']['entry_country_id']);
		$this->assertSame($countBefore+1, $countAfter);
	}

	/**
	 * Test that an address without a country can be added with the default
	 * country set.
	 */
	public function testAddJsonWithoutCountry() {
		$userId = 1;
		$Addresses = $this->mockAuthAndTask($userId, true);
		$countBefore = $Addresses->Address->find('count');
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
		));
		$data = json_encode([
			'data' => [
				'type' => 'shipping_addresses',
				'attributes' => [
					'entry_street_address' => '123 Test Rd',
					'entry_postcode' => '12345',
					'entry_city' => 'APO',
					'entry_zone_id' => '7',
				],
			],
		]);

		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));

		$countAfter = $Addresses->Address->find('count');
		$addressAfter = $Addresses->Address->read();
		$this->assertTrue((bool)$addressAfter);
		$this->assertSame('223', $addressAfter['Address']['entry_country_id']);
		$this->assertSame($countBefore+1, $countAfter);
	}

	/**
	 * Test that posting a wrong data type will throw an exception.
	 */
	public function testAddJsonFailsWithWrongDataType() {
		$userId = 1;
		$Addresses = $this->setupAuth($userId);
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
		));
		$data = json_encode([
			'data' => [
				'type' => 'addresses',
				'attributes' => [
				],
			],
		]);

		$this->setExpectedException(
			'BadRequestException',
			'Invalid type set, must set type ShippingAddress for this endpoint'
		);

		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 * Test that posting missing address data will throw an exception.
	 *
	 * @expectedException BaseSerializerException
	 */
	public function testAddJsonFailsWithMissingData() {
		$userId = 1;
		$Addresses = $this->setupAuth($userId);
		$Controller = $this->generate('Addresses', ['methods' => ['logBaseSerializerException']]);
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
		));
		$data = json_encode([
			'data' => [
				'type' => 'shipping_addresses',
				'attributes' => [
				],
			],
		]);

		$Controller->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->anything(),
				$this->identicalTo('ShippingAddress'),
				$this->identicalTo('customers'),
				$this->anything()
			);

		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 * Test that posting non APO address data will throw an exception.
	 *
	 * @expectedException BaseSerializerException
	 */
	public function testAddJsonFailsWithNonApoData() {
		$userId = 1;
		$Addresses = $this->setupAuth($userId);
		$Controller = $this->generate('Addresses', ['methods' => ['logBaseSerializerException']]);
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$url = Router::url(array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
		));
		$data = json_encode([
			'data' => [
				'type' => 'shipping_addresses',
				'attributes' => [
					'entry_street_address' => '123 Test Rd',
					'entry_postcode' => '12345',
					'entry_city' => 'Sometown',
					'entry_zone_id' => '1',
				],
			],
		]);

		$Controller->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->anything(),
				$this->identicalTo('ShippingAddress'),
				$this->identicalTo('customers'),
				$this->anything()
			);

		$this->testAction($url, array(
			'data' => $data,
			'method' => 'post'
		));
	}

	/**
	 * Test that we can sucessfully edit an address and be redirected
	 *
	 * @return	void
	 */
	public function testEdit() {
		$userId = 1;
		$addressId = 1;
		$newFirstName = 'John';
		$newLastName = 'Doe';

		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'entry_firstname' => $newFirstName,
			'entry_lastname' =>  $newLastName,
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => 'Lorem ipsum dolor sit amet',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_state' => '',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'put', 'data' => $data));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.entry_firstname' => $newFirstName,
				'Address.entry_lastname' => $newLastName,
			),
		));

		$this->assertArrayHasKey('Address', $record, 'Address record was not found after save.');
		$this->assertEqual($newFirstName, $record['Address']['entry_firstname']);
		$this->assertStringEndsWith('/account', ($this->headers['Location']));
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testEditGet() {
		$userId = 1;
		$addressId = 1;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'get'));

		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('addressName', $this->vars);
		$this->assertArrayHasKey('Address', $Addresses->request->data);
		$this->assertTrue(empty($this->headers['Location']));
	}

	/**
	 *
	 *
	 * @expectedException ForbiddenException
	 * @return	void
	 */
	public function testEditUserDoesNotOwnAddress() {
		$userId = 1;
		$addressId = 2;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'get'));

	}

	/**
	 *
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testEditAddressNotExists() {
		$userId = 1;
		$addressId = 999;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'get'));

	}

	/**
	 * Test put data fails validation
	 *
	 * @return	void
	 */
	public function testEditValidationFails() {
		$userId = 1;
		$addressId = 1;
		$newFirstName = 'John';
		$newLastName = 'Doe';

		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'entry_firstname' => '', // User tring to save without a name
			'entry_lastname' =>  '',
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => 'Lorem ipsum dolor sit amet',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'put', 'data' => $data));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.entry_firstname' => $newFirstName,
				'Address.entry_lastname' => $newLastName,
			),
		));

		$this->assertEmpty($record);
		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('Address', $Addresses->request->data);
		$this->assertTrue(empty($this->headers['Location']));
	}

	/**
	 * Test put data fails validation
	 *
	 * @return	void
	 */
	public function testEditValidationFailsShippingAddressInUse() {
		$userId = 1;
		$newFirstName = 'John';
		$newLastName = 'Doe';

		$Addresses = $this->setupAuth($userId);

		$customer = $Addresses->Address->Customer->find('first', array(
			'conditions' => array('Customer.customers_id' => $userId),
		));

		// Shipping address in use
		$addressId = $customer['Customer']['customers_shipping_address_id'];

		$data = array('Address' => array(
			'entry_firstname' => '', // User tring to save without a name
			'entry_lastname' =>  '',
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => 'Lorem ipsum dolor sit amet',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'put', 'data' => $data));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.entry_firstname' => $newFirstName,
				'Address.entry_lastname' => $newLastName,
			),
		));

		$this->assertEmpty($record);
		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('Address', $Addresses->request->data);
		$this->assertArrayHasKey('entry_zone_id', $Addresses->request->data['Address']);
		$this->assertArrayNotHasKey('ShippingAddress', $Addresses->request->data);
		$this->assertTrue(empty($this->headers['Location']));
	}

	/**
	 * Test POSTing valid data
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testEditBadMethod() {
		$userId = 1;
		$addressId = 1;
		$newFirstName = 'John';
		$newLastName = 'Doe';

		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'entry_firstname' => $newFirstName,
			'entry_lastname' =>  $newLastName,
			'entry_gender' => 'Lorem ipsum dolor sit ame',
			'entry_company' => 'Lorem ipsum dolor sit amet',
			'entry_street_address' => 'Lorem ipsum dolor sit amet',
			'entry_suburb' => 'Lorem ipsum dolor sit amet',
			'entry_postcode' => 'Lorem ip',
			'entry_city' => 'Lorem ipsum dolor sit amet',
			'entry_state' => '',
			'entry_country_id' => 163,
			'entry_zone_id' => 1,
			'entry_basename' => 'Lorem ipsum dolor sit amet'
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'post', 'data' => $data));
	}

	/**
	 * Test that editing a shipping address you can enter a non APO address.
	 */
	public function testEditCurrentShippingAddress() {
		$userId = 1;
		$addressId = 5;
		$validCity = 'Not an APO City';

		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'entry_city' => $validCity,
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'put', 'data' => $data));
		$record = $Addresses->Address->findByAddressBookId($addressId);

		$this->assertEqual($validCity, $record['Address']['entry_city'], 'Failed not saving an invalid shipping address.');
	}

	/**
	 * Test that editing an emergency address you can enter a non APO address.
	 */
	public function testEditCurrentEmergencyAddress() {
		$userId = 1;
		$addressId = 6;
		$validCity = 'Not an APO City';

		$Addresses = $this->setupAuth($userId);

		$data = array('Address' => array(
			'entry_city' => $validCity,
		));

		$this->testAction('/address/' . $addressId . '/edit', array('method' => 'put', 'data' => $data));
		$record = $Addresses->Address->findByAddressBookId($addressId);

		$this->assertEqual($validCity, $record['Address']['entry_city'], 'Failed not saving an invalid backup shipping address.');
	}

	/**
	 * Test that we can sucessfully delete an address and be redirected
	 *
	 * @return	void
	 */
	public function testDelete() {
		$userId = 1;
		$addressId = 4;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'delete'));
		$record = $Addresses->Address->findByAddressBookId($addressId);

		$this->assertEmpty($record);
		$this->assertStringEndsWith('/account', ($this->headers['Location']));
	}

	/**
	 *
	 *
	 * @expectedException ForbiddenException
	 * @return	void
	 */
	public function testDeleteUserDoesNotOwnAddress() {
		$userId = 1;
		$addressId = 2;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'delete'));

	}

	/**
	 *
	 *
	 * @expectedException NotFoundException
	 * @return	void
	 */
	public function testDeleteAddressNotExists() {
		$userId = 1;
		$addressId = 999;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'delete'));

	}

	/**
	 * Test delete with GET verb
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testDelteWithBadMethodGet() {
		$userId = 1;
		$addressId = 1;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'get'));
	}

	/**
	 * Test delete with PUT verb
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testDelteWithBadMethodPut() {
		$userId = 1;
		$addressId = 1;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'put'));
	}

	/**
	 * Test delete with POST verb
	 * @expectedException MethodNotAllowedException
	 * @return	void
	 */
	public function testDeleteWithBadMethodPost() {
		$userId = 1;
		$addressId = 1;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'post'));
	}

	/**
	 * Test that deleting fails when address is in use and redirects.
	 *
	 * @return	void
	 */
	public function testDeleteAddressInUse() {
		$userId = 1;
		$addressId = 1;

		$Addresses = $this->setupAuth($userId);

		$this->testAction('/address/' . $addressId . '/delete', array('method' => 'delete'));
		$record = $Addresses->Address->findByAddressBookId($addressId);

		$this->assertNotEmpty($record);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringEndsWith('edit/addresses', $this->headers['Location']);
	}

	/**
	 * Confirm that when a delete operation fails, the expected flash message
	 * is displayed and a redirect does not happen.
	 *
	 * @return void
	 */
	public function testDeleteFailure() {
		$userId = 1;
		$addressId = 7;
		$this->setupAuth($userId);

		$Controller = $this->generate('Addresses', [
			'components' => [
				'Auth' => ['user'],
				'Flash' => ['set'],
			],
			'models' => [
				'Address' => ['delete'],
			],
		]);

		$Controller->Address->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$Controller->Flash->expects($this->once())
			->method('set')
			->with('The address could not be deleted.');

		$this->testAction('/address/' . $addressId . '/delete', ['method' => 'delete']);
		$this->assertArrayNotHasKey('Location', $this->headers);
	}

	/**
	 * tests that an address can be created correctly by a manager without
	 * adding it to a customer's default addresses.
	 *
	 * @return	void
	 */
	public function testManagerAdd() {
		$userId = 1;
		$customerId = 2;
		$firstName = 'UkP1JL9s';
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);
		$Addresses->Address->recursive = -1;
		$beforeCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$data = array('Address' => array(
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => ''
		));

		$this->testAction($url, array('data' => $data));
		$afterCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $customerId,
				'Address.entry_firstname' => $firstName,
			),
		));

		$this->assertArrayHasKey('Address', $record);
		$this->assertEqual($firstName, $record['Address']['entry_firstname']);
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
		$this->assertEquals(($beforeCount+1), $afterCount, 'More than one record was created.');
	}

	/**
	 * test a GET request
	 *
	 * @return	void
	 */
	public function testManagerAddGET() {
		$userId = 1;
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('United States', $this->vars['zones']);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'Should not redirect');
	}

	/**
	 * tests that an address with bad data fails and does not redirect
	 *
	 * @return	void
	 */
	public function testManagerAddBadData() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);

		$data = array('Address' => array(
			'entry_company' => '',
			'entry_firstname' => '',
			'entry_lastname' =>  '',
			'entry_street_address' => '',
			'entry_suburb' => '',
			'entry_postcode' => '',
			'entry_city' => '',
			'entry_country_id' => 223,
			'entry_zone_id' => '',
			'entry_basename' => ''
		));

		$this->testAction($url, array('data' => $data));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $customerId,
				'Address.entry_firstname' => $firstName,
			),
		));

		$this->assertTrue(empty($record));
		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'page should not redirect');
	}

	/**
	 * test that an address can be added as a customer's
	 * non-shipping addres and redirect to viewing the customer.
	 *
	 * @return	void
	 */
	public function testManagerAddAndMakeCustomersBilling() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);

		$Addresses->Address->recursive = -1;
		$beforeCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$data = array('Address' => array(
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 1,
			'entry_basename' => '',
			'make_this_my' => 'billing'
		));

		$this->testAction($url, array('data' => $data));
		$afterCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$addressRecord = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $customerId,
				'Address.entry_firstname' => $firstName,
			),
		));
		$customerRecord = $Addresses->Address->Customer->find('first', array(
			'conditions' => array(
				'Customer.customers_id' => $customerId,
			),
		));
		$this->assertArrayHasKey('Address', $addressRecord);
		$this->assertEquals($firstName, $addressRecord['Address']['entry_firstname']);
		$this->assertEquals($addressRecord['Address']['address_book_id'], $customerRecord['Customer']['customers_default_address_id'], 'New address was not saved as the default addresss.');
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
		$this->assertEquals(($beforeCount+1), $afterCount, 'More than one record was created.');
	}

	/**
	 * test that an address can be added as a customer's
	 * shipping addres and redirect to viewing the customer.
	 *
	 * @return	void
	 */
	public function testManagerAddAndMakeCustomersShipping() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);

		$data = array('Address' => array(
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => '',
			'make_this_my' => 'shipping'
		));

		$this->testAction($url, array('data' => $data));
		$addressRecord = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $customerId,
				'Address.entry_firstname' => $firstName,
			),
		));
		$customerRecord = $Addresses->Address->Customer->find('first', array(
			'conditions' => array(
				'Customer.customers_id' => $customerId,
			),
		));

		$this->assertArrayHasKey('Address', $addressRecord);
		$this->assertEqual($firstName, $addressRecord['Address']['entry_firstname']);
		$this->assertArrayHasKey('Customer', $customerRecord);
		$this->assertEqual($addressRecord['Address']['address_book_id'], $customerRecord['Customer']['customers_shipping_address_id'], 'Customer record was not updated.');
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'manager' => true,
		)), $this->headers['Location']);
	}

	/**
	 * Confirm the expected exception is thrown when the HTTP method is not
	 * GET or POST.
	 *
	 * @return void
	 */
	public function testManagerAddWrongType() {
		$userId = 1;
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);
		$this->setExpectedException('MethodNotAllowedException', 'Method must be GET or POST');
		$this->testAction($url, ['method' => 'delete']);
	}

	/**
	 * Confirm the expected exception is thrown when the requested customer is
	 * invalid.
	 *
	 * @return void
	 */
	public function testManagerAddInvalidCustomer() {
		$userId = 1;
		$customerId = 999999999;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);
		$this->setExpectedException('NotFoundException', 'The requested customer was not found.');
		$this->testAction($url, ['method' => 'get']);
	}

	/**
	 * Confirm that when an addresses is added and successfully saved but
	 * can't be updated as a default address the expected flash message is
	 * displayed and the redirect is back to 'edit-addresses'.
	 *
	 * @return void
	 */
	public function testManagerAddAndMakeCustomersAddressFailure() {
		$userId = 1;
		$firstName = 'UkP1JL9s';
		$customerId = 2;
		$this->setupAdminAuth($userId);
		$url = Router::url([
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'manager' => true,
		]);

		$Controller = $this->generate('Addresses', [
			'components' => [
				'Auth' => ['user'],
				'Flash' => ['set'],
			],
			'methods' => [
				'_setLastCreatedAddressAsCustomersAddress',
			],
		]);

		$Controller->expects($this->once())
			->method('_setLastCreatedAddressAsCustomersAddress')
			->will($this->returnValue(false));
		$Controller->Flash->expects($this->exactly(2))
			->method('set')
			->with($this->logicalOr(
				'The address has been saved.',
				'We were unable to set the new address as the customers billing address'
			));

		$data = ['Address' => [
			'customers_id' => $userId,
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => '',
			'make_this_my' => 'billing'
		]];

		$this->testAction($url, ['data' => $data]);
		$this->assertStringEndsWith('/default-addresses', ($this->headers['Location']));
	}

	/**
	 * tests that an address can be created correctly by an employee without
	 * adding it to a customer's default addresses.
	 *
	 * @return	void
	 */
	public function testEmployeeAdd() {
		$userId = 2;
		$customerId = 2;
		$firstName = 'UkP1JL9s';
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'employee' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);
		$Addresses->Address->recursive = -1;
		$beforeCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$data = array('Address' => array(
			'entry_company' => '',
			'entry_firstname' => $firstName,
			'entry_lastname' =>  'Last',
			'entry_street_address' => '1234 Somewhere St.',
			'entry_suburb' => '',
			'entry_postcode' => '12345',
			'entry_city' => 'APO',
			'entry_country_id' => 223,
			'entry_zone_id' => 7,
			'entry_basename' => ''
		));

		$this->testAction($url, array('data' => $data));
		$afterCount = $Addresses->Address->find('count', array(
			'conditions' => array('customers_id' => $customerId)
		));
		$record = $Addresses->Address->find('first', array(
			'conditions' => array(
				'Address.customers_id' => $customerId,
				'Address.entry_firstname' => $firstName,
			),
		));

		$this->assertArrayHasKey('Address', $record);
		$this->assertEqual($firstName, $record['Address']['entry_firstname']);
		$this->assertStringEndsWith(Router::url(array(
			'controller' => 'customers',
			'action' => 'view',
			'id' => $customerId,
			'employee' => true,
		)), $this->headers['Location']);
		$this->assertEquals(($beforeCount + 1), $afterCount, 'More than one record was created.');
	}

	/**
	 * test a GET request as an employee
	 *
	 * @return	void
	 */
	public function testEmployeeAddGET() {
		$userId = 2;
		$customerId = 2;
		$url = Router::url(array(
			'controller' => 'addresses',
			'action' => 'add',
			'customerId' => $customerId,
			'employee' => true,
		));
		$Addresses = $this->setupAdminAuth($userId);

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('zones', $this->vars);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertTrue(empty($this->headers['Location']), 'Should not redirect');
	}

	protected function setupAuth($userId) {
		$Addresses = $this->generate('Addresses', array(
			'components' => array(
				'Auth' => array('user')
			)
		));

		$authUser = function($id) {
			$user = ClassRegistry::init('Customer')->find('first', array(
				'contain' => array(),
				'conditions' => array('customers_id' => $id),
			));
			return array('User' => $user['Customer']);
		};
		$userMap = array(
			array('customers_id', $userId),
			array(null, $authUser($userId)),
		);
		$Addresses->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValueMap($userMap));

		return $Addresses;
	}

	protected function setupAdminAuth($userId) {
		Configure::write('Security.admin.ips', false);

		$Addresses = $this->generate('Addresses', array(
			'components' => array(
				'Auth' => array('user')
			)
		));

		$authUser = function($id) {
			$user = ClassRegistry::init('Admin')->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id),
			));
			return array('User' => $user['Admin']);
		};
		$userMap = array(
			array('id', $userId),
			array(null, $authUser($userId)),
		);
		$Addresses->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValueMap($userMap));

		return $Addresses;
	}

	/**
	 * Mocks the addresses controller, Auth, and the taskFactory method. Tells
	 * AppEmail what to return on send.
	 *
	 * @param array $authMap The value map of auth responses.
	 * @param bool $returnValue The return value of send.
	 * @return The customer controller object.
	 */
	protected function mockAuthAndTask($userId, $returnValue) {
		$Addresses = $this->generate('Addresses', array(
			'methods' => array('taskFactory'),
			'components' => array(
				'Auth' => array('user')
			)
		));
		$Task = $this->getMock('Task', array('createJob'));
		$Task->expects($this->once())
			->method('createJob')
			->will($this->returnValue(true));
		$Addresses->expects($this->once())
			->method('taskFactory')
			->will($this->returnValue($Task));

		$authUser = function($id) {
			$user = ClassRegistry::init('Customer')->find('first', array(
				'contain' => array(),
				'conditions' => array('customers_id' => $id),
			));
			return array('User' => $user['Customer']);
		};
		$userMap = array(
			array('customers_id', $userId),
			array(null, $authUser($userId)),
		);
		$Addresses->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnValueMap($userMap));

		return $Addresses;
	}

	/**
	 * Confirm that if no `make_this_my` value is passed, the method sets it to
	 * `billing`.
	 *
	 * @return void
	 */
	public function testSetLastCreatedAddressAsCustomersAddressSetsTypeIfNotSet() {
		$customerId = 1;
		$Addresses = $this->generate('TestAddresses');
		$result = $Addresses->_setLastCreatedAddressAsCustomersAddress($customerId);
		$this->assertArrayHasKey('Address', $Addresses->request->data);
		$this->assertArrayHasKey('make_this_my', $Addresses->request->data['Address']);
		$this->assertSame('billing', $Addresses->request->data['Address']['make_this_my']);
	}

	/**
	 * Confirm that if the `make_this_my` address type is invalid, the save
	 * is not attempted and the method returns false.
	 *
	 * @return void
	 */
	public function testSetLastCreatedAddressAsCustomersAddressInvalidType() {
		$customerId = 1;
		$Addresses = $this->generate('TestAddresses', [
			'models' => [
				'Address' => ['getInsertId'],
			],
		]);

		$Addresses->Address->expects($this->never())
			->method('getInsertId');

		$Addresses->request->data['Address']['make_this_my'] = 'foo';
		$result = $Addresses->_setLastCreatedAddressAsCustomersAddress($customerId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when the `make_this_my` address field cannot be saved, the
	 * method returns false.
	 *
	 * @return void
	 */
	public function testSetLastCreatedAddressAsCustomersAddressSaveFailure() {
		$customerId = 1;
		$Addresses = $this->generate('TestAddresses', [
			'models' => [
				'Address' => ['getInsertId'],
				'Customer' => ['saveField'],
			],
		]);

		$Addresses->Address->expects($this->once())
			->method('getInsertId')
			->will($this->returnValue(999));

		$Addresses->Address->Customer->expects($this->once())
			->method('saveField')
			->will($this->returnValue(false));

		$result = $Addresses->_setLastCreatedAddressAsCustomersAddress($customerId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that when the `make_this_my` address field is successfully saved,
	 * the method returns true.
	 *
	 * @return void
	 */
	public function testSetLastCreatedAddressAsCustomersAddressSaveSuccess() {
		$customerId = 1;
		$Addresses = $this->generate('TestAddresses', [
			'models' => [
				'Address' => ['getInsertId'],
				'Customer' => ['saveField'],
			],
		]);

		$Addresses->Address->expects($this->once())
			->method('getInsertId')
			->will($this->returnValue(999));

		$Addresses->Address->Customer->expects($this->once())
			->method('saveField')
			->will($this->returnValue(true));

		$result = $Addresses->_setLastCreatedAddressAsCustomersAddress($customerId);
		$this->assertTrue($result);
	}
}
