<?php
App::uses('AddressBook', 'Model');

/**
 * AddressBook Test Case
 *
 */
class AddressTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.address',
		'app.customer',
		'app.zone',
		'app.country',
		'app.authorized_name',
		'app.search_index',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->AddressBook = ClassRegistry::init('Address');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->AddressBook);

		parent::tearDown();
	}

	public function testConstructSetsFullField() {
		$address = $this->AddressBook->find('first');
		$this->AssertArrayHasKey('full', $address['Address']);
	}

	public function testConstructSetsFullAndFullHasZone() {
		$address = $this->AddressBook->find('first');
		$this->assertArrayHasKey('full', $address['Address']);
		$this->assertTextEndsWith(', AL', $address['Address']['full']);
	}

	public function testBeforeValidateStripsCharsFromAddressFields() {
		$line1 = '1234 Street Rd.*(&^%&)!@/-';
		$line2 = 'Apt. #67890/-';
		$expectedLine1 = '1234 Street Rd./-';
		$expectedLine2 = $line2;

		$data = array(
			'entry_street_address' => $line1,
			'entry_suburb' => $line2
		);

		$this->AddressBook->set($data);

		$address = $this->AddressBook->beforeValidate();

		$this->assertEqual($expectedLine1, $this->AddressBook->data[$this->AddressBook->alias]['entry_street_address']);
		$this->assertEqual($expectedLine2, $this->AddressBook->data[$this->AddressBook->alias]['entry_suburb']);
	}

	public function testBeforeValidateNotFailsOnStripCharsWhenKeysNotExist() {
		$this->AddressBook = $this->getMockForModel('Address', array('stripInvalidAddressChars'));
		$this->AddressBook->expects($this->once())
			->method('stripInvalidAddressChars');
		$data = array(
			'entry_postcode' => '12345-6789'
		);

		$this->AddressBook->set($data);

		$address = $this->AddressBook->beforeValidate();

		$this->assertArrayNotHasKey('entry_street_address', $this->AddressBook->data[$this->AddressBook->alias]);
		$this->assertArrayNotHasKey('entry_suburb', $this->AddressBook->data[$this->AddressBook->alias]);
	}

	public function testFindForPaymentStandard() {
		$result = $this->AddressBook->findForPayment(1);

		$this->assertNotEmpty($result['Address']['entry_firstname']);
		$this->assertNotEmpty($result['Address']['entry_lastname']);
		$this->assertNotEmpty($result['Address']['entry_street_address']);
		$this->assertNotEmpty($result['Address']['entry_suburb']);
		$this->assertNotEmpty($result['Address']['entry_city']);
		$this->assertNotEmpty($result['Address']['entry_postcode']);
		$this->assertNotEmpty($result['Zone']['zone_code']);
		$this->assertNotEmpty($result['Country']['countries_name']);
		$this->assertNotEmpty($result['Country']['countries_iso_code_2']);
		$this->assertNotEmpty($result['Country']['address_format_id']);
	}

	public function testFindForPaymentWithCustomerId() {
		$result = $this->AddressBook->findForPayment(1, 1);

		$this->assertNotEmpty($result['Address']['entry_firstname']);
		$this->assertNotEmpty($result['Address']['entry_lastname']);
		$this->assertNotEmpty($result['Address']['entry_street_address']);
		$this->assertNotEmpty($result['Address']['entry_suburb']);
		$this->assertNotEmpty($result['Address']['entry_city']);
		$this->assertNotEmpty($result['Address']['entry_postcode']);
		$this->assertNotEmpty($result['Zone']['zone_code']);
		$this->assertNotEmpty($result['Country']['countries_name']);
		$this->assertNotEmpty($result['Country']['countries_iso_code_2']);
		$this->assertNotEmpty($result['Country']['address_format_id']);
	}

	public function testFindForPaymentWithWrongCustomerId() {
		$result = $this->AddressBook->findForPayment(1, 2);
		$this->assertEmpty($result);
	}

	/**
	 * Test that a zone is attached when address data is passed directly in.
	 */
	public function testAttachZonePassedIn() {
		$address = ['Address' => ['entry_zone_id' => 1]];
		$result = $this->AddressBook->attachZone($address);
		$this->assertSame('AL', $result['Zone']['zone_code']);
	}

	/**
	 * Test that a zone is attached when address data from model is used.
	 */
	public function testAttachZoneModelHasData() {
		$data = $this->AddressBook->read(null, 1);
		$this->AddressBook->set($data);

		$result = $this->AddressBook->attachZone();

		$this->assertSame('AL', $result['Zone']['zone_code']);
	}

	/**
	 * Test that attachZone still passes if zone already exists.
	 */
	public function testAttachZoneWithZone() {
		$data = $this->AddressBook->findForPayment(1);
		$data['Address']['entry_zone_id'] = 1;
		$this->AddressBook->set($data);

		$result = $this->AddressBook->attachZone();

		$this->assertSame('AL', $result['Zone']['zone_code']);
	}

	/**
	 * Test that attachZone throws an execption if entry_zone_id is not passed
	 * in Address array of data.
	 *
	 * @expectedException CakeException
	 */
	public function testAttachZoneMissingZoneId() {
		$address = ['Address' => []];
		$this->AddressBook->attachZone($address);
	}

	/**
	 * Test that attachZone throws an execption if entry_zone_id does not match
	 * an existing zone.
	 *
	 * @expectedException CakeException
	 */
	public function testAttachZoneNoMatchingZone() {
		$address = ['Address' => ['entry_zone_id' => 'non_existent']];
		$this->AddressBook->attachZone($address);
	}

	/**
	 * Test that attachZone throws an execption if zone is passed in, but does
	 * not match the Zone record already attached to the address.
	 *
	 * @expectedException CakeException
	 */
	public function testAttachZoneMismatchOfData() {
		$address = [
			'Address' => ['entry_zone_id' => '47'],
			'Zone' => ['zone_code' => 'WONT-MATCH'],
		];
		$this->AddressBook->attachZone($address);
	}

	/**
	 * Test that setDefaultsForCustomer will properly set the default and
	 * shipping addresses for a customer. This address must be an APO/FPO
	 * address in order to pass shipping validation.
	 */
	public function testSetDefaultsForCustomer() {
		$customerId = 7;
		$addressId = 10;

		$this->AddressBook->id = $addressId;
		$this->AddressBook->setDefaultsForCustomer($customerId);

		$customer = $this->AddressBook->Customer->read(null, $customerId);

		$this->assertSame('10', $customer['Customer']['customers_default_address_id']);
		$this->assertSame('10', $customer['Customer']['customers_shipping_address_id']);
	}

	/**
	 * Test that a country is attached when address data is passed directly in.
	 */
	public function testAttachCountryPassedIn() {
		$address = ['Address' => ['entry_country_id' => 223]];
		$result = $this->AddressBook->attachCountry($address);
		$this->assertSame('United States', $result['Country']['countries_name']);
		$this->assertSame('2', $result['Country']['address_format_id']);
	}

	/**
	 * Test that a country is attached when address data from model is used.
	 */
	public function testAttachCountryModelHasData() {
		$data = $this->AddressBook->read(null, 1);
		$this->AddressBook->set($data);

		$result = $this->AddressBook->attachCountry();
		$this->assertSame('Costa Rica', $result['Country']['countries_name']);
		$this->assertSame('1', $result['Country']['address_format_id']);

	}

	/**
	 * Test that attachCountry still passes if country already exists.
	 */
	public function testAttachCountryWithCountry() {
		$data = $this->AddressBook->findForPayment(1);
		$data['Address']['entry_country_id'] = 163;
		$this->AddressBook->set($data);

		$result = $this->AddressBook->attachCountry();
		$this->assertSame('Costa Rica', $result['Country']['countries_name']);
		$this->assertSame('1', $result['Country']['address_format_id']);
	}

	/**
	 * Test that attachCountry throws an execption if entry_country_id is not passed
	 * in Address array of data.
	 *
	 * @return void
	 */
	public function testAttachCountryMissingZoneId() {
		$address = ['Address' => []];

		$this->setExpectedException(
			'CakeException',
			'Asked to attachCountry, but did not provide Address.entry_country_id.'
		);

		$this->AddressBook->attachCountry($address);
	}

	/**
	 * Test that attachCountry throws an execption if entry_country_id does not match
	 * an existing country.
	 *
	 * @return void
	 */
	public function testAttachCountryNoMatchingCountry() {
		$address = ['Address' => ['entry_country_id' => 'non_existent']];

		$this->setExpectedException(
			'CakeException',
			'Asked to attachCountry, but country id provided does not exist.'
		);

		$this->AddressBook->attachCountry($address);
	}

	/**
	 * Test that attachCountry throws an execption if country is passed in, but does
	 * not match the Country record already attached to the address.
	 *
	 * @return void
	 */
	public function testAttachCountryMismatchOfData() {
		$address = [
			'Address' => ['entry_contry_id' => '223'],
			'Country' => ['countries_name' => 'WONT-MATCH'],
		];

		$this->setExpectedException(
			'CakeException',
			'Asked to attachCountry, but did not provide Address.entry_country_id.'
		);

		$this->AddressBook->attachCountry($address);
	}

	/**
	 * Confirm that when a Country array is passed along with an address but the
	 * named country does not match Address.entry_country_id the expected
	 * exception is thrown.
	 *
	 * @return void
	 */
	public function testAttachCountryCountryNameMisMatch() {
		$address = [
			'Address' => ['entry_country_id' => 223],
			'Country' => ['countries_name' => 'Foo Bar'],
		];
		$this->setExpectedException(
			'CakeException',
			'Asked to attachCountry, but country exists and countries_name does not match.'
		);
		$result = $this->AddressBook->attachCountry($address);
		// $this->assertSame('United States', $result['Country']['countries_name']);
		// $this->assertSame('2', $result['Country']['address_format_id']);
	}

	/**
	 * Confirm that a new address is created and that it is set to the customer's default
	 * address.
	 *
	 * @return void
	 */
	public function testSaveAndMakeDefaultSuccess() {
		$data = array(
			'Customer' => array(
				'entry_basename' => 'some base',
			),
			'Address' => array(
				'entry_firstname' => 'Custom',
				'entry_lastname' => 'Name',
				'entry_street_address' => 'PO Box 4',
				'entry_suburb' => '',
				'entry_city' => 'Somewhere',
				'entry_zone_id' => '59',
				'entry_postcode' => '28712',
				'entry_country_id' => '223',
				'customers_id' => '1'
			)
		);
		$Customer = ClassRegistry::init('Customer');
		$customerBefore = $Customer->findByCustomersId($data['Address']['customers_id']);
		$this->assertSame('1', $customerBefore['Customer']['customers_default_address_id']);

		$result = $this->AddressBook->saveAndMakeDefault($data);
		$this->assertSame(
			$this->AddressBook->getInsertId(),
			$result,
			'Returned $result should be the last insert id'
		);
		$customerAfter = $Customer->findByCustomersId($data['Address']['customers_id']);
		$this->assertSame(
			$this->AddressBook->getInsertId(),
			$customerAfter['Customer']['customers_default_address_id'],
			'New default address should be the newly created address id'
		);

		$newAddress = $this->AddressBook->findByAddressBookId($this->AddressBook->getInsertId());
		$this->assertSame($data['Address']['entry_firstname'], $newAddress['Address']['entry_firstname']);
		$this->assertSame($data['Address']['entry_lastname'], $newAddress['Address']['entry_lastname']);
		$this->assertSame($data['Address']['entry_street_address'], $newAddress['Address']['entry_street_address']);
		$this->assertSame($data['Address']['entry_postcode'], $newAddress['Address']['entry_postcode']);
		$this->assertSame($data['Customer']['entry_basename'], $newAddress['Address']['entry_basename']);
	}

	/**
	 * Confirm that saveAndMakeDefault() returns false if the save fails.
	 *
	 * @return void
	 */
	public function testSaveAndMakeDefaultFails() {
		$this->AddressBook = $this->getMockForModel('Address', array('save'));
		$this->AddressBook->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$result = $this->AddressBook->saveAndMakeDefault(array());
		$this->assertFalse($result);
	}

	/**
	 * Confirm that saveAndMakeDefault() returns false if the save suceeds but
	 * the customer record can't be updated.
	 *
	 * @return void
	 */
	public function testSaveAndMakeDefaultSaveFieldFails() {
		$data = [
			'Address' => [
				'customers_id' => 12345,
			],
		];
		$this->AddressBook = $this->getMockForModel('Address', [ 'save']);
		$this->Customer = $this->getMockForModel('Customer', [ 'saveField']);

		$this->AddressBook->expects($this->once())
			->method('save')
			->will($this->returnValue(true));
		$this->Customer->expects($this->once())
			->method('saveField')
			->will($this->returnValue(false));

		$result = $this->AddressBook->saveAndMakeDefault($data);
		$this->assertFalse($result);
	}
}
