<?php
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('PaymentComponent', 'Controller/Component');

class SomeControllerTest extends Controller {
	public $paginate = null;
}

class TestPaymentComponent extends PaymentComponent {
	public $lastErrorMessage = false;
}

/**
 * PaymentComponent Test Case
 *
 */
class PaymentComponentTest extends CakeTestCase {

	/**
	 *
	 */
	public $fixtures = array(
		'app.order',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->Payment = new PaymentComponent($Collection);
		$this->componentStartup();
	}

	protected function setUpMocked($methods) {
		$Collection = new ComponentCollection();
		$this->Payment = $this->getMockBuilder('PaymentComponent')
			->setMethods($methods)
			->setConstructorArgs([$Collection])
			->getMock();
		$this->componentStartup();
	}

	protected function componentStartup() {
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		$this->Controller = new SomeControllerTest($CakeRequest, $CakeResponse);
		$this->Payment->startup($this->Controller);
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Payment);

		parent::tearDown();
	}

	public function testCustomerHasOrdersAwaitingPayment() {
		$customerId = 1;
		$this->Payment->Auth = $this->getMock('Auth', array('user'));
		$this->Payment->Auth->expects($this->once())
			->method('user')
			->with('customers_id')
			->will($this->returnValue($customerId));
		$result = $this->Payment->customerHasOrdersAwaitingPayment();

		$this->assertTrue($result);
	}

	public function testCustomerHasOrdersAwaitingPaymentWhen() {
		$customerId = 2;
		$this->Payment->Auth = $this->getMock('Auth', array('user'));
		$this->Payment->Auth->expects($this->once())
			->method('user')
			->with('customers_id')
			->will($this->returnValue($customerId));
		$result = $this->Payment->customerHasOrdersAwaitingPayment();

		$this->assertFalse($result);
	}

	public function testChargeSuccess() {
		$card = [];
		$result = ['test'];
		$this->setUpMocked(['prepareCardForCharge']);
		$this->Payment->paymentLib = $this->getMockBuilder('Payment')
			->disableOriginalConstructor()
			->getMock();
		$this->Payment->paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue($result));
		$this->assertEquals($result, $this->Payment->charge($card));
	}

	public function testChargeWillPrepareCardForCharge() {
		$card = [];
		$this->setUpMocked(['prepareCardForCharge']);
		$this->Payment->paymentLib = $this->getMockBuilder('Payment')
			->disableOriginalConstructor()
			->getMock();
		$this->Payment->expects($this->once())
			->method('prepareCardForCharge')
			->with($card)
			->will($this->returnValue($card));
		$this->Payment->charge($card);
	}

	/**
	 */
	public function testChargeFailure() {
		$card = [];
		$this->setUpMocked(['log']);
		$this->Payment->expects($this->once())
			->method('log')
			->with(
				$this->stringContains('PaymentComponent::charge'),
				$this->identicalTo('orders')
			);
		$this->assertFalse($this->Payment->charge($card));
	}

	/**
	 * Confirm at component startup an instance of the Payment lib is available
	 * to the component.
	 *
	 * @return void
	 */
	public function testStartup() {
		$this->assertInstanceOf('Payment', $this->Payment->paymentLib);
	}

	/**
	 * Confirm upon component shutdown the Payment lib property is removed.
	 *
	 * @return void
	 */
	public function testShutdown() {
		$this->assertInstanceOf('Payment', $this->Payment->paymentLib);
		$this->Payment->shutdown($this->Controller);
		$this->assertNull($this->Payment->paymentLib);
	}

	/**
	 * Confirm if the Payment lib property is not null and instance of the
	 * Payment lib is returned. The `PayPal` configure var is deleted to
	 * show it's not being used to instantiate a new Payment object.
	 *
	 * @return void
	 */
	public function testGetPaymentLibNotSet() {
		Configure::delete('PayPal');
		$result =$this->Payment->getPaymentLib();
		$this->assertInstanceOf('Payment', $result);
	}

	/**
	 * Confirm if the Payment object is not set and PayPal credentials are missing
	 * the expected exception is thrown.
	 *
	 * @return void
	 */
	public function testGetPaymentLibMissingCredentials() {
		Configure::delete('PayPal');
		$this->Payment->paymentLib = null;
		$this->setExpectedException('InternalErrorException', 'Payment gateway not properly configured');
		$result =$this->Payment->getPaymentLib();
	}

	/**
	 * Confirm lastErrorMessage returns the expected property if set.
	 *
	 * @return void
	 */
	public function testLastErrorMessage() {
		$this->Payment = $this->getMockBuilder('TestPaymentComponent')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$this->Payment->lastErrorMessage = 'canary';

		$result =$this->Payment->lastErrorMessage();
		$this->assertSame('canary', $result);
	}

	/**
	 * Confirm bool false is returned and Order::find() is not called when a
	 * customer id is not set.
	 *
	 * @return void
	 */
	public function testCustomerHasOrdersAwaitingPaymentInvalidCustomer() {
		$customerId = null;
		$this->Payment->Auth = $this->getMockBuilder('Auth')
		   ->setMethods(['user'])
		   ->getMock();
		$Order = $this->getMockForModel('Order', ['find']);

		$this->Payment->Auth->expects($this->once())
			->method('user')
			->with('customers_id')
			->will($this->returnValue($customerId));
		$Order->expects($this->never())
			->method('find');

		$result = $this->Payment->customerHasOrdersAwaitingPayment();

		$this->assertFalse($result);
	}

	/**
	 * Confirm bool true is returned and Order::find() is not called when a
	 * customer has a set `hasOrdersAwaitingPayment` session value.
	 *
	 * @return void
	 */
	public function testCustomerHasOrdersAwaitingPaymentSessionSet() {
		$customerId = 1;
		$this->Payment->Auth = $this->getMockBuilder('Auth')
		   ->setMethods(['user'])
		   ->getMock();
		$this->Payment->Session = $this->getMockBuilder('Session')
		   ->setMethods(['read'])
		   ->getMock();
		$Order = $this->getMockForModel('Order', ['find']);

		$this->Payment->Auth->expects($this->once())
			->method('user')
			->with('customers_id')
			->will($this->returnValue($customerId));
		$this->Payment->Session->expects($this->once())
			->method('read')
			->will($this->returnValue(true));
		$Order->expects($this->never())
			->method('find');

		$result = $this->Payment->customerHasOrdersAwaitingPayment();

		$this->assertTrue($result);
	}

	/**
	 * Confirm the expected card type is returned with a valid test card number.
	 *
	 * @return void
	 */
	public function testGetCardTypeValid() {
		$number = '4242424242424242';
		$result = $this->Payment->getCardType($number);
		$this->assertSame('visa', $result);
	}

	/**
	 * Confirm bool false is returned with an invalid card number.
	 *
	 * @return void
	 */
	public function testGetCardTypeInvalid() {
		$number = '12345';
		$result = $this->Payment->getCardType($number);
		$this->assertFalse($result);
	}

	/**
	 * Confirm bool true is returned with a valid test card number.
	 *
	 * @return void
	 */
	public function testCardNumberIsValidWithValidNumber() {
		$number = '4242424242424242';
		$result = $this->Payment->cardNumberIsValid($number);
		$this->assertTrue($result);
	}

	/**
	 * Confirm bool false is returned with an invalid test card number.
	 *
	 * @return void
	 */
	public function testCardNumberIsValidWithInvalidNumber() {
		$number = '12345';
		$result = $this->Payment->cardNumberIsValid($number);
		$this->assertFalse($result);
	}
}
