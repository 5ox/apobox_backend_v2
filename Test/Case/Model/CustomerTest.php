<?php
App::uses('Customer', 'Model');

/**
 * Customer Test Case
 *
 */
class CustomerTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = [
		'app.country',
		'app.customer',
		'app.address',
		'app.zone',
		'app.order',
		'app.password_request',
		'app.customer_reminder',
		'app.authorized_name',
		'app.search_index',
		'app.customers_info',
	];

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->Customer = $this->getMockForModel('Customer', ['authorizeCreditCard']);
		$this->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Customer);

		parent::tearDown();
	}

	/**
	 * test that the emergency address is not also the shipping address.
	 *
	 * @return	void
	 */
	public function testEmergencyAddressIsNotShippingAddress() {
		$data = ['Customer' => [
			'customers_id' => 1,
			'customers_shipping_address_id' => 1,
			'customers_emergency_address_id' => 1,
		]];
		$result = $this->Customer->save($data);

		$this->assertFalse($result);
	}

	/**
	 * test that the emergency address is not also the shipping address if
	 * set at differt times.
	 *
	 * @return	void
	 */
	public function testEmergencyAddressIsNotShippingAddressOnlyEmergencySaved() {
		$data = ['Customer' => [
			'customers_id' => 1,
			'customers_shipping_address_id' => 4,
		]];
		$result = $this->Customer->save($data);

		$data = ['Customer' => [
			'customers_id' => 1,
			'customers_emergency_address_id' => 4,
		]];
		$result = $this->Customer->save($data);
		$record = $this->Customer->read(null, $data['Customer']['customers_id']);

		$this->assertFalse($result);
		$this->assertNotEqual(
			$data['Customer']['customers_emergency_address_id'],
			$record['Customer']['customers_emergency_address_id']
		);
	}

	/**
	 * test that the shipping address is not also the emergency address if
	 * set at differt times.
	 *
	 * @return	void
	 */
	public function testShippingAddressIsNotEmergencyAddressOnlyShippingSaved() {
		$data = ['Customer' => [
			'customers_id' => 1,
			'customers_emergency_address_id' => 4,
		]];
		$this->Customer->save($data);

		$data = ['Customer' => [
			'customers_id' => 1,
			'customers_shipping_address_id' => 4,
		]];
		$result = $this->Customer->save($data);
		$record = $this->Customer->read(null, $data['Customer']['customers_id']);

		$this->assertFalse($result);
		$this->assertNotEqual(
			$data['Customer']['customers_shipping_address_id'],
			$record['Customer']['customers_shipping_address_id']
		);

	}

	/**
	 * test magic find method
	 *
	 * @return	void
	 */
	public function testFindAllIncompleteBillings() {
		$result = $this->Customer->find('AllIncompleteBillings');

		$this->assertEqual(2, $result[0]['Customer']['customers_id']);
	}

	/**
	 * test magic find method
	 *
	 * @return	void
	 */
	public function testFindFirstIncompleteBilling() {
		$result = $this->Customer->find('FirstIncompleteBilling');

		$this->assertEqual(2, $result['Customer']['customers_id']);
	}

	/**
	 * Test that `active` magic find method will return proper results.
	 *
	 * @return	void
	 */
	public function testFindActive() {
		$result = $this->Customer->find('active');

		$this->assertCount(6, $result);
	}

	/**
	 * tests accetping existing adddress id with valid
	 * ShippingAddress.
	 *
	 * @return	void
	 */
	public function testIsShippingAddressValid() {
		$check = ['customers_shipping_address' => 4];
		$id = current($check);

		$result = $this->Customer->isShippingAddress($check);

		$this->assertTrue($result);
	}

	/**
	 * tests accetping existing adddress id with INvalid
	 * ShippingAddress.
	 *
	 * @return	void
	 */
	public function testIsShippingAddressIsNotShippingAddress() {
		$check = ['customers_default_shipping_address' => 2];
		$id = current($check);

		$result = $this->Customer->isShippingAddress($check);

		$this->assertFalse($result);
	}

	/**
	 * tests that the method handles non-existant addressesa.
	 *
	 * @return	void
	 */
	public function testIsShippingAddressNoAddressWithId() {
		$check = ['customers_default_shipping_address' => 999];
		$id = current($check);

		$result = $this->Customer->isShippingAddress($check);

		$this->assertFalse($result);
	}

	/**
	 * test that we can identify when an address is in use
	 *
	 * @return	void
	 */
	public function testAddressIsInUse() {
		$addressId = 1;
		$customerId = 1;

		$this->Customer->id = $customerId;

		$result = $this->Customer->addressIsInUse($addressId);

		$this->assertTrue($result);
	}

	/**
	 * test that we can identify when an address is NOT in use
	 *
	 * @return	void
	 */
	public function testAddressIsInUseAddressNotInUse() {
		$addressId = 2;
		$customerId = 1;

		$this->Customer->id = $customerId;

		$result = $this->Customer->addressIsInUse($addressId);

		$this->assertFalse($result);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testBeforeSavePasswordsAreHashed() {
		$customerId = 1;
		$password = '35fM}2~0BkLg';

		$this->Customer->id = $customerId;
		$this->Customer->recursive = -1;

		$this->Customer->saveField('customers_password', $password, true);
		$result = $this->Customer->findByCustomersId($customerId);
		$hashedPassword = $result['Customer']['customers_password'];
		$this->assertNotEqual($hashedPassword, $password);
		$this->assertGreaterThan(12, strlen($hashedPassword));
	}

	public function testBeforeSaveCardNumberHashed() {
		$customerId = 1;
		$cardNumber = '5118102631949403';
		$expected   = 'XXXXXXXXXXXX9403';

		$this->Customer->id = $customerId;
		$this->Customer->recursive = -1;

		$this->Customer->save(
			['Customer' => [
				'cc_number' => $cardNumber,
			]],
			true,
			['cc_number', 'cc_number_encrypted']
		);
		$result = $this->Customer->read();

		$this->assertEqual($result['Customer']['cc_number'], $expected);
	}

	/**
	 * Ensure credit card is encrypted and can decrypt back to original.
	 */
	public function testBeforeSaveCardNumberEncrypted() {
		$customerId = 1;
		$cardNumber = '5118102631949403';

		$this->Customer->id = $customerId;
		$this->Customer->recursive = -1;

		$this->Customer->save(
			['Customer' => [
				'cc_number' => $cardNumber,
			]],
			true,
			['cc_number', 'cc_number_encrypted']
		);
		$record = $this->Customer->read();
		$result = $this->Customer->decryptCC(
			$record['Customer']['cc_number_encrypted'],
			Configure::read('Security.creditCardKey')
		);

		$this->assertEqual($result, $cardNumber);
	}

	/**
	 * test of maskCardNumber
	 */
	public function testMaskCardNumber() {
		$cardNumber = '5118102631949403';
		$expected   = 'XXXXXXXXXXXX9403';

		$result = $this->Customer->maskCardNumber($cardNumber);

		$this->assertEqual($result, $expected);
	}

	/**
	 * test of maskCardNumber with interger input
	 */
	public function testMaskCardNumberInt() {
		$cardNumber = 5118102631949403;
		$expected  = 'XXXXXXXXXXXX9403';

		$result = $this->Customer->maskCardNumber($cardNumber);

		$this->assertEqual($result, $expected);
	}

	/**
	 * Encryption changes each time so we make sure we can encrypt and decrypt
	 * and get the same result.
	 */
	public function testEncryptCCAndDecryptCC() {
		$expected = $cardNumber = '5118102631949403';
		$key = 'testkey';
		$encrypted = $this->Customer->encryptCC($cardNumber, $key);
		$result = $this->Customer->decryptCC($encrypted, $key);

		$this->assertEqual($result, $expected);
	}

	/**
	 *
	 */
	public function testEncryptCCFailsOnEmptyString() {
		$cardNumber = '';
		$key = 'testkey';
		$result = $this->Customer->encryptCC($cardNumber, $key);

		$this->assertFalse($result);
	}

	/**
	 *
	 */
	public function testDecryptCC() {
		$encryptedCardNumber = 'zzkrvXfDrK+v8lRqpPe/NCBqnHx1RGzpbCZ45TK6nBHmYwjCBRt90UmPGwveIiAp';
		$key = 'testkey';
		$expected = '5118102631949403';
		$result = $this->Customer->decryptCC($encryptedCardNumber, $key);

		$this->assertEqual($result, $expected);
	}

	/**
	 *
	 */
	public function testDecryptCCEmptyString() {
		$encryptedCardNumber = '';
		$key = 'testkey';
		$result = $this->Customer->decryptCC($encryptedCardNumber, $key);

		$this->assertFalse($result);
	}

	/**
	 *
	 */
	public function testDecryptCCTooShortForIV() {
		$encryptedCardNumber  = 'tooShort';
		$key = 'testkey';
		$result = $this->Customer->decryptCC($encryptedCardNumber, $key);

		$this->assertFalse($result);
	}

	/**
	 *
	 */
	public function testSaveUpdatedCreditCard() {
		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$customerBefore = $this->Customer->findByCustomersId($customersId);
		$this->Customer->save(
			$data,
			true,
			array_merge(array_keys($data['Customer']), ['cc_number_encrypted'])
		);
		$customerAfter = $this->Customer->findByCustomersId($customersId);

		$this->assertTrue($customerAfter['Customer']['cc_number_encrypted'] != $customerBefore['Customer']['cc_number_encrypted']);
	}

	/**
	 * If the fields param is pass with cc_number but without cc_number_encrypted
	 * the save must fail.
	 */
	public function testSaveUpdatedCreditCardWithBadFieldList() {
		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];
		$fields = [
			'customers_id',
			'cc_firstname',
			'cc_lastname',
			'cc_number',
			'cc_expires_month',
			'cc_expires_year',
			'cc_cvv'
		];

		$result = $this->Customer->save($data, true, $fields);
		$this->assertFalse($result);
	}

	/**
	 * Ensure we can save with a field argument that allows writing over
	 * the cc_number and cc_number_encrypted fields.
	 */
	public function testSaveUpdatedCreditCardWithGoodFieldList() {
		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$customerBefore = $this->Customer->findByCustomersId($customersId);
		$this->Customer->save(
			$data,
			true,
			array_merge(array_keys($data['Customer']), ['cc_number_encrypted'])
		);
		$customerAfter = $this->Customer->findByCustomersId($customersId);

		$this->assertTrue($customerAfter['Customer']['cc_number_encrypted'] != $customerBefore['Customer']['cc_number_encrypted']);
	}

	/**
	 * Ensure we can save with a field argument that allows writing over
	 * the cc_number and cc_number_encrypted fields.
	 */
	public function testBeforeSaveFailsWhenCardAuthFails() {
		$this->Customer = $this->getMockForModel('Customer', ['authorizeCreditCard']);
		$this->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(false));
		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$this->assertFalse($this->Customer->save($data));
	}

	/**
	 *
	 */
	public function testSaveDoesNotStoreCVV() {
		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$result = $this->Customer->save(
			$data,
			true,
			array_merge(array_keys($data['Customer']), ['cc_number_encrypted'])
		);

		$this->assertTrue(!empty($result));
		$this->assertArrayHasKey('Customer', $result);
		$this->assertTrue(empty($result['Customer']['cc_cvv']), 'Card CVV must not be saved.');
	}

	public function testAuthorizeCreditCardSucceeds() {
		$Payment = $this->getMockBuilder('Payment')
			->disableOriginalConstructor()
			->getMock();
		$this->Customer = $this->getMockForModel('Customer', ['getPaymentLib']);
		$this->Customer->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($Payment));
		$Payment->expects($this->once())
			->method('authorizeAndStoreCard')
			->will($this->returnValue(true));

		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$this->assertArrayHasKey('Customer', $this->Customer->save(
			$data,
			true,
			array_merge(array_keys($data['Customer']), ['cc_number_encrypted'])
		));
	}

	public function testAuthorizeCreditCardFails() {
		$Payment = $this->getMockBuilder('Payment')
			->disableOriginalConstructor()
			->getMock();
		$this->Customer = $this->getMockForModel('Customer', ['getPaymentLib']);
		$this->Customer->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($Payment));
		$Payment->expects($this->once())
			->method('authorizeAndStoreCard')
			->will($this->returnValue(false));

		$customersId = 1;
		$data['Customer'] = [
			'customers_id' => $customersId,
			'cc_firstname' => 'First',
			'cc_lastname' => 'Last',
			'cc_number' => '5118102631949403',
			'cc_expires_month' => 5,
			'cc_expires_year' => (new DateTime("+1 year"))->format('y'),
			'cc_cvv' => '123'
		];

		$this->assertFalse($this->Customer->save(
			$data,
			true,
			array_merge(array_keys($data['Customer']), ['cc_number_encrypted'])
		));
	}

	public function testInitForChargeEncryptedCard() {
		$cardNumber = '5118102631949403';
		$encrypted = $this->Customer->encryptCC($cardNumber, Configure::read('Security.creditCardKey'));
		$card = [
			'cc_number' => 'XXXXXXXXXXXX9403',
			'cc_number_encrypted' => $encrypted,
			'cc_cvv' => '',
		];
		$expected = [
			'cc_number' => $cardNumber,
			'cc_number_encrypted' => $encrypted,
			'cc_cvv' => 'not_used',
		];

		$result = $this->Customer->initForCharge($card);

		$this->assertEquals($expected, $result);
	}

	public function testInitForChargeNotEncrypted() {
		$card = ['does_not_matter' => 'does_not_change'];
		$result = $this->Customer->initForCharge($card);
		$this->assertEquals($card, $result);
	}

	public function testCCExpriration() {
		$month = '5';
		$year = '99';
		$this->Customer->data['Customer']['cc_expires_year'] = $year;

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertTrue($result);
	}

	public function testCCExprirationWithIntegers() {
		$month = 5;
		$year = 99;
		$this->Customer->data['Customer']['cc_expires_year'] = $year;

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertTrue($result);
	}

	public function testCCExprirationInPast() {
		$month = '5';
		$year = '10';
		$this->Customer->data['Customer']['cc_expires_year'] = $year;

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertFalse($result);
	}

	public function testCCExprirationSameMonthAndYearAsExpiration() {
		$now = date_create();
		$month = date_format($now, 'n');
		$year = date_format($now, 'y');
		$this->Customer->data['Customer']['cc_expires_year'] = $year;

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertFalse($result);
	}

	public function testCCExprirationWith4DigitYear() {
		$now = date_create();
		$month = date_format($now, 'n');
		$year = date_format($now, 'Y');
		$this->Customer->data['Customer']['cc_expires_year'] = $year;

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertFalse($result);
	}

	public function testCCExprirationWithoutYear() {
		$now = date_create();
		$month = date_format($now, 'n');

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertFalse($result);
	}

	public function testCCExpriration1MonthInFuture() {
		Configure::delete('CreditCard.invalid_before');
		$now = date_create('2015-05-05');
		$expirationDate = date_create('2016-06-01');
		$month = date_format($expirationDate, 'n');
		$year = date_format( $expirationDate, 'y');
		$this->Customer = $this->getMockForModel('Customer', ['getDatetime']);
		$this->Customer->data['Customer']['cc_expires_year'] = $year;
		$this->Customer->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue($now));

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertTrue($result);
	}

	public function testCCExprirationWith3DayInvalidationPeriod() {
		$invalidBefore = '3D';
		Configure::write('CreditCard.invalid_before', $invalidBefore);
		$expireDate = date_create('first day of next month');
		$month = date_format($expireDate, 'n');
		$year = date_format($expireDate, 'y');
		$currentTime = $expireDate->sub(new DateInterval('P' . $invalidBefore));
		$this->Customer = $this->getMockForModel('Customer', ['getDatetime']);
		$this->Customer->data['Customer']['cc_expires_year'] = $year;
		$this->Customer->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue($currentTime));

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertFalse($result);
	}

	public function testCCExpriration3DaysBeforeExpirationMonthWithoutInvalidationPeriod() {
		$invalidBefore = null;
		Configure::write('CreditCard.invalid_before', $invalidBefore);
		$expireDate = date_create('first day of next month');
		$month = date_format($expireDate, 'n');
		$year = date_format($expireDate, 'y');
		$currentTime = $expireDate->sub(new DateInterval('P3D'));
		$this->Customer = $this->getMockForModel('Customer', ['getDatetime']);
		$this->Customer->data['Customer']['cc_expires_year'] = $year;
		$this->Customer->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue($currentTime));

		$result = $this->Customer->ccExpiration([$month]);

		$this->assertTrue($result);
	}

	public function testListAllFull() {
		$result = $this->Customer->listAll();
		$this->assertEquals('TU4321 Test User', $result[4]);
	}

	public function testListAllQuery() {
		$result = $this->Customer->listAll('U432');
		$this->assertCount(1, $result);
		$this->assertEquals('TU4321 Test User', $result[4]);
	}

	/**
	 * Confirm that notSetAsShippingAddress() sets the model id correctly if
	 * passed id is empty.
	 *
	 * @return	void
	 */
	public function testNotSetAsShippingAddressWithoutId() {
		$id = 1;
		$check = [];
		$this->Customer->data['Customer']['customers_id'] = $id;

		$this->assertNotEquals($id, $this->Customer->id);
		$this->Customer->notSetAsShippingAddress($check);
		$this->assertEquals($id, $this->Customer->id);
	}

	/**
	 * Confirm that notSetAsEmergencyAddress() sets the model id correctly if
	 * passed id is empty.
	 *
	 * @return	void
	 */
	public function testNotSetAsEmergencyAddressWithoutId() {
		$id = 1;
		$check = [];
		$this->Customer->data['Customer']['customers_id'] = $id;

		$this->assertNotEquals($id, $this->Customer->id);
		$this->Customer->notSetAsEmergencyAddress($check);
		$this->assertEquals($id, $this->Customer->id);
	}

	/**
	 * Confirm that notSetAsEmergencyAddress() returns the correct bool based
	 * on supplied $check and model $data.
	 *
	 * @dataProvider provideNotSetAsEmergencyAddress
	 * @return	void
	 */
	public function testNotSetAsEmergencyAddressWithoutAddressId($dataId, $check, $expected) {
		$this->Customer->data = ['Customer' => [
			'customers_id' => 1,
			'customers_emergency_address_id' => $dataId,
		]];
		$result = $this->Customer->notSetAsEmergencyAddress($check);
		$method = $expected ? 'assertTrue' : 'assertFalse';
		$this->$method($result);
	}

	public function provideNotSetAsEmergencyAddress() {
		return [
			[1, [], true],
			[4, ['customers_emergency_address_id' => 4], false],
			[1, ['customers_emergency_address_id' => 4], true],
		];
	}

	/**
	 * Confirm that addressIsCustomer returns false if a customer can't be found by
	 * $id.
	 *
	 * @return	void
	 */
	public function testAddressIsCustomers() {
		$check = [];
		$result = $this->Customer->addressIsCustomers($check);
		$this->assertFalse($result);
	}

	/**
	 * Confirm beforeSave returns false if authorizeCreditCard throws and
	 * exception.
	 *
	 * @return	void
	 */
	public function testBeforeSaveCardException() {
		$this->Customer = $this->getMockForModel('Customer', ['authorizeCreditCard', 'log']);
		$this->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->throwException(new Exception()));
		$this->Customer->expects($this->once())
			->method('log')
			->with(
				$this->stringContains('CustomerModel::beforeSave'),
				$this->identicalTo('customers')
			);

		$this->Customer->data = ['Customer' => [
			'cc_number' => '12345678',
		]];

		$result = $this->Customer->beforeSave();
		$this->assertFalse($result);
	}

	/**
	 * Confirm the magic find method returns an empty array if no results
	 * are found.
	 *
	 * @return	void
	 */
	public function testFindFirstIncompleteBillingsNoResults() {
		for ($i = 1; $i < 20; $i++) {
			$this->Customer->delete($i);
		}
		$result = $this->Customer->find('FirstIncompleteBilling');
		$this->assertEmpty($result);
	}

	/**
	 * Confirm that if an $id is passed to addressIsInUse the method will set
	 * the model->id property to $id.
	 *
	 * @return	void
	 */
	public function testAddressIsInUseWithId() {
		$addressId = 1;
		$customerId = 1;

		$this->assertFalse($this->Customer->id);
		$result = $this->Customer->addressIsInUse($addressId, $customerId);
		$this->assertEquals($customerId, $this->Customer->id);
	}

	/**
	 * Confirm that if an $id is any array the method will use the first id
	 * and still work.
	 *
	 * @return	void
	 */
	public function testAddressIsInUseWithArrayId() {
		$addressId = 1;
		$customerId = [1, 2];

		$result = $this->Customer->addressIsInUse($addressId, $customerId);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that if an $id is null or false the method will return false
	 *
	 * @return	void
	 */
	public function testAddressIsInUseWithNullId() {
		$addressId = 1;

		$customerId = null;
		$result = $this->Customer->addressIsInUse($addressId, $customerId);
		$this->assertFalse($result);

		$customerId = false;
		$result = $this->Customer->addressIsInUse($addressId, $customerId);
		$this->assertFalse($result);
	}

	/**
	 * test that we can identify when an address is NOT in use
	 *
	 * @return	void
	 */
	public function testAddressIsInUseShippingAddressNotInUse() {
		$addressId = 2;
		$customerId = 1;

		$this->Customer->id = $customerId;

		$result = $this->Customer->addressIsInUseShipping($addressId);

		$this->assertFalse($result);
	}

	/**
	 * Confirm that if an $id is passed to addressIsInUse the method will set
	 * the model->id property to $id.
	 *
	 * @return	void
	 */
	public function testAddressIsInUseShippingWithId() {
		$addressId = 1;
		$customerId = 1;

		$this->assertFalse($this->Customer->id);
		$result = $this->Customer->addressIsInUseShipping($addressId, $customerId);
		$this->assertEquals($customerId, $this->Customer->id);
	}

	/**
	 * Confirm that if an $id is any array the method will use the first id
	 * and still work.
	 *
	 * @return	void
	 */
	public function testAddressIsInUseShippingWithArrayId() {
		$addressId = 1;
		$customerId = [1, 2];

		$result = $this->Customer->addressIsInUseShipping($addressId, $customerId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that if an $id is null or false the method will return false
	 *
	 * @return	void
	 */
	public function testAddressIsInUseShippingWithNullId() {
		$addressId = 1;

		$customerId = null;
		$result = $this->Customer->addressIsInUseShipping($addressId, $customerId);
		$this->assertFalse($result);

		$customerId = false;
		$result = $this->Customer->addressIsInUseShipping($addressId, $customerId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm beforeSave sets `invoicing_authorized` to 0 and returns true
	 * for new records (no id passed).
	 *
	 * @return	void
	 */
	public function testBeforeSaveSetsIinvoicingAuthorized() {
		$this->Customer->data = ['Customer' => []];
		$result = $this->Customer->beforeSave();

		$this->assertTrue($result);
		$this->assertNotEmpty($this->Customer->data['Customer']);
		$this->assertEquals(0, $this->Customer->data['Customer']['invoicing_authorized']);
	}

	/**
	 * Confirm beforeSave doesn't change `invoicing_authorized` and returns true
	 * for new records (no id passed) if `invoicing_authorized` is already set.
	 *
	 * @return	void
	 */
	public function testBeforeSaveNotSetsIinvoicingAuthorized() {
		$this->Customer->data = ['Customer' => [
			'invoicing_authorized' => 1
		]];

		$result = $this->Customer->beforeSave();
		$this->assertTrue($result);
		$this->assertNotEmpty($this->Customer->data['Customer']);
		$this->assertEquals(1, $this->Customer->data['Customer']['invoicing_authorized']);
	}

	/**
	 * Confirm beforeSave doesn't change `invoicing_authorized` and returns true
	 * for update/edits.
	 *
	 * @return	void
	 */
	public function testBeforeSaveNotSetsIinvoicingAuthorizedWhenEditing() {
		$this->Customer->data = ['Customer' => [
			'customers_id' => 1
		]];

		$result = $this->Customer->beforeSave();
		$this->assertTrue($result);
		$this->assertArrayNotHasKey('invoicing_authorized', $this->Customer->data['Customer']);
	}

	/**
	 * Test that a `billing_id` is generated for new customers.
	 */
	public function testAfterSaveBillingId() {
		$this->Customer->create();
		$this->Customer->save(
			[
				'customers_firstname' => 'Billy',
				'customers_lastname' => 'Bob',
			],
			['fieldList' => ['customers_firstname', 'customers_lastname']]
		);

		$newBillingId = $this->Customer->field('billing_id');
		$this->assertRegExp('/BB\d{4}/', $newBillingId);
	}

	/**
	 * Test that billingId() will throw an exception if an id is not set on
	 * the model.
	 *
	 * @expectedException BadRequestException
	 */
	public function testBillingIdNoModelRecord() {
		$this->Customer->billingId();
	}

	/**
	 * Test that a billing id is retrieved from an existing customer.
	 */
	public function testBillingIdExisting() {
		$this->Customer->id = 2;
		$this->assertSame('IB1234', $this->Customer->billingId());
	}

	/**
	 * Test that a billing_id will be generated, saved, and returned for a
	 * customer if it does not already exist.
	 */
	public function testBillingIdNew() {
		$this->mockSearchIndex();
		$this->Customer->create();
		$this->Customer->save(
			[
				'customers_firstname' => 'Billy',
				'customers_lastname' => 'Bob',
			],
			[
				'fieldList' => ['customers_firstname', 'customers_lastname'],
				'callbacks' => false,
			]
		);

		$this->assertEmpty($this->Customer->field('billing_id'));
		$newBillingId = $this->Customer->billingId();
		$this->assertRegExp('/BB\d{4}/', $newBillingId);
		$this->assertSame($newBillingId, $this->Customer->field('billing_id'));
	}

	/**
	 * Test that newBillingId() will throw an exception if an id is not set on
	 * the model.
	 *
	 * @expectedException BadRequestException
	 */
	public function testNewBillingIdNoModelRecord() {
		$this->Customer->newBillingId();
	}

	/**
	 * Test that newBillingId() will save and return a `billing_id` based on
	 * set data.
	 */
	public function testNewBillingIdWithSetData() {
		$this->mockSearchIndex();
		$this->Customer->id = 1;
		$this->Customer->set([
			'customers_firstname' => 'Will',
			'customers_lastname' => 'Rogers',
		]);
		$result = $this->Customer->newBillingId();
		$this->assertRegExp('/WR\d{4}/', $result);
		$this->assertSame($result, $this->Customer->field('billing_id'));
	}

	/**
	 * Test that newBillingId() will return a `billing_id` based on saved data.
	 */
	public function testNewBillingIdWithSavedData() {
		$this->mockSearchIndex();
		$this->Customer->id = 1;
		$result = $this->Customer->newBillingId();
		$this->assertRegExp('/LL\d{4}/', $result);
	}

	/**
	 * Test that newBillingId() will keep trying if discovered `billing_id`s
	 * already exist in database. This test will take the 4th value.
	 */
	public function testNewBillingIdWillRepeat() {
		$this->mockSearchIndex();
		$this->Customer = $this->getMockForModel('Customer', ['findByBillingId']);
		$this->Customer->expects($this->any())->method('findByBillingId')
			->will($this->onConsecutiveCalls(true, true, true, false));
		$this->Customer->id = 1;
		$this->Customer->set([
			'customers_firstname' => 'Zee',
			'customers_lastname' => 'Zigler',
		]);
		$result = $this->Customer->newBillingId();
		$this->assertRegExp('/ZZ\d{4}/', $result);
	}

	/**
	 * Confirm that findExpiringCreditCards() can find an expiring credit card
	 * on the specified day.
	 *
	 * @return void
	 */
	public function testFindExpiringCreditCards() {
		$customerId = 5;

		$data = [
			'Customer' => [
				'customers_id' => $customerId,
				'customers_email_address' => 'somerandomemail',
				'customers_firstname' => 'firstname',
				'customers_lastname' => 'lastname',
				'cc_expires_month' => date('m', strtotime('next month')),
				'cc_expires_year' => date('y', strtotime('next month')),
			],
		];

		$saved = $this->Customer->save($data, false);
		$this->assertTrue((bool)$saved, 'Confirm the record was saved');

		$result = $this->Customer->findExpiringCreditCards();
		$this->assertArrayHasKey($customerId, $result);
		$this->assertSame(
			$data['Customer']['customers_email_address'],
			$result[$customerId]['customers_email_address']
		);
		$this->assertSame(
			$data['Customer']['customers_firstname'] . ' ' . $data['Customer']['customers_lastname'],
			$result[$customerId]['customers_fullname']
		);
	}

	/**
	 * Confirm that findAllPartialSignups returns expected array keys.
	 *
	 * @return void
	 */
	public function testFindAllPartialSignups() {
		$result = $this->Customer->findAllPartialSignups();
		$this->assertArrayHasKey('Customer', $result[0]);
		$this->assertArrayHasKey('CustomerReminder', $result[0]);
		$this->assertArrayHasKey('customers_fullname', $result[0]['Customer']);
	}

	/**
	 * Confirm that findAllPartialSignups returns an empty result if
	 * the count is set to `0`.
	 *
	 * @return void
	 */
	public function testFindAllPartialSignupsSetCount() {
		Configure::write('Customers.signupReminders', 0);
		$result = $this->Customer->findAllPartialSignups();
		$this->assertEmpty($result);
	}

	/**
	 * Confirm that findAllExpiredPartialSignups returns expected array keys.
	 *
	 * @return void
	 */
	public function testFindAllExpiredPartialSignups() {
		$Customer = $this->getMockForModel('Customer', ['getDateTime']);
		$Customer->expects($this->once())
			->method('getDateTime')
			->will($this->returnValue(new DateTime()));
		$result = $Customer->findAllExpiredPartialSignups();
		$this->assertArrayHasKey('Customer', $result[0]);
		$this->assertArrayHasKey('customers_id', $result[0]['Customer']);
	}

	/**
	 * Confirm that findAllExpiredPartialSignups returns an empty result if
	 * the count is set to a very high number of weeks.
	 *
	 * @return void
	 */
	public function testFindAllExpiredPartialSignupsSetCount() {
		Configure::write('Customers.purgePartials', '99999');
		$Customer = $this->getMockForModel('Customer', ['getDateTime']);
		$Customer->expects($this->once())
			->method('getDateTime')
			->will($this->returnValue(new DateTime()));
		$result = $Customer->findAllExpiredPartialSignups();
		$result = $this->Customer->findAllExpiredPartialSignups();
		$this->assertEmpty($result);
	}

	/**
	 * Confirm that an expired customer can be deleted when the customer's
	 * id is passed in an array to purgeExpiredPartials().
	 *
	 * @return void
	 */
	public function testPurgeExpiredPartials() {
		$Customer = $this->getMockForModel('Customer', ['getDateTime']);
		$Customer->expects($this->exactly(2))
			->method('getDateTime')
			->will($this->returnValue(new DateTime()));

		$before = $Customer->findAllExpiredPartialSignups();
		$this->assertNotEmpty($before);
		$this->assertArrayHasKey('Customer', $before[0]);

		$result = $this->Customer->purgeExpiredPartials($before);
		$this->assertTrue($result);

		$after = $Customer->findAllExpiredPartialSignups();
		$this->assertEmpty($after);
	}

	/**
	 * Confirm that findExpiredCreditCards() can find an expired credit card.
	 *
	 * @return void
	 */
	public function testFindExpiredCreditCardsWithResult() {
		$customerId = 1;
		$customerId2 = 4;

		$maxMonths = Configure::read('Customers.expiredCardReminders.maxMonths');

		$date5m = date_create('-' . ($maxMonths - 1) . ' months');
		$date7m = date_create('-' . ($maxMonths + 1) . ' months');
		$data = [
			'Customer' => [
				'customers_id' => $customerId,
				'customers_email_address' => 'somerandomemail',
				'customers_firstname' => 'firstname',
				'customers_lastname' => 'lastname',
				'cc_expires_month' => $date5m->format('m'),
				'cc_expires_year' => $date5m->format('y'),
			],
		];
		$data2 = [
			'Customer' => [
				'customers_id' => $customerId2,
				'customers_email_address' => 'anotheremailaddress',
				'customers_firstname' => 'newfirstname',
				'customers_lastname' => 'newlastname',
				'cc_expires_month' => $date7m->format('m'),
				'cc_expires_year' => $date7m->format('y'),
			],
		];

		$saved = $this->Customer->save($data, false);
		$saved2 = $this->Customer->save($data2, false);
		$this->assertTrue((bool)$saved, 'Confirm the record was saved');
		$this->assertTrue((bool)$saved2, 'Confirm the record was saved');

		$result = $this->Customer->findExpiredCreditCards(1);

		$this->assertCount(1, $result, 'There should only be one card exprired within ' . $maxMonths . ' months.');
		$this->assertSame($customerId, (int)$result[0]['Customer']['customers_id']);
		$this->assertSame(
			$data['Customer']['customers_email_address'],
			$result[0]['Customer']['customers_email_address']
		);
		$this->assertSame(
			$data['Customer']['customers_firstname'] . ' ' . $data['Customer']['customers_lastname'],
			$result[0]['Customer']['customers_fullname']
		);
	}

	/**
	 * Confirm that findExpiredCreditCards() returns an empty array if no expired
	 * cards are found.
	 *
	 * @return void
	 */
	public function testFindExpiredCreditCardsWithNoResults() {
		$customerId = 1;

		$data = [
			'Customer' => [
				'customers_id' => $customerId,
				'customers_email_address' => 'somerandomemail',
				'customers_firstname' => 'firstname',
				'customers_lastname' => 'lastname',
				'cc_expires_month' => date('m'),
				'cc_expires_year' => date('y', strtotime('next year')),
			],
		];

		$saved = $this->Customer->save($data, false);
		$this->assertTrue((bool)$saved, 'Confirm the record was saved');

		$result = $this->Customer->findExpiredCreditCards();
		$this->assertEmpty($result);
	}

	/**
	 * Confirm that if a `CustomerReminder` record exists for a customer
	 * findExpiredCreditCards() will not include the customer in it's results.
	 *
	 * @return void
	 */
	public function testFindExpiredCreditCardsWithExisting() {
		$customerId = 1;

		$maxMonths = Configure::read('Customers.expiredCardReminders.maxMonths');
		$date5m = date_create('-' . ($maxMonths - 1) . ' months');

		$data = [
			'Customer' => [
				'customers_id' => $customerId,
				'customers_email_address' => 'somerandomemail',
				'customers_firstname' => 'firstname',
				'customers_lastname' => 'lastname',
				'cc_expires_month' => $date5m->format('m'),
				'cc_expires_year' => $date5m->format('y'),
			],
		];
		$reminderData = [
			'CustomerReminder' => [
					'customers_id' => $customerId,
					'orders_id' => '0',
					'reminder_type' => 'expired_card',
					'reminder_count' => '1',
			]
		];

		$saved = $this->Customer->save($data, false);
		$this->assertTrue((bool)$saved, 'A customer record should have been saved');
		$result = $this->Customer->findExpiredCreditCards();
		$this->assertNotEmpty($result, 'Expired card records should exist');
		$this->assertCount(1,$result, 'There should be exactly 1 expired card record');
		$this->assertSame($customerId, (int)$result[0]['Customer']['customers_id']);

		$reminderSaved = $this->Customer->CustomerReminder->save($reminderData, false);
		$this->assertTrue((bool)$reminderSaved, 'A customer remoinder record should have been saved');

		$result = $this->Customer->findExpiredCreditCards();
		$this->assertEmpty($result, 'There should be no expired card records to notify');

		// Now increase the notification count and test again
		Configure::write('Customers.expiredCardReminders.numberToSend', 2);

		$result = $this->Customer->findExpiredCreditCards();
		$this->assertNotEmpty($result, 'Expired card records should exist');
		$this->assertCount(1,$result, 'There should be exactly 1 expired card record');

		//$this->Customer->CustomerReminder->id = $this->Customer->CustomerReminder->getLastInsertId();
		$reminderSaved = $this->Customer->CustomerReminder->saveField('reminder_count', 2);
		$this->assertTrue((bool)$reminderSaved, 'A customer remoinder record should have been saved');

		$result = $this->Customer->findExpiredCreditCards();
		$this->assertEmpty($result, 'There should be no expired card records to notify');
	}

	/**
	 * Confirm that closeAccount() set's `is_active` to false and empties the
	 * credit card related fields when supplied with a valid customer id.
	 *
	 * @return void
	 */
	public function testCloseAccountValidCustomer() {
		$customerId = 1;
		$fields = [
			'cc_firstname',
			'cc_lastname',
			'cc_number',
			'cc_number_encrypted',
			'cc_expires_month',
			'cc_expires_year',
			'card_token',
		];

		$beforeCustomer = $this->Customer->findByCustomersId($customerId);
		$this->assertTrue($beforeCustomer['Customer']['is_active']);
		foreach ($fields as $field) {
			$this->assertNotEmpty($beforeCustomer['Customer'][$field], $field . ' should not be empty');
		}

		$result = $this->Customer->closeAccount($customerId);
		$this->assertArrayHasKey('Customer', $result);

		$afterCustomer = $this->Customer->findByCustomersId($customerId);

		$this->assertFalse($afterCustomer['Customer']['is_active']);
		foreach ($fields as $field) {
			$this->assertEmpty($afterCustomer['Customer'][$field], $field . ' should be empty');
		}
	}

	/**
	 * Confirm that closeAccount() returns false when supplied with an invalid
	 * customer id.
	 *
	 * @return void
	 */
	public function testCloseAccountInvalidCustomer() {
		$result = $this->Customer->closeAccount(9999);
		$this->assertFalse($result);
	}

	/**
	 * Test that only active customers are returned by findForQuickOrder().
	 *
	 * @return	void
	 * @dataProvider providesFindForQuickOrder
	 */
	public function testFindForQuickOrder($billingId, $found) {
		$assert = ($found ? 'True' : 'False');
		$this->{'assert' . $assert}((bool)$this->Customer->findForQuickOrder($billingId));
	}

	public function providesFindForQuickOrder() {
		return [
			['XU934', true],
			['ZZ4679', true],
			['ZZ1234', false],
		];
	}

	/**
	 * Confirm that partial account status is correctly determined by isPartialSignup().
	 *
	 * @return	void
	 * @dataProvider provideIsPartialSignup
	 */
	public function testIsPartialSignup($customerId, $expected) {
		$assert = ($expected ? 'True' : 'False');
		$this->{'assert' . $assert}((bool)$this->Customer->isPartialSignup($customerId));
	}

	public function provideIsPartialSignup() {
		return [
			[1, false],
			[7, true],
			[2, false],
		];
	}

	/**
	 * mockSearchIndex
	 *
	 * @return object A mocked instance of SearchIndex
	 */
	protected function mockSearchIndex() {
		$SearchIndex = $this->getMockForModel('SearchIndex', [
			'findByAssociationKey',
			'saveField',
		]);
		$SearchIndex->expects($this->once())
			->method('findByAssociationKey')
			->will($this->returnValue(['SearchIndex' => ['id' => 1, 'data' => 'foo']]));
		$SearchIndex->expects($this->once())
			->method('saveField')
			->will($this->returnValue(true));
		return $SearchIndex;
	}

	/**
	 * Confirm that indexData() returns the expected SearchIndex.data formatted
	 * results based on customer ID.
	 *
	 * @return void
	 * @dataProvider provideIndexData
	 */
	public function testIndexData($customerId, $expected, $msg = '') {
		$result = $this->Customer->indexData($customerId);
		$this->assertSame($expected, $result);
	}

	public function provideIndexData() {
		return [
			[1, 'Lorem . Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet. someone@example.com. John. Doe. John. Smith'],
			[2, 'IB1234. Incomplete. Billing. someon.else@example.com. George. Washington. Lorem. SetDefaults'],
			[3, '. Empty Billing ID. Billing. someon.else@example.com'],
		];
	}

	/**
	 * Confirm that the expected array results are returned in the expected
	 * format.
	 *
	 * @return void
	 */
	public function testFindMissingCustomersInfo() {
		$result = $this->Customer->findMissingCustomersInfo();
		$this->assertSame(1, count($result));
		$this->assertSame(6, key($result));
		$this->assertArrayHasKey(6, $result);
		$this->assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result[6]);
	}

	/**
	 * Confirm that the `isUnique` validation rule can be removed if the correct
	 * conditions are met.
	 *
	 * @return void
	 */
	public function testBeforeValidateRemovesValidationRule() {
		$inactiveUser = 'test.user1234@example.com';
		$data = ['Customer' => ['customers_email_address' => $inactiveUser]];
		$this->Customer->data = $data;

		$before = $this->Customer->validator()->getField('customers_email_address')->getRule('unique');
		$this->assertInternalType('object', $before);
		$this->assertSame('isUnique', $before->rule[0]);

		$result = $this->Customer->beforeValidate();

		$after = $this->Customer->validator()->getField('customers_email_address')->getRule('unique');
		$this->assertNull($after);
	}

	/**
	 * Confirm that findCustomerTotalsReport returns expected data with various
	 * query combinations.
	 *
	 * @dataProvider provideFindCustomerTotalsReport
	 * @return void
	 */
	public function testFindCustomerTotalsReport($data, $expected, $testFor) {
		$result = $this->Customer->findCustomerTotalsReport($data);
		$this->assertEquals($expected['count'], count($result), 'Count should match when testing ' . $testFor . '.');
		if ($result) {
			$this->assertArrayHasKey(0, $result);
			$this->assertArrayHasKey('name', $result[0]);
			$this->assertArrayHasKey('y', $result[0]);
			$this->assertEquals($expected['first_total'], $result[0]['y'], 'Total should match when testing ' . $testFor . '.');
		}
	}

	/**
	 * provideFindCustomerTotalsReport
	 *
	 * @return array
	 */
	public function provideFindCustomerTotalsReport() {
		return [
			[
				'data' => [
				],
				'result' => [
					'count' => 2,
					'first_total' => 5,
				],
				'defaults',
			],
			[
				'data' => [
					'field' => 'ShippingAddress.entry_country_id',
				],
				'result' => [
					'count' => 1,
					'first_total' => 6,
				],
				'shipping country field',
			],
			[
				'data' => [
					'field' => 'ShippingAddress.entry_city',
				],
				'result' => [
					'count' => 2,
					'first_total' => 5,
				],
				'shipping city field',
			],
			[
				'data' => [
					'field' => 'DefaultAddress.entry_postcode',
				],
				'result' => [
					'count' => 1,
					'first_total' => 6,
				],
				'billing postcode field',
			],
			[
				'data' => [
					'field' => 'DefaultAddress.entry_country_id',
				],
				'result' => [
					'count' => 1,
					'first_total' => 6,
				],
				'billing country field',
			],
			[
				'data' => [
					'field' => 'DefaultAddress.entry_city',
				],
				'result' => [
					'count' => 1,
					'first_total' => 6,
				],
				'billing city field',
			],
			[
				'data' => [
					'field' => 'Customer.insurance_amount',
				],
				'result' => [
					'count' => 4,
					'first_total' => 4,
				],
				'insurance amount field',
			],
			[
				'data' => [
					'field' => 'bogus',
				],
				'result' => [
					'count' => 2,
					'first_total' => 5,
				],
				'invalid field name',
			],
			[
				'data' => [
					'limit' => 1,
				],
				'result' => [
					'count' => 1,
					'first_total' => 5,
				],
				'limit',
			],
			[
				'data' => [
					// 'field' => 'ShippingAddress.entry_postal_code',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00',
					// 'limit' => 10,
				],
				'result' => [
					'count' => 2,
					'first_total' => 4,
				],
				'dates',
			],
			[
				'data' => [
					'field' => 'DefaultAddress.entry_postal_code',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00',
					'limit' => 1,
				],
				'result' => [
					'count' => 1,
					'first_total' => 4,
				],
				'all options',
			],
		];
	}

	/**
	 * Confirm that findCustomerTotalsReport when given a country field, will
	 * return the two digit country code.
	 *
	 * @dataProvider provideFindCustomerTotalsReportCountry
	 * @return void
	 */
	public function testFindCustomerTotalsReportCountry($data, $expected, $testFor) {
		$result = $this->Customer->findCustomerTotalsReport($data);
		$this->assertSame($expected, $result[0]['name'], 'Two character country code should be returned when testing ' . $testFor . '.');
	}

	/**
	 * provideFindCustomerTotalsReportCountry
	 *
	 * @return array
	 */
	public function provideFindCustomerTotalsReportCountry() {
		return [
			[
				'data' => [
					'field' => 'ShippingAddress.entry_country_id',
				],
				'expected' => 'CR',
				'shipping country field',
			],
			[
				'data' => [
					'field' => 'DefaultAddress.entry_country_id',
				],
				'expected' => 'CR',
				'billing country field',
			],
		];
	}
}
