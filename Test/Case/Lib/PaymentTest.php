<?php
App::uses('Payment', 'Lib');

class TestPayment extends Payment {
	public function massageAddressData($address) {
		return parent::massageAddressData($address);
	}

	public function addressHasValidCountry($address) {
		return parent::addressHasValidCountry($address);
	}

	public function authorizeFundingInstrument($fi) {
		return parent::authorizeFundingInstrument($fi);
	}
}

class PaymentTest extends PHPUnit_Framework_TestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Configure::write('PayPal.allowedBillingCountries', ['US', 'GB']);

		$this->Payment = $this->getMockBuilder('TestPayment')
			->setMethods(array('authorizeFundingInstrument', 'getFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();

		$this->paypalApiPayment = $this->getMockBuilder('PayPal\Api\Payment')
			->disableOriginalConstructor()
			->setMethods(array('create'))
			->getMock();
		$this->fi = $this->getMockBuilder('FundingInstrument')
			->disableOriginalConstructor()
			->getMock();

		$this->goodAddress = [
			'Address' => [
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_state' => 'NY',
				'entry_postcode' => '12345',
			],
			'Zone' => ['zone_code' => 'NY'],
			'Country' => ['countries_iso_code_2' => 'US'],
		];
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Payment, $this->paypalApiPayment, $this->fi);
		parent::tearDown();
	}

	/**
	 *
	 */
	public function testAuthorizeCardSuccess() {
		$Payment = $this->getMockBuilder('TestPayment')
			->setMethods(array('authorizeFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$Payment->expects($this->once())
			->method('authorizeFundingInstrument')
			->will($this->returnValue($this->paypalApiPayment));
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->paypalApiPayment->state = 'approved';

		$card = array(
			'cc_number' => '5556813796868175',
			'cc_expires_month' => '05',
			'cc_expires_year' => '80',
			'cc_cvv' => '123',
			'cc_firstname' => 'Joe',
			'cc_lastname' => 'Test',
		);
		$result = $Payment->authorizeCard($card, $this->goodAddress);

		$this->assertTrue($result);
	}

	/**
	 *
	 */
	public function testAuthorizeCardDeniedByService() {
		$this->Payment->expects($this->once())
			->method('getFundingInstrument')
			->will($this->returnValue($this->fi));
		$this->Payment->expects($this->once())
			->method('authorizeFundingInstrument')
			->will($this->returnValue($this->paypalApiPayment));
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->paypalApiPayment->state = 'denied';

		$card = array(
			'number' => '',
			'expire_month' => '',
			'expire_year' => '',
			'cvv2' => '',
			'first_name' => '',
			'last_name' => '',
		);
		$address = array(
			'line1' => '',
			'line2' => '',
			'city' => '',
			'state' => '',
			'country_code' => '',
			'postal_code' => '',
		);

		$result = $this->Payment->authorizeCard($card, $address);

		$this->assertFalse($result);
	}

	/**
	 *
	 */
	public function testAuthorizeCardGetFundingInstrumentReturnsFalse() {
		$this->Payment->expects($this->once())
			->method('getFundingInstrument')
			->will($this->returnValue(false));
		$this->Payment->expects($this->never())
			->method('authorizeFundingInstrument')
			->will($this->returnValue($this->paypalApiPayment));

		$card = array(
			'number' => '',
			'expire_month' => '',
			'expire_year' => '',
			'cvv2' => '',
			'first_name' => '',
			'last_name' => '',
		);
		$address = array(
			'line1' => '',
			'line2' => '',
			'city' => '',
			'state' => '',
			'country_code' => '',
			'postal_code' => '',
		);

		$result = $this->Payment->authorizeCard($card, $address);

		$this->assertFalse($result);
	}

	/**
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp #error validating the credit card#
	 */
	public function testAuthorizeCardPayPalValidationErrorThrowsException() {
		$json = json_encode(array(
			'name' => 'VALIDATION_ERROR',
			'message' => 'PayPal Message'
		));
		$exception = $this->getMockBuilder('PayPal\Exception\PayPalConnectionException')
			->disableOriginalConstructor()
			->setMethods(array('getData'))
			->getMock();
		$exception->expects($this->once())
			->method('getData')
			->will($this->returnValue($json));
		$this->Payment->expects($this->once())
			->method('getFundingInstrument')
			->will($this->returnValue($this->fi));
		$this->Payment->expects($this->once())
			->method('authorizeFundingInstrument')
			->will($this->returnValue($this->paypalApiPayment));
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->throwException($exception));

		$card = array(
			'number' => '',
			'expire_month' => '',
			'expire_year' => '',
			'cvv2' => '',
			'first_name' => '',
			'last_name' => '',
		);
		$address = array(
			'line1' => '',
			'line2' => '',
			'city' => '',
			'state' => '',
			'country_code' => '',
			'postal_code' => '',
		);

		$result = $this->Payment->authorizeCard($card, $address);
	}

	/**
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp #PayPal Message#
	 */
	public function testAuthorizeCardPayPal500ErrorThrowsException() {
		$json = json_encode(array(
			'name' => 'INTERNAL_SERVICE_ERROR',
			'message' => 'PayPal Message'
		));
		$exception = $this->getMockBuilder('PayPal\Exception\PayPalConnectionException')
			->disableOriginalConstructor()
			->setMethods(array('getData'))
			->getMock();
		$exception->expects($this->once())
			->method('getData')
			->will($this->returnValue($json));
		$this->Payment->expects($this->once())
			->method('getFundingInstrument')
			->will($this->returnValue($this->fi));
		$this->Payment->expects($this->once())
			->method('authorizeFundingInstrument')
			->will($this->returnValue($this->paypalApiPayment));
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->throwException($exception));

		$card = array(
			'number' => '',
			'expire_month' => '',
			'expire_year' => '',
			'cvv2' => '',
			'first_name' => '',
			'last_name' => '',
		);
		$address = array(
			'line1' => '',
			'line2' => '',
			'city' => '',
			'state' => '',
			'country_code' => '',
			'postal_code' => '',
		);

		$result = $this->Payment->authorizeCard($card, $address);
	}

	public function XtestStoreCard() {
		$card = array(
			'number' => '4242424242424242',
			'expire_month' => '10',
			'expire_year' => '18',
			'cvv2' => '424',
			'first_name' => 'Tom',
			'last_name' => 'Tester',
		);
		$result = $this->Payment->storeCard($card);

		$this->assertTrue(false);
	}

	public function testAuthorizeAndStoreCard() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeCard', 'storeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->Payment->expects($this->once())
			->method('authorizeCard')
			->will($this->returnValue(true));
		$this->Payment->expects($this->once())
			->method('storeCard')
			->will($this->returnValue(true));
		$card = array();
		$address = array();
		$result = $this->Payment->authorizeAndStoreCard($card, $address);

		$this->assertTrue($result);
	}

	public function testAuthorizeAndStoreCardAuthFails() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeCard', 'storeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->Payment->expects($this->once())
			->method('authorizeCard')
			->will($this->returnValue(false));
		$this->Payment->expects($this->never())
			->method('storeCard');
		$card = array(
			'number' => '4242424242424242',
			'expire_month' => '10',
			'expire_year' => '18',
			'cvv2' => '424',
			'first_name' => 'Tom',
			'last_name' => 'Tester',
		);
		$address = array();
		$result = $this->Payment->authorizeAndStoreCard($card, $address);

		$this->assertFalse($result);
	}

	public function testAuthorizeAndStoreCardStoreFails() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeCard', 'storeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->Payment->expects($this->once())
			->method('authorizeCard')
			->will($this->returnValue(true));
		$this->Payment->expects($this->once())
			->method('storeCard')
			->will($this->returnValue(false));
		$card = array(
			'number' => '4242424242424242',
			'expire_month' => '10',
			'expire_year' => '18',
			'cvv2' => '424',
			'first_name' => 'Tom',
			'last_name' => 'Tester',
		);
		$address = array(
			'line1' => '',
			'line2' => '',
			'city' => '',
			'state' => '',
			'country_code' => '',
			'postal_code' => '',
		);
		$result = $this->Payment->authorizeAndStoreCard($card, $address);

		$this->assertFalse($result);
	}

	public function testChargeCard() {
		$cardId = 'CARD-07259026HW151010XKTJ6V7I';
		$paymentId = 'PAY-C9QWUIHRPEAOURPNCP';
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getPayment'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->paypalApiPayment->state = 'approved';
		$this->paypalApiPayment->id = $paymentId;
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->Payment->expects($this->once())
			->method('getPayment')
			->will($this->returnValue($this->paypalApiPayment));

		$result = $this->Payment->chargeCard($cardId, array('total' => '1'));

		$this->assertEquals($paymentId, $result);
	}

	public function testChargeCardWithCardData() {
		$cardId = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'Tester',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '09',
			'cc_expires_year' => '20',
			'cc_cvv' => '424',
		);
		$address = array(
			'Address' => array(
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_postcode' => '12345',
			),
			'Zone' => array('zone_code' => 'NY'),
			'Country' => array('countries_iso_code_2' => 'US'),
		);
		$paymentId = 'PAY-C9QWUIHRPEAOURPNCP';
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getPayment'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->paypalApiPayment->state = 'approved';
		$this->paypalApiPayment->id = $paymentId;
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->Payment->expects($this->once())
			->method('getPayment')
			->will($this->returnValue($this->paypalApiPayment));

		$result = $this->Payment->chargeCard($cardId, array(
			'address' => $address,
			'total' => '1'
		));

		$this->assertEquals($paymentId, $result);
	}

	public function testChargeCardFails() {
		$cardId = 'CARD-07259026HW151010XKTJ6V7I';
		$paymentId = 'PAY-C9QWUIHRPEAOURPNCP';
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getPayment'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->paypalApiPayment->state = 'not-approved';
		$this->paypalApiPayment->id = $paymentId;
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->Payment->expects($this->once())
			->method('getPayment')
			->will($this->returnValue($this->paypalApiPayment));

		$result = $this->Payment->chargeCard($cardId, array('total' => '1'));

		$this->assertFalse($result);
	}

	/**
	 * @expectedException Exception
	 */
	public function testChargeCardGatewayThrowsException() {
		$cardId = 'CARD-07259026HW151010XKTJ6V7I';
		$paymentId = 'PAY-C9QWUIHRPEAOURPNCP';
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getPayment'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->paypalApiPayment->state = 'not-approved';
		$this->paypalApiPayment->id = $paymentId;
		$json = json_encode(array(
			'name' => 'STRANGE_ERROR',
			'message' => 'Something went wrong.'
		));
		$exception = $this->getMockBuilder('PayPal\Exception\PayPalConnectionException')
			->disableOriginalConstructor()
			->setMethods(array('getData'))
			->getMock();
		$exception->expects($this->once())
			->method('getData')
			->will($this->returnValue($json));
		$this->paypalApiPayment->expects($this->once())
			->method('create')
			->will($this->throwException($exception));
		$this->Payment->expects($this->once())
			->method('getPayment')
			->will($this->returnValue($this->paypalApiPayment));

		$result = $this->Payment->chargeCard($cardId, array('total' => '1'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testChargeCardMissingTotal() {
		$cardId = 'CARD-07259026HW151010XKTJ6V7I';
		$paymentId = 'PAY-C9QWUIHRPEAOURPNCP';
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getPayment'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->paypalApiPayment->state = 'not-approved';
		$this->paypalApiPayment->id = $paymentId;
		$this->paypalApiPayment->expects($this->never())
			->method('create')
			->will($this->returnSelf());
		$this->Payment->expects($this->never())
			->method('getPayment')
			->will($this->returnValue($this->paypalApiPayment));

		$result = $this->Payment->chargeCard($cardId, array('address' => array()));
	}

	public function testGetCreditCard() {
		$card = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '4417119669820331',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => '123',
		);

		$result = $this->Payment->getCreditCard($card);

		$this->assertTrue(is_object($result));
		$this->assertEquals('PayPal\Api\CreditCard', get_class($result));
		$this->assertEquals('123', $result->getCvv2());
	}

	public function testGetCreditCardNotUsedCVV() {
		$card = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '4417119669820331',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => 'not_used',
		);

		$result = $this->Payment->getCreditCard($card);

		$this->assertTrue(is_object($result));
		$this->assertEquals('PayPal\Api\CreditCard', get_class($result));
		$this->assertNull($result->getCvv2());
	}

	/**
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage cvv2
	 */
	public function testGetCreditCardMissingCVVKey() {
		$card = array(
			//'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '4417119669820331',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
		);

		$result = $this->Payment->getCreditCard($card);
	}

	/**
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage first_name
	 */
	public function testGetCreditCardMissingFirstNameKey() {
		$card = array(
			//'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '4417119669820331',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => '123',
		);

		$result = $this->Payment->getCreditCard($card);
	}

	/**
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage number
	 */
	public function testGetCreditCardRequiredKeyIsEmpty() {
		$card = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => '123',
		);

		$result = $this->Payment->getCreditCard($card);
	}

	public function testGetAddress() {
		$address = array(
			'line1' => '123 Street',
			'line2' => '',
			'city' => 'Gotham',
			'state' => 'NY',
			'country_code' => 'US',
			'postal_code' => '12345',
		);

		$result = $this->Payment->getAddress($address);

		$this->assertTrue(is_object($result));
		$this->assertEquals('PayPal\Api\Address', get_class($result));
	}

	/**
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage line1
	 */
	public function testGetAddressRequiredKeyIsEmpty() {
		$address = array(
			'line1' => '',
			'city' => 'Gotham',
			'state' => 'NY',
			'country_code' => 'US',
			'postal_code' => '12345',
		);

		$this->Payment->getAddress($address);
	}

	public function testGetFundingInstrumentDefault() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();

		$card = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '424242424242',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => '123',
		);
		$address = array(
			'Address' => array(
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_postcode' => '12345',
			),
			'Zone' => array('zone_code' => 'NY'),
			'Country' => array('countries_iso_code_2' => 'US'),
		);
		$result = $this->Payment->getFundingInstrument($card, $address);

		$this->assertTrue(is_object($result), 'Should return an object.');
		$this->assertEquals('PayPal\Api\FundingInstrument', get_class($result), 'Returned object should be type PayPal\Api\FundingInstrument.');
		$this->assertNotEmpty($result->credit_card->billing_address);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testGetFundingInstrumentWithoutAddressFails() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();

		$card = array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '424242424242',
			'cc_expires_month' => '6',
			'cc_expires_year' => '2025',
			'cc_cvv' => '123',
		);

		$result = $this->Payment->getFundingInstrument($card);
	}

	public function testGetFundingInstrumentAsStringWithoutAddress() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();

		$card = 'CARD-1MD19612EW4364010KGFNJQI';

		$result = $this->Payment->getFundingInstrument($card);

		$this->assertTrue(is_object($result), 'Should return an object.');
		$this->assertEquals('PayPal\Api\FundingInstrument', get_class($result), 'Returned object should be type PayPal\Api\FundingInstrument.');
		$this->assertNotEmpty($result->credit_card_token);
		$this->assertInstanceOf('PayPal\Api\CreditCardToken', $result->credit_card_token, 'Returned object should be type PayPal\Api\CreditCardToken.');
	}

	public function testGetFundingInstrumentAsArrayWithCardToken() {
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('authorizeFundingInstrument'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();

		$card = array('card_token' => 'CARD-1MD19612EW4364010KGFNJQI');

		$result = $this->Payment->getFundingInstrument($card);

		$this->assertTrue(is_object($result), 'Should return an object.');
		$this->assertEquals('PayPal\Api\FundingInstrument', get_class($result), 'Returned object should be type PayPal\Api\FundingInstrument.');
		$this->assertNotEmpty($result->credit_card_token);
		$this->assertInstanceOf('PayPal\Api\CreditCardToken', $result->credit_card_token, 'Returned object should be type PayPal\Api\CreditCardToken.');
	}

	public function testGetPayment() {
		$payer = array();
		$transactions = array(new stdClass());

		$result = $this->Payment->getPayment($payer, $transactions);

		$this->assertTrue(is_object($result));
		$this->assertTrue(is_array($result->transactions), 'Transactions should be an array.');
		$this->assertEquals('PayPal\Api\Payment', get_class($result));
		$this->assertEquals('authorize', $result->intent, 'Intent should be set to "authorize" when not specified in method call.');
	}

	public function testGetPaymentWithSaleIntent() {
		$payer = array();
		$transactions = array(new stdClass());
		$intent = 'sale';

		$result = $this->Payment->getPayment($payer, $transactions, $intent);

		$this->assertTrue(is_object($result));
		$this->assertTrue(is_array($result->transactions), 'Transactions should be an array.');
		$this->assertEquals('PayPal\Api\Payment', get_class($result));
		$this->assertEquals('sale', $result->intent, 'Intent should be as provided in method argument.');
	}

	public function testGetPaymentWithSingleObjectTransactions() {
		$payer = array();
		$transactions = new stdClass();

		$result = $this->Payment->getPayment($payer, $transactions);

		$this->assertTrue(is_object($result));
		$this->assertTrue(is_array($result->transactions), 'Transactions should be an array.');
		$this->assertEquals('PayPal\Api\Payment', get_class($result));
		$this->assertEquals('authorize', $result->intent, 'Intent should be set to "authorize" when not specified in method call.');
	}

	public function testStoreCard() {
		$cardId = 'CARD-ASDF789D23UJLS';
		$creditCard = $this->getMockBuilder('PayPal\Api\CreditCard')
			->setMethods(array('create'))
			->getMock();
		$creditCard->id = $cardId;
		$creditCard->expects($this->once())
			->method('create')
			->will($this->returnSelf());
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getCreditCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->Payment->expects($this->once())
			->method('getCreditCard')
			->will($this->returnValue($creditCard));
		$card = array();

		$result = $this->Payment->storeCard($card);

		$this->assertEquals($result, $cardId, 'Should return Card ID from payment gateway.');
	}

	/**
	 * @expectedException Exception
	 */
	public function testStoreCardGatewayThrowsException() {
		$cardId = 'CARD-ASDF789D23UJLS';
		$creditCard = $this->getMockBuilder('PayPal\Api\CreditCard')
			->setMethods(array('create'))
			->getMock();
		$creditCard->id = $cardId;
		$ppException = new \PayPal\Exception\PayPalConnectionException('Something went wrong.', 500);
		$ppException->setData('{"error": "Boom!", "error_description": "Something went wrong."}');
		$creditCard->expects($this->once())
			->method('create')
			->will($this->throwException($ppException));
		$this->Payment = $this->getMockBuilder('Payment')
			->setMethods(array('getCreditCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$this->Payment->expects($this->once())
			->method('getCreditCard')
			->will($this->returnValue($creditCard));
		$card = array();

		$result = $this->Payment->storeCard($card);

		$this->assertNotEquals($result, $cardId, 'Should return Card ID from payment gateway.');
	}

	public function testMassageAddressDataValid() {
		$address = array(
			'Address' => array(
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_postcode' => '12345',
			),
			'Zone' => array('zone_code' => 'NY'),
			'Country' => array('countries_iso_code_2' => 'GB'),
		);
		$expected = [
			'line1' => '123 Street',
			'line2' => '',
			'city' => 'Gotham',
			'state' => 'NY',
			'country_code' => 'GB',
			'postal_code' => '12345',
		];
		$result = $this->Payment->massageAddressData($address);
		$this->assertSame($expected, $result);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testMassageAddressDataMissingCountry() {
		$address = array(
			'Address' => array(
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_postcode' => '12345',
			),
			'Zone' => array('zone_code' => 'NY'),
		);
		$this->Payment->massageAddressData($address);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testMassageAddressDataMissingZone() {
		$address = array(
			'Address' => array(
				'entry_street_address' => '123 Street',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_postcode' => '12345',
			),
			'Country' => array('countries_iso_code_2' => 'GB'),
		);
		$this->Payment->massageAddressData($address);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testMassageAddressDataMissingAddress() {
		$address = array(
			'Zone' => array('zone_code' => 'NY'),
			'Country' => array('countries_iso_code_2' => 'GB'),
		);
		$this->Payment->massageAddressData($address);
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	public function testMassageAddressDataBadAddress() {
		$address = array(
			'address' => 'bad string',
			'Zone' => array('zone_code' => 'NY'),
			'Country' => array('countries_iso_code_2' => 'GB'),
		);
		$this->Payment->massageAddressData($address);
	}

	/**
	 * @dataProvider provideAddressHasValidCountry
	 */
	public function testAddressHasValidCountry($address, $expected) {
		$result = $this->Payment->addressHasValidCountry($address);
		$this->assertSame($expected, $result);
	}

	public function provideAddressHasValidCountry() {
		$addressWithCountry = function($country) {
			return ['Country' => ['countries_iso_code_2' => $country]];
		};

		return [
			[[], false],
			[$addressWithCountry('US'), true],
			[$addressWithCountry('GB'), true],
			[$addressWithCountry('BD'), false],
		];
	}

	/**
	 * Confirm that authorizeFundingInstrument() can set and store expected
	 * values based on provided $fi data and payment defaults.
	 *
	 * @return void
	 */
	public function testAuthorizeFundingInstrument() {
		$fi = [
			'credit_card' => [
				'number' => '4242424242424242',
				'type' => 'visa',
				'expire_month' => '12',
				'expire_year' => '2017',
				'first_name' => 'Test',
				'last_name' => 'User',
				'cvv2' => '123',
				'billing_address' => [
					'line1' => 'PO Box 1',
					'line2' => '',
					'city' => 'Testville',
					'country_code' => 'US',
					'postal_code' => '91361',
					'state' => 'CA'
				],
			],
		];

		$this->Payment = $this->getMockBuilder('TestPayment')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$result = $this->Payment->authorizeFundingInstrument($fi);
		$this->assertInstanceOf('PayPal\Api\Payment', $result, 'should be an instance of PayPal\Api\Payment');

		$data = $result->toArray();
		$this->assertArrayHasKey('intent', $data, 'should have key set by getPayment()');
		$this->assertSame('authorize', $data['intent'], 'should have default value "authorize"');
		$this->assertArrayHasKey('payer', $data, 'should have key set by getPayer()');
		$this->assertSame('credit_card', $data['payer']['payment_method'], 'should have default value of "credit_card"');
		$this->assertArrayHasKey('funding_instruments', $data['payer'], 'should have key set by getPayer()');
		$this->assertSame($fi, $data['payer']['funding_instruments'][0], 'should have identical arrays');
		$this->assertArrayHasKey('transactions', $data, 'should have key set by getTransaction()');
		$this->assertArrayHasKey('amount', $data['transactions'][0], 'should have key set by getAmount()');
		$this->assertSame('USD', $data['transactions'][0]['amount']['currency'], 'should have default value of "USD"');
		$this->assertSame('1.00', $data['transactions'][0]['amount']['total'], 'should have default value of "1.00"');
		$this->assertSame('1.00', $data['transactions'][0]['amount']['details']['subtotal'], 'should have default value of "1.00"');
		$this->assertSame('APO Box card authorization.', $data['transactions'][0]['description'], 'should have default description text');
	}
}
