<?php
App::uses('OrdersController', 'Controller');
App::uses('Payment', 'Lib');

/**
 * TestOrdersController to access protected methods for direct testing.
 */
class TestOrdersController extends OrdersController {
	public $uses = array('Order');

	public function _marshalAddressTo($type, $address) {
		return parent::_marshalAddressTo($type, $address);
	}

	public function _changeOrderStatus($id, $data) {
		return parent::_changeOrderStatus($id, $data);
	}

	public function _setDataForOrder($data, $customer) {
		return parent::_setDataForOrder($data, $customer);
	}

	public function _setAddressesForOrder($data, $customerId, $fallbackToDefaults = false) {
		return parent::_setAddressesForOrder($data, $customerId, $fallbackToDefaults);
	}

	public function _fallbackToDefaultAddresses($data, $customerId) {
		return parent::_fallbackToDefaultAddresses($data, $customerId);
	}

	public function _prepareChargeData($order) {
		return parent::_prepareChargeData($order);
	}

	public function _sendChargeResponse($order, $type) {
		return parent::_sendChargeResponse($order, $type);
	}

	public function addPostage($id) {
		return parent::addPostage($id);
	}

	public function printZebraLabel($id, $asImage = false) {
		return parent::printZebraLabel($id, $asImage);
	}

	public function prepareLabelData($order) {
		return parent::prepareLabelData($order);
	}

	public function initZebraLabel($config = []) {
		return parent::initZebraLabel($config);
	}

	public function initUsps($config = []) {
		return parent::initUsps($config);
	}

	public function initFedex() {
		return parent::initFedex();
	}

	public function printFedexLabel($id, $reprint = false) {
		return parent::printFedexLabel($id, $reprint);
	}

	public function _checkOrSetAddress() {
		return parent::_checkOrSetAddress();
	}

	public function getUspsRates($order) {
		return parent::getUspsRates($order);
	}

	public function getFedexRates($order) {
		return parent::getFedexRates($order);
	}

	public function _setMailClass($customer) {
		return parent::_setMailClass($customer);
	}

	public function _processCharge($isInvoice, $order) {
		return parent::_processCharge($isInvoice, $order);
	}

	public function _markFailedPayment($id) {
		return parent::_markFailedPayment($id);
	}
}

/**
 * OrdersController Test Case
 *
 */
class OrdersControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order',
		'app.country',
		'app.customer',
		'app.address',
		'app.insurance',
		'app.zone',
		'app.order',
		'app.order_total',
		'app.order_status',
		'app.order_status_history',
		'app.admin',
		'app.customer_reminder',
		'app.custom_order',
		'app.order_data',
		'app.tracking',
		'app.authorized_name',
	);

	/**
	 * setup
	 *
	 * @return void
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 * Main view test. Tests various parts of the controller vars and view.
	 *
	 * - package labels do not show for customers
	 *
	 * @return	void
	 */
	public function testViewGetWithLabels() {
		$customerId = 1;
		$orderId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$html = $this->testAction($url, array('method' => 'get', 'return' => 'view'));

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayNotHasKey('ordersStatuses', $this->vars);
		$this->assertArrayHasKey('currentStatusHistory', $this->vars);
		$this->assertInternalType('array', $this->vars['currentStatusHistory']);
		$this->assertArrayHasKey('statusHistories', $this->vars);
		$this->assertInternalType('array', $this->vars['statusHistories']);
		$this->assertTrue(!empty($this->vars['statusHistories']), 'No OrderStatusHistories found.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['statusHistories'][0]);
		$this->assertArrayHasKey('orderCharges', $this->vars);
		$this->assertInternalType('array', $this->vars['orderCharges']);
		$this->assertNotContains('NonMachinable', $html);
		$this->assertNotContains('Oversize', $html);
		$this->assertNotContains('Balloon Rate', $html);
	}

	/**
	 * Test specifically that package labels do not show if they don't exist
	 * on this order.
	 *
	 * @return	void
	 */
	public function testViewGetNoLabels() {
		$customerId = 2;
		$orderId = 2;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$html = $this->testAction($url, array('method' => 'get', 'return' => 'view'));

		$this->assertNotContains('NonMachinable', $html);
		$this->assertNotContains('Oversize', $html);
		$this->assertNotContains('Balloon Rate', $html);
	}

	/**
	 * @expectedException ForbiddenException
	 *
	 * @return	void
	 */
	public function testViewOrderNotBelongsToCustomer() {
		$customerId = 1;
		$orderId = 2;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'get'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testViewPUT() {
		$customerId = 1;
		$orderId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testViewPOST() {
		$customerId = 1;
		$orderId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testViewDELETE() {
		$customerId = 1;
		$orderId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallyGET() {
		$orderId = 1;
		$customerId = 1;
		$Customers = $this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('order', $this->vars, '$order should be set');
		$this->assertArrayHasKey('OrderTotal', $this->vars['order'], '$order should contain OrderTotal');
		$this->assertArrayHasKey('selected', $this->vars, '$selected should be set');
		$this->assertArrayHasKey('orderCharges', $this->vars, '$orderCharges should be set');
		$this->assertArrayNotHasKey('Location', $this->headers, 'GET should not redirect');
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testPayManuallyDELETE() {
		$orderId = 1;
		$customerId = 1;
		$Customers = $this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallyWithSavingCreditCardInformation() {
		$orderId = 3;
		$customerId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(true));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Activity
			->expects($this->once())
			->method('record')
			->with('edit', $customerId, 'payment_info');
		$Orders->Order->Customer->CustomerReminder
			->expects($this->at(0))
			->method('clearRecord')
			->with($orderId, 'awaiting_payment');
		$Orders->Order->Customer->CustomerReminder
			->expects($this->at(1))
			->method('clearRecord')
			->with($customerId, 'payment_info');
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should be 4 after sucessful manual payment.');
		$this->assertEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertEquals('Tom', $customerAfter['Customer']['cc_firstname']);
		$this->assertEquals('TheTester', $customerAfter['Customer']['cc_lastname']);
		$this->assertStringEndsWith('4242', $customerAfter['Customer']['cc_number']);
		$this->assertEquals('09', $customerAfter['Customer']['cc_expires_month']);
		$this->assertEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year']);

		$this->assertNotSame('Tom TheTester', $orderBefore['Order']['cc_owner']);
		$this->assertSame('Tom TheTester', $orderAfter['Order']['cc_owner']);
		$this->assertNotSame('123 Somewhere Ave.', $orderBefore['Order']['billing_street_address']);
		$this->assertSame('123 Somewhere Ave.', $orderAfter['Order']['billing_street_address']);
		$this->assertNotSame('Gotham', $orderBefore['Order']['billing_city']);
		$this->assertSame('Gotham', $orderAfter['Order']['billing_city']);
		$this->assertNotSame('91361', $orderBefore['Order']['billing_postcode']);
		$this->assertSame('91361', $orderAfter['Order']['billing_postcode']);

		$this->assertNotSame($customerBefore['Customer']['customers_default_address_id'], $customerAfter['Customer']['customers_default_address_id']);

		$this->assertArrayNotHasKey('order', $this->vars, '$order should not be set');
		$this->assertArrayNotHasKey('orderCharges', $this->vars, '$orderCharges should not be set');
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
		$this->assertStringEndsWith(
			Router::url(array('controller' => 'customers', 'action' => 'account')),
			$this->headers['Location'], 'Should redirect to account.'
		);
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallySavingCreditCardInformationFails() {
		$orderId = 1;
		$customerId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(true));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard', 'save'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$Orders->Order->Customer->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should be 4 after sucessful manual payment.');
		$this->assertEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertNotEquals('Tom', $customerAfter['Customer']['cc_firstname']);
		$this->assertNotEquals('TheTester', $customerAfter['Customer']['cc_lastname']);
		$this->assertNotEquals('09', $customerAfter['Customer']['cc_expires_month']);
		$this->assertNotEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year']);

		$this->assertArrayNotHasKey('order', $this->vars, '$order should not be set');
		$this->assertArrayNotHasKey('orderCharges', $this->vars, '$orderCharges should not be set');
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
		$this->assertStringEndsWith(
			Router::url(array('controller' => 'customers', 'action' => 'account')),
			$this->headers['Location'], 'Should redirect to account.'
		);
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallyChargingCardFails() {
		$orderId = 1;
		$customerId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(false));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertNotEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should not be 4 after payment failure.');
		$this->assertNotEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should not be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertNotEquals('Tom', $customerAfter['Customer']['cc_firstname']);
		$this->assertNotEquals('TheTester', $customerAfter['Customer']['cc_lastname']);
		$this->assertNotEquals('09', $customerAfter['Customer']['cc_expires_month']);
		$this->assertNotEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year']);

		$this->assertArrayHasKey('order', $this->vars, '$order should be set');
		$this->assertArrayHasKey('orderCharges', $this->vars, '$orderCharges should be set');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallyPaymentInformationValidationFails() {
		$orderId = 1;
		$customerId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->never())
			->method('chargeCard')
			->will($this->returnValue(false));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard', 'validates'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$Orders->Order->Customer->expects($this->once())
			->method('validates')
			->will($this->returnValue(false));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertNotEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should not be 4 after payment failure.');
		$this->assertNotEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should not be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertNotEquals('Tom', $customerAfter['Customer']['cc_firstname']);
		$this->assertNotEquals('TheTester', $customerAfter['Customer']['cc_lastname']);
		$this->assertNotEquals('09', $customerAfter['Customer']['cc_expires_month']);
		$this->assertNotEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year']);

		$this->assertArrayHasKey('order', $this->vars, '$order should be set');
		$this->assertArrayHasKey('orderCharges', $this->vars, '$orderCharges should be set');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallySavingCreditCardInformationWithExistingAddress() {
		$orderId = 1;
		$customerId = 1;
		$addressId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(true));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'customers_default_address_id' => $addressId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
		));
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should be 4 after sucessful manual payment.');
		$this->assertEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');
		$this->assertEquals('Tom TheTester', $orderAfter['Order']['cc_owner'], 'Order\'s card first name should be updated.');
		$this->assertNotEquals('4242424242424242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be masked.');
		$this->assertStringEndsWith('4242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be updated.');
		$this->assertEquals('09' . $ccExpireYear, $orderAfter['Order']['cc_expires'], 'Order\'s card expiration month should be updated.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertEquals('Tom', $customerAfter['Customer']['cc_firstname'], 'Customer\'s card first name should be updated.');
		$this->assertEquals('TheTester', $customerAfter['Customer']['cc_lastname'], 'Customer\'s card last name  should be updated.');
		$this->assertNotEquals('4242424242424242', $customerAfter['Customer']['cc_number'], 'Customer\'s card number should be masked.');
		$this->assertStringEndsWith('4242', $customerAfter['Customer']['cc_number'], 'Customer\'s card number should be updated.');
		$this->assertEquals('09', $customerAfter['Customer']['cc_expires_month'], 'Customer\'s card expiration month should be updated.');
		$this->assertEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year'], 'Customer\'s card expiration year should be updated.');

		$this->assertNotSame('Tom TheTester', $orderBefore['Order']['cc_owner']);
		$this->assertSame('Tom TheTester', $orderAfter['Order']['cc_owner']);
		$this->assertSame($orderBefore['Order']['billing_street_address'], $orderAfter['Order']['billing_street_address']);
		$this->assertSame($orderBefore['Order']['billing_city'], $orderAfter['Order']['billing_city']);
		$this->assertSame($orderBefore['Order']['billing_postcode'], $orderAfter['Order']['billing_postcode']);

		$this->assertSame($customerBefore['Customer']['customers_default_address_id'], $customerAfter['Customer']['customers_default_address_id']);

		$this->assertArrayNotHasKey('order', $this->vars, '$order should not be set');
		$this->assertArrayNotHasKey('orderCharges', $this->vars, '$orderCharges should not be set');
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
		$this->assertStringEndsWith(
			Router::url(array('controller' => 'customers', 'action' => 'account')),
			$this->headers['Location'], 'Should redirect to account.'
		);
	}

	/**
	 * Tests that paying manually with a new address will succeed and that Zone
	 * is correctly added to the `address` array.
	 */
	public function testPayManuallySavingCreditCardInformationWithNewAddress() {
		$orderId = 1;
		$customerId = 1;
		$addressId = 'custom';
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'customers_default_address_id' => $addressId,
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$chargeOptions = [
			'address' => [
				'Customer' => array_merge($data['Customer'], ['customers_id' => $customerId]),
				'Address' => $data['Address'],
				'Zone' => ['zone_code' => 'IL'],
			],
			'total' => '112.600000',
			'description' => 'Order #1',
		];
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(true));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should be 4 after sucessful manual payment.');
		$this->assertEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');
		$this->assertEquals('Tom TheTester', $orderAfter['Order']['cc_owner'], 'Order\'s card first name should be updated.');
		$this->assertNotEquals('4242424242424242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be masked.');
		$this->assertStringEndsWith('4242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be updated.');
		$this->assertEquals('09' . $ccExpireYear, $orderAfter['Order']['cc_expires'], 'Order\'s card expiration month should be updated.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertEquals('Tom', $customerAfter['Customer']['cc_firstname'], 'Customer\'s card first name should be updated.');
		$this->assertEquals('TheTester', $customerAfter['Customer']['cc_lastname'], 'Customer\'s card last name  should be updated.');
		$this->assertNotEquals('4242424242424242', $customerAfter['Customer']['cc_number'], 'Customer\'s card number should be masked.');
		$this->assertStringEndsWith('4242', $customerAfter['Customer']['cc_number'], 'Customer\'s card number should be updated.');
		$this->assertEquals('09', $customerAfter['Customer']['cc_expires_month'], 'Customer\'s card expiration month should be updated.');
		$this->assertEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year'], 'Customer\'s card expiration year should be updated.');

		$this->assertNotSame('Tom TheTester', $orderBefore['Order']['cc_owner']);
		$this->assertSame('Tom TheTester', $orderAfter['Order']['cc_owner']);
		$this->assertNotSame('123 Somewhere Ave.', $orderBefore['Order']['billing_street_address']);
		$this->assertSame('123 Somewhere Ave.', $orderAfter['Order']['billing_street_address']);
		$this->assertNotSame('Gotham', $orderBefore['Order']['billing_city']);
		$this->assertSame('Gotham', $orderAfter['Order']['billing_city']);
		$this->assertNotSame('91361', $orderBefore['Order']['billing_postcode']);
		$this->assertSame('91361', $orderAfter['Order']['billing_postcode']);

		$this->assertNotSame($customerBefore['Customer']['customers_default_address_id'], $customerAfter['Customer']['customers_default_address_id']);

		$this->assertArrayNotHasKey('order', $this->vars, '$order should not be set');
		$this->assertArrayNotHasKey('orderCharges', $this->vars, '$orderCharges should not be set');
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
		$this->assertStringEndsWith(
			Router::url(array('controller' => 'customers', 'action' => 'account')),
			$this->headers['Location'], 'Should redirect to account.'
		);
	}

	/**
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testPayManuallyOrderNotExists() {
		$orderId = 999;
		$customerId = 1;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '1',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
		));

		$this->testAction($url, array('method' => 'post', 'data' => $data));
	}

	/**
	 *
	 * @return	void
	 */
	public function testPayManuallyWithoutSavingCreditCardInformation() {
		$orderId = 3;
		$customerId = 1;
		$paymentLib = $this->getMockBuilder('Payment', array('chargeCard'))
			->setConstructorArgs(array('clientId', 'clientSecret', 'sandbox'))
			->getMock();
		$paymentLib->expects($this->once())
			->method('chargeCard')
			->will($this->returnValue(true));
		$Orders = $this->setupAuth($customerId);
		$Orders->Payment->expects($this->once())
			->method('getPaymentLib')
			->will($this->returnValue($paymentLib));
		$Orders->Payment->paymentLib = $paymentLib;
		$Orders->Order->Customer->CustomerReminder
			->expects($this->at(0))
			->method('clearRecord')
			->with($orderId, 'awaiting_payment');

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array(
			'Customer' => array(
				'cc_firstname' => 'Tom',
				'cc_lastname' => 'TheTester',
				'cc_number' => '4242424242424242',
				'cc_expires_month' => '09',
				'cc_expires_year' => $ccExpireYear,
				'cc_cvv' => '424',
				'save' => '0',
			),
			'Address' => array(
				'entry_firstname' => 'Tom',
				'entry_lastname' => 'TheTester',
				'entry_street_address' => '123 Somewhere Ave.',
				'entry_suburb' => '',
				'entry_city' => 'Gotham',
				'entry_zone_id' => 23,
				'entry_postcode' => '91361',
				'entry_country_id' => '223',
			),
		);
		$Orders->Order->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard', 'validates'));
		$Orders->Order->Customer->expects($this->any())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		// Mocking validate() to ensure it is called only once.
		$Orders->Order->Customer->expects($this->once())
			->method('validates')
			->will($this->returnValue(true));
		$customerBefore = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountBefore = $Orders->Order->Customer->find('count');
		$orderCountBefore = $Orders->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$customerAfter = $Orders->Order->Customer->findByCustomersId($customerId);
		$customerCountAfter = $Orders->Order->Customer->find('count');
		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$orderCountAfter = $Orders->Order->find('count');

		$this->assertEquals('4', $orderAfter['Order']['orders_status'], 'Orders status should be 4 after sucessful manual payment.');
		$this->assertEquals('4', $orderAfter['Order']['billing_status'], 'billing_status should be 4 after sucessful manual payment.');
		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or delete Order records.');
		$this->assertEquals('Tom TheTester', $orderAfter['Order']['cc_owner'], 'Order\'s card first name should be updated.');
		$this->assertNotEquals('4242424242424242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be masked.');
		$this->assertStringEndsWith('4242', $orderAfter['Order']['cc_number'], 'Order\'s card number should be updated.');
		$this->assertEquals('09' . $ccExpireYear, $orderAfter['Order']['cc_expires'], 'Order\'s card expiration month should be updated.');

		$this->assertEquals($customerCountBefore, $customerCountAfter, 'Should not create or delete Customer records.');
		$this->assertNotEquals('Tom', $customerAfter['Customer']['cc_firstname'], 'Field should not be updated');
		$this->assertNotEquals('TheTester', $customerAfter['Customer']['cc_lastname'], 'Field should not be updated');
		$this->assertNotEquals('09', $customerAfter['Customer']['cc_expires_month'], 'Field should not be updated');
		$this->assertNotEquals($ccExpireYear, $customerAfter['Customer']['cc_expires_year'], 'Field should not be updated');
		$this->assertEquals('Tom TheTester', $orderAfter['Order']['cc_owner'], 'Order.cc_owner should be updated.');
		$this->assertEquals('XXXXXXXXXXXX4242', $orderAfter['Order']['cc_number'], 'Order.cc_number should be maked and updated.');
		$this->assertEquals('09' . $ccExpireYear, $orderAfter['Order']['cc_expires'], 'Order.cc_expires should be updated.');
		$this->assertArrayNotHasKey('order', $orderAfter['Order'], 'Order.cc_cvv after find should be masked.');

		$this->assertArrayNotHasKey('order', $this->vars, '$order should not be set');
		$this->assertArrayNotHasKey('orderCharges', $this->vars, '$orderCharges should not be set');
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull POST should redirect somewhere.');
		$this->assertStringEndsWith(
			Router::url(array('controller' => 'customers', 'action' => 'account')),
			$this->headers['Location'], 'Should redirect to account.'
		);
	}

	/**
	 * @expectedException BadRequestException
	 *
	 * @return	void
	 */
	public function testPayManuallyWithoutAddress() {
		$orderId = 1;
		$customerId = 1;
		$Orders = $this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$ccExpireYear = date('y', strtotime('+3 years'));
		$data = array('Customer' => array(
			'cc_firstname' => 'Tom',
			'cc_lastname' => 'TheTester',
			'cc_number' => '4242424242424242',
			'cc_expires_month' => '09',
			'cc_expires_year' => $ccExpireYear,
			'cc_cvv' => '424',
			'save' => '0',
		));
		$customerCountBefore = $Orders->Order->Customer->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
	}

	/**
	 * @expectedException BadRequestException
	 *
	 * @return	void
	 */
	public function testPayManuallyWithoutCustomerBilling() {
		$orderId = 1;
		$customerId = 1;
		$Orders = $this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'pay_manually',
			'id' => $orderId,
		));
		$data = array('Address' => array(
			'entry_firstname' => 'Tom',
			'entry_lastname' => 'TheTester',
			'entry_address' => '123 Somewhere Ave.',
			'entry_suburb' => '',
			'entry_city' => 'Gotham',
		));
		$customerCountBefore = $Orders->Order->Customer->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
	}

	/**
	 * Confirm that the expected view vars are set.
	 *
	 * @return	void
	 */
	public function testManagerSearch() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertGreaterThan(
			strtotime($this->vars['results'][4]['Order']['date_purchased']),
			strtotime($this->vars['results'][1]['Order']['date_purchased'])
		);
		$this->assertGreaterThan(
			strtotime($this->vars['results'][4]['Order']['last_modified']),
			strtotime($this->vars['results'][1]['Order']['last_modified'])
		);
		$this->assertArrayHasKey('fromThePast', $this->vars);
		$this->assertArrayHasKey('statusFilterOptions', $this->vars);
		$this->assertArrayHasKey('showStatus', $this->vars);
		$this->assertArrayHasKey('userIsManager', $this->vars);
		$this->assertArrayHasKey('customRequests', $this->vars);
		$this->assertArrayHasKey('OrderTotal', $this->vars['results'][0], '$results should contain at least one OrderTotal.');
		$this->assertArrayHasKey('Customer', $this->vars['results'][0], '$results should contain at least one Customer.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['results'][0], '$results should contain at least one OrderStatus.');
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerSearchPOST() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerSearchPUT() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerSearchDELETE() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * testManagerView method
	 *
	 * @return	void
	 */
	public function testManagerViewGet() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		));

		$html = $this->testAction($url, array('method' => 'get', 'return' => 'view'));

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('ordersStatuses', $this->vars);
		$this->assertEquals(5, count($this->vars['ordersStatuses']), 'Should set all statuses as options.');
		$this->assertArrayHasKey('currentStatusHistory', $this->vars);
		$this->assertInternalType('array', $this->vars['currentStatusHistory']);
		$this->assertArrayHasKey('statusHistories', $this->vars);
		$this->assertInternalType('array', $this->vars['statusHistories']);
		$this->assertTrue(!empty($this->vars['statusHistories']), 'No OrderStatusHistories found.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['statusHistories'][0]);
		$this->assertArrayHasKey('orderCharges', $this->vars);
		$this->assertInternalType('array', $this->vars['orderCharges']);
		$this->assertContains('NonMachinable', $html);
		$this->assertContains('Oversize', $html);
		$this->assertContains('Balloon Rate', $html);
		$this->assertArrayHasKey('invoiceCustomer', $this->vars);
		$this->assertNotNull($this->vars['xml']);
		$this->assertSame('usps', $this->vars['mailClass']);
		$this->assertSame('#', $this->vars['url']);
		$this->assertFalse($this->vars['reprint']);
		$this->assertSame('Print', $this->vars['action']);
		$this->assertSame(['manager' => true], $this->vars['level']);
	}

	/**
	 * Confirm that the expected view variables are set when an order has a
	 * `mail_class` of 'FEDEX' and is not a reprint request.
	 *
	 * @return	void
	 */
	public function testManagerViewGetFedex() {
		$userId = 1;
		$orderId = 3;
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		]);
		$expectedUrl = [
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => (string) $orderId,
			'manager' => true
		];

		$html = $this->testAction($url, ['method' => 'get']);

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('xml', $this->vars);
		$this->assertNull($this->vars['xml']);
		$this->assertSame('fedex', $this->vars['mailClass']);
		$this->assertSame($expectedUrl, $this->vars['url']);
		$this->assertFalse($this->vars['reprint']);
		$this->assertSame('Print', $this->vars['action']);
		$this->assertSame(['manager' => true], $this->vars['level']);
	}

	/**
	 * Confirm that the expected view variables are set when an order has a
	 * `mail_class` of 'FEDEX' and is a reprint request.
	 *
	 * @return	void
	 */
	public function testManagerViewGetFedexReprint() {
		$userId = 1;
		$orderId = 2;
		$this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		]);
		$expectedUrl = [
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => (string) $orderId,
			'manager' => true,
			'reprint' => 'reprint',
		];

		$html = $this->testAction($url, ['method' => 'get']);

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('xml', $this->vars);
		$this->assertNull($this->vars['xml']);
		$this->assertSame('fedex', $this->vars['mailClass']);
		$this->assertSame($expectedUrl, $this->vars['url']);
		$this->assertTrue($this->vars['reprint']);
		$this->assertSame('Reprint', $this->vars['action']);
		$this->assertSame(['manager' => true], $this->vars['level']);
	}

	/**
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerViewNonExistantOrder() {
		$userId = 1;
		$orderId = 999;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('order', $this->vars);
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerViewPOST() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'post'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerViewPUT() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerviewDELETE() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('method' => 'delete'));
	}

	/**
	 * Describes the base use case for adding an order
	 *
	 * @return	void
	 */
	public function testManagerAdd() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', array(
			'methods' => array(
				'getUspsRates',
			),
			'models' => array(
				'CustomPackageRequest' => array('updateOrderId'),
			),
		));
		$Orders->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue([['@CLASSID' => '1', 'Rate' => '12.69']]));
		$Orders->Order->CustomPackageRequest->expects($this->once())
			->method('updateOrderId')
			->will($this->returnValue(true));
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		));
		$data['Order'] = $this->_getOrderCustomerData($data);

		$orderStorageOptions = array(
			'conditions' => array(
				'OrderStorage.class' => 'ot_custom',
			),
		);
		$orderRepackOptions = array(
			'conditions' => array(
				'OrderRepack.class' => 'ot_custom_1',
			)
		);
		$orderBatteryOptions = array(
			'conditions' => array(
				'OrderBattery.class' => 'ot_custom_2',
			)
		);
		$orderReturnOptions = array(
			'conditions' => array(
				'OrderReturn.class' => 'ot_custom_3',
			)
		);
		$orderMisaddressedOptions = array(
			'conditions' => array(
				'OrderMisaddressed.class' => 'ot_custom_4',
			)
		);
		$orderShipToUSOptions = array(
			'conditions' => array(
				'OrderShipToUS.class' => 'ot_custom_5',
			)
		);

		$orderCountBefore = $Customers->Order->find('count');
		$orderShippingCountBefore = $Customers->Order->OrderShipping->find('count');
		$orderStorageCountBefore = $Customers->Order->OrderStorage->find('count', $orderStorageOptions);
		$orderInsuranceCountBefore = $Customers->Order->OrderInsurance->find('count');
		$orderFeeCountBefore = $Customers->Order->OrderFee->find('count');
		$orderSubtotalCountBefore = $Customers->Order->OrderSubtotal->find('count');
		$orderTotalCountBefore = $Customers->Order->OrderTotal->find('count');
		$orderRepackCountBefore = $Customers->Order->OrderRepack->find('count', $orderRepackOptions);
		$orderBatteryCountBefore = $Customers->Order->OrderBattery->find('count', $orderBatteryOptions);
		$orderReturnCountBefore = $Customers->Order->OrderReturn->find('count', $orderReturnOptions);
		$orderMisaddressedCountBefore = $Customers->Order->OrderMisaddressed->find('count', $orderMisaddressedOptions);
		$orderShipToUSCountBefore = $Customers->Order->OrderShipToUS->find('count', $orderShipToUSOptions);

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$order = $Customers->Order->find('first', array(
			'contain' => array(
				'OrderInsurance',
				'OrderFee',
				'OrderSubtotal',
				'OrderTotal',
				'OrderShipping',
			),
			'conditions' => array(
				'Order.orders_id' => $Customers->Order->getLastInsertId()
			)
		));

		$orderCountAfter = $Customers->Order->find('count');
		$orderShippingCountAfter = $Customers->Order->OrderShipping->find('count');
		$orderStorageCountAfter = $Customers->Order->OrderStorage->find('count', $orderStorageOptions);
		$orderInsuranceCountAfter = $Customers->Order->OrderInsurance->find('count');
		$orderFeeCountAfter = $Customers->Order->OrderFee->find('count');
		$orderSubtotalCountAfter = $Customers->Order->OrderSubtotal->find('count');
		$orderTotalCountAfter = $Customers->Order->OrderTotal->find('count');
		$orderRepackCountAfter = $Customers->Order->OrderRepack->find('count', $orderRepackOptions);
		$orderBatteryCountAfter = $Customers->Order->OrderBattery->find('count', $orderBatteryOptions);
		$orderReturnCountAfter = $Customers->Order->OrderReturn->find('count', $orderReturnOptions);
		$orderMisaddressedCountAfter = $Customers->Order->OrderMisaddressed->find('count', $orderMisaddressedOptions);
		$orderShipToUSCountAfter = $Customers->Order->OrderShipToUS->find('count', $orderShipToUSOptions);

		$this->assertEquals('1.75', $order['OrderInsurance']['value']);
		$this->assertEquals('12.69', $order['OrderShipping']['value']);
		$this->assertEquals('$12.69', $order['OrderShipping']['text']);
		$this->assertEquals('10.95', $order['OrderFee']['value']);
		$this->assertEquals('$10.95', $order['OrderFee']['text']);
		$this->assertEquals('25.39', $order['OrderSubtotal']['value']);
		$this->assertEquals('$25.39', $order['OrderSubtotal']['text']);
		$this->assertEquals('25.39', $order['OrderTotal']['value']);
		$this->assertEquals('<b>$25.39</b>', $order['OrderTotal']['text']);
		$this->assertEquals(($orderCountBefore + 1), $orderCountAfter, 'Should create exactly one Order record. Created: ' . ($orderCountAfter - $orderCountBefore));
		$this->assertEquals(($orderShippingCountBefore + 1), $orderShippingCountAfter, 'Should created exactly one OrderShipping record');
		$this->assertEquals(($orderStorageCountBefore + 1), $orderStorageCountAfter, 'Should created exactly one OrderStorage record');
		$this->assertEquals(($orderInsuranceCountBefore + 1), $orderInsuranceCountAfter, 'Should created exactly one OrderInsurance record');
		$this->assertEquals(($orderFeeCountBefore + 1), $orderFeeCountAfter, 'Should created exactly one OrderInsurance record');
		$this->assertEquals(($orderSubtotalCountBefore + 1), $orderSubtotalCountAfter, 'Should created exactly one OrderSubtotal record');
		$this->assertEquals(($orderTotalCountBefore + 1), $orderTotalCountAfter, 'Should created exactly one OrderTotal record');
		$this->assertEquals(($orderRepackCountBefore + 1), $orderRepackCountAfter, 'Should created exactly one OrderRepack record');
		$this->assertEquals(($orderBatteryCountBefore + 1), $orderBatteryCountAfter, 'Should created exactly one OrderBattery record');
		$this->assertEquals(($orderReturnCountBefore + 1), $orderReturnCountAfter, 'Should created exactly one OrderReturn record');
		$this->assertEquals(($orderMisaddressedCountBefore + 1), $orderMisaddressedCountAfter, 'Should created exactly one OrderMisaddressed record');
		$this->assertEquals(($orderShipToUSCountBefore + 1), $orderShipToUSCountAfter, 'Should created exactly one OrderShipToUS record');
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i/charge', $this->headers['Location']);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testManagerAddGET() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));

		$orderCountBefore = $Customers->Order->find('count');

		$this->testAction($url, array('method' => 'get'));

		$orderCountAfter = $Customers->Order->find('count');

		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or remove Order records');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
		$this->assertArrayHasKey('customers', $this->vars);
		$this->assertArrayHasKey('orderStatuses', $this->vars);
		$this->assertArrayHasKey('requests', $this->vars);
	}

	/**
	 * Order defaults will be set based on customer records.
	 *
	 * @dataProvider providesManagerAddGetCustomerDefaultsGetSet
	 * @return	void
	 */
	public function testManagerAddGetCustomerDefaultsSet($customerId, $defaults) {
		$userId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));

		$result = $this->testAction($url, array('method' => 'get'));
		$data = $Orders->request->data['Order'];

		$this->assertSame($defaults['mail_class'], $data['mail_class']);
		$this->assertSame($defaults['insurance_coverage'], $data['insurance_coverage']);
	}

	public function providesManagerAddGetCustomerDefaultsGetSet() {
		return [
			[1, [
				'mail_class' => 'priority',
				'insurance_coverage' => '50.00',
			]],
			[4, [
				'mail_class' => 'parcel',
				'insurance_coverage' => '100.00',
			]],
			[5, [
				'mail_class' => null,
				'insurance_coverage' => '50.00',
			]],
		];
	}

	/**
	 * Describes creation failure
	 *
	 * @return	void
	 */
	public function testManagerAddSaveFailsWithCustomersIdSet() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$Customers->Order = $this->getMockForModel('Order', array('save'));
		$Customers->Order->expects($this->once())
			->method('save')
			->will($this->returnValue(false));
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		));

		$orderCountBefore = $Customers->Order->find('count');

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$orderCountAfter = $Customers->Order->find('count');

		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or remove Order records');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
		$this->assertArrayHasKey('customers', $this->vars);
		$this->assertArrayHasKey('orderStatuses', $this->vars);
		$this->assertArrayHasKey('customersAddresses', $this->vars);
	}

	/**
	 * Test that employees can vew orders and various vars and view elements exist.
	 *
	 * @return	void
	 */
	public function testEmployeeViewGet() {
		$userId = 2;
		$orderId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
			'employee' => true,
		));

		$html = $this->testAction($url, array('method' => 'get', 'return' => 'view'));

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('ordersStatuses', $this->vars);
		$this->assertEquals(5, count($this->vars['ordersStatuses']), 'Should set all statuses as options.');
		$this->assertArrayHasKey('currentStatusHistory', $this->vars);
		$this->assertInternalType('array', $this->vars['currentStatusHistory']);
		$this->assertArrayHasKey('statusHistories', $this->vars);
		$this->assertInternalType('array', $this->vars['statusHistories']);
		$this->assertTrue(!empty($this->vars['statusHistories']), 'No OrderStatusHistories found.');
		$this->assertArrayHasKey('OrderStatus', $this->vars['statusHistories'][0]);
		$this->assertArrayHasKey('orderCharges', $this->vars);
		$this->assertInternalType('array', $this->vars['orderCharges']);
		$this->assertContains('NonMachinable', $html);
		$this->assertContains('Oversize', $html);
		$this->assertContains('Balloon Rate', $html);
	}

	/**
	 * testEmployeeUpdateStatus method
	 *
	 * @return	void
	 */
	public function testEmployeeUpdateStatus() {
		$orderId = 1;
		$Orders = $this->generate('Orders', array(
			'methods' => array(
				'manager_update_status',
			),
		));
		$Orders->expects($this->once())
			->method('manager_update_status');

		$Orders->employee_update_status($orderId);
	}

	/**
	 * testManagerUpdateStatus method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatus() {
		$userId = 1;
		$orderId = 1;

		$Orders = $this->setupManagerAuth($userId);
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newOrderStatusId = '4';
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'orders_status' => $newOrderStatusId,
				'status_history_comments' => $newComments,
				'notify_customer' => 1,
			)
		);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$oSHAfter = $Orders->Order->OrderStatusHistory->read(null, $Orders->Order->OrderStatusHistory->getLastInsertId());
		$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

		$this->assertEquals($newOrderStatusId, $orderAfter['Order']['orders_status'], 'Status ID was not updated.');
		$this->assertEquals(($oSHCountBefore+1), $oSHCountAfter, 'Should create exactly one OrderStatusHistory record.');
		$this->assertEquals($newComments, $oSHAfter['OrderStatusHistory']['comments'], 'OrderStatusHistory comments were not updated.');
		$this->assertStringEndsWith('/orders/' . $orderId, $this->headers['Location']);
	}

	/**
	 * testManagerUpdateStatus method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatusWhenNewStatusIsAwaitingPayment() {
		$userId = 1;
		$orderId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newOrderStatusId = '2';
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'orders_status' => $newOrderStatusId,
				'status_history_comments' => $newComments,
				'notify_customer' => 1,
			)
		);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$oSHAfter = $Orders->Order->OrderStatusHistory->read(null, $Orders->Order->OrderStatusHistory->getLastInsertId());
		$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

		$this->assertEquals($newOrderStatusId, $orderAfter['Order']['orders_status'], 'Status ID was not updated.');
		$this->assertEquals(($oSHCountBefore+1), $oSHCountAfter, 'Should create exactly one OrderStatusHistory record.');
		$this->assertEquals($newComments, $oSHAfter['OrderStatusHistory']['comments'], 'OrderStatusHistory comments were not updated.');
		$this->assertStringEndsWith('/orders/' . $orderId, $this->headers['Location']);
	}

	/**
	 * testManagerUpdateStatus method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatusWhenNewStatusIsShipped() {
		$userId = 1;
		$orderId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newOrderStatusId = '3';
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'orders_status' => $newOrderStatusId,
				'status_history_comments' => $newComments,
				'usps_track_num' => 'asdasd12313123',
				'notify_customer' => 1,
			)
		);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$oSHAfter = $Orders->Order->OrderStatusHistory->read(null, $Orders->Order->OrderStatusHistory->getLastInsertId());
		$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

		$this->assertEquals($newOrderStatusId, $orderAfter['Order']['orders_status'], 'Status ID was not updated.');
		$this->assertEquals(($oSHCountBefore+1), $oSHCountAfter, 'Should create exactly one OrderStatusHistory record.');
		$this->assertEquals($newComments, $oSHAfter['OrderStatusHistory']['comments'], 'OrderStatusHistory comments were not updated.');
		$this->assertStringEndsWith('/manager', $this->headers['Location']);
	}

	/**
	 * testManagerUpdateStatusOrderNotExists method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatusOrderNotExists() {
		$userId = 1;
		$orderId = 9999;
		$email = $this->getMockBuilder('AppEmail')
			->disableOriginalConstructor()
			->getMock();
		$email->expects($this->never())
			->method('sendStatusUpdate');
		$Orders = $this->setupManagerAuth($userId);
		$Orders->expects($this->never())
			->method('emailFactory')
			->will($this->returnValue($email));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newOrderStatusId = 4;
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'orders_status' => $newOrderStatusId,
				'status_history_comments' => $newComments,
				'notify_customer' => 1,
			)
		);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->setExpectedException('NotFoundException');

		try {
			$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));
		} catch (Exception $e) {
			$orderAfter = $Orders->Order->findByOrdersId($orderId);
			$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

			$this->assertEmpty($orderBefore, 'Order shoud not be found.');
			$this->assertEquals($oSHCountBefore, $oSHCountAfter, 'Should not create or remove any OrderStatusHistory records.');
			$this->assertEmpty($orderAfter, 'Order shoud not be created.');
			throw $e;
		}

	}

	/**
	 * testManagerUpdateStatusMissingOrderStatus method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatusMissingOrderStatus() {
		$userId = 1;
		$orderId = 1;
		$referer = '/manager/update-status';
		$email = $this->getMockBuilder('AppEmail')
			->disableOriginalConstructor()
			->getMock();
		$email->expects($this->never())
			->method('sendStatusUpdate')
			->will($this->returnValue(true));
		$Orders = $this->setupManagerAuth($userId);
		$Orders->expects($this->never())
			->method('emailFactory')
			->will($this->returnValue($email));
		$Orders->expects($this->any())
			->method('referer')
			->will($this->returnValue($referer));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'status_history_comments' => $newComments,
				'notify_customer' => 1,
			)
		);
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$oSHAfter = $Orders->Order->OrderStatusHistory->read(null, $Orders->Order->OrderStatusHistory->getLastInsertId());
		$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

		$this->assertEquals($oSHCountBefore, $oSHCountAfter, 'Should not create or remove any OrderStatusHistory records.');
		$this->assertNotEquals($newComments, $oSHAfter['OrderStatusHistory']['comments'], 'OrderStatusHistory comments should not be updated.');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere');
		$this->assertStringEndsWith($referer, $this->headers['Location'], 'Should redirect to referer');
	}

	/**
	 * testManagerUpdateStatusMarkAsFails method
	 *
	 * @return	void
	 */
	public function testManagerUpdateStatusMarkAsFails() {
		$userId = 1;
		$orderId = 1;
		$referer = '/manager/update-status';
		$email = $this->getMockBuilder('AppEmail')
			->disableOriginalConstructor()
			->getMock();
		$email->expects($this->never())
			->method('sendStatusUpdate')
			->will($this->returnValue(true));
		$Orders = $this->setupManagerAuth($userId);
		$Orders->expects($this->never())
			->method('emailFactory')
			->will($this->returnValue($email));
		$Orders->expects($this->any())
			->method('referer')
			->will($this->returnValue($referer));
		$url = array(
			'controller' => 'orders',
			'action' => 'update_status',
			'id' => $orderId,
			'manager' => true,
		);
		$newOrderStatusId = 4;
		$newComments = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed mollis elit. Maecenas varius metus sit amet dolor consectetur eleifend. Vivamus erat massa, placerat non nisl in, finibus fermentum augue. Duis eu magna scelerisque, maximus metus posuere.';
		$data = array(
			'Order' => array(
				'orders_status' => $newOrderStatusId,
				'status_history_comments' => $newComments,
				'notify_customer' => 1,
			)
		);
		$Orders->Order = $this->getMockForModel('Order', array('markAs'));
		$Orders->Order->expects($this->once())
			->method('markAs')
			->will($this->returnValue(false));
		$orderBefore = $Orders->Order->findByOrdersId($orderId);
		$oSHCountBefore = $Orders->Order->OrderStatusHistory->find('count');

		$this->testAction(Router::url($url), array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->findByOrdersId($orderId);
		$oSHAfter = $Orders->Order->OrderStatusHistory->read(null, $Orders->Order->OrderStatusHistory->getLastInsertId());
		$oSHCountAfter = $Orders->Order->OrderStatusHistory->find('count');

		$this->assertNotEquals($newOrderStatusId, $orderAfter['Order']['orders_status'], 'Status ID should not be updated.');
		$this->assertEquals($oSHCountBefore, $oSHCountAfter, 'Should not create or remove any OrderStatusHistory records.');
		$this->assertNotEquals($newComments, $oSHAfter['OrderStatusHistory']['comments'], 'OrderStatusHistory comments should not be updated.');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere');
		$this->assertStringEndsWith($referer, $this->headers['Location'], 'Should redirect to referer');
	}

	/**
	 * Confirms that the delete method deletes the order and it's associated
	 * records
	 *
	 * @return	void
	 */
	public function testManagerDelete() {
		$userId = 1;
		$orderId = 1;

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'delete',
			'manager' => true,
			$orderId
		));

		$this->setupManagerAuth($userId);

		$Order = ClassRegistry::init('Order');
		$options = array(
			'conditions' => array(
				'Order.orders_id' => $orderId,
			),
			'contain' => array(
				'OrderTotal',
				'OrderStatusHistory',
			),
		);
		$optionsAssociated = array(
			'conditions' => array(
				'orders_id' => $orderId,
			),
		);
		$before = $Order->find('all', $options);
		$this->assertArrayHasKey('Order', $before[0]);
		$this->assertEquals($orderId, $before[0]['Order']['orders_id']);
		$this->assertArrayHasKey('OrderTotal', $before[0]);
		$this->assertNotEmpty($before[0]['OrderTotal']);
		$this->assertArrayHasKey('OrderStatusHistory', $before[0]);
		$this->assertNotEmpty($before[0]['OrderStatusHistory']);

		$this->testAction($url, array('method' => 'post'));

		$after = $Order->find('all', $options);
		$this->assertEmpty($after);
		$this->assertEmpty($Order->OrderTotal->find('all', $optionsAssociated));
		$this->assertEmpty($Order->OrderStatusHistory->find('all', $optionsAssociated));
		$this->assertArrayHasKey('Location', $this->headers, 'Sucessfull delete should redirect somewhere.');
	}

	/**
	 *
	 * @return	void
	 */
	public function testManagerMarkAsShipped() {
		$userId = 1;
		$orderId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Orders->expects($this->any())
			->method('referer')
			->will($this->returnValue('/anything'));
		$expectedStatusId = '3';
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			'id' => $orderId,
			'manager' => true,
		));
		$Order = ClassRegistry::init('Order');
		$this->testAction($url, array('method' => 'post'));

		$after = $Order->findByOrdersId($orderId);

		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Status was not updated to "Shipped (3)"');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere');
	}

	/**
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testManagerMarkAsShippedGET() {
		$userId = 1;
		$orderId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			'id' => $orderId,
			'manager' => true,
		));
		$this->testAction($url, array('method' => 'get'));
	}

	/**
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerMarkAsShippedOrderNotExists() {
		$userId = 1;
		$orderId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			'id' => $orderId,
			'manager' => true,
		));
		$Orders->Order = $this->getMockForModel('Order', array('exists'));
		$Orders->Order->expects($this->once())
			->method('exists')
			->will($this->returnValue(false));

		$this->testAction($url, array('method' => 'post'));
	}

	public function testManagerMarkAsShippedModelFails() {
		$userId = 1;
		$orderId = 1;
		$email = $this->getMockBuilder('AppEmail')
			->disableOriginalConstructor()
			->getMock();
		$email->expects($this->never())
			->method('sendShipped')
			->will($this->returnValue(true));
		$Orders = $this->setupManagerAuth($userId);
		$Orders->expects($this->never())
			->method('emailFactory')
			->will($this->returnValue($email));
		$Orders->expects($this->any())
			->method('referer')
			->will($this->returnValue('/anything'));
		$expectedStatusId = '3';
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			'id' => $orderId,
			'manager' => true,
		));
		$Orders->Order = $this->getMockForModel('Order', array('markAsShipped'));
		$Orders->Order->expects($this->once())
			->method('markAsShipped')
			->will($this->returnValue(false));

		$this->testAction($url, array('method' => 'post'));

		$after = $Orders->Order->findByOrdersId($orderId);

		$this->assertNotEquals($expectedStatusId, $after['Order']['orders_status'], 'Status should not be expected value "Shipped (3)"');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
	}

	public function testManagerMarkAsShippedModelRefererNotRoot() {
		Configure::write('Security.admin.ips', false);
		$userId = 1;
		$orderId = 1;
		$refererString = '/manager/orders/' . $orderId;
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login')
			),
			'methods' => array('referer', 'emailFactory'),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$user = ClassRegistry::init('Admin')->find('first', array(
			'contain' => array(),
			'conditions' => array('id' => $userId),
		));
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$expectedStatusId = '3';
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			'id' => $orderId,
			'manager' => true,
		));
		$Orders->Order = ClassRegistry::init('Order');

		$this->testAction($url, array('method' => 'post'));

		$after = $Orders->Order->findByOrdersId($orderId);

		$this->assertEquals($expectedStatusId, $after['Order']['orders_status'], 'Status should be "Shipped (3)"');
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect somewhere.');
		$this->assertStringEndsWith($refererString, $this->headers['Location'], 'Should redirect to referer.');
	}

	/**
	 *
	 */
	public function testManagerChargeSuccess() {
		$orderId = 8;
		$userId = 1;
		Configure::write('Security.admin.ips', false);

		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge')
			),
			'methods' => array(
				'emailFactory',
				'checkIfOrderCanBeCharged',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));

		$orderBefore = $Orders->Order->find('first', array(
			'conditions' => array('Order.orders_id' => $orderId)
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
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));

		$data = array(
			'submit' => 'charge',
			'Order' => array('orders_id' => $orderId),
			'OrderShipping' => array('value' => '1.00'),
			'OrderStorage' => array('value' => '2.00'),
			'OrderInsurance' => array('value' => '3.00'),
			'OrderFee' => array('value' => '4.00'),
			'OrderRepack' => array('value' => '5.00'),
			'OrderBattery' => array('value' => '5.00'),
			'OrderReturn' => array('value' => '2.00'),
			'OrderMisaddressed' => array('value' => '1.00'),
			'OrderShipToUS' => array('value' => '2.00'),
		);
		$historyBefore = $Orders->Order->OrderStatusHistory->find('count', ['conditions' => ['orders_id' => $orderId]]);

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$historyAfter = $Orders->Order->OrderStatusHistory->find('count', ['conditions' => ['orders_id' => $orderId]]);
		$historyLast = $Orders->Order->OrderStatusHistory->find('first', [
			'conditions' => ['orders_id' => $orderId],
			'order' => 'orders_status_history_id DESC',
		]);
		$orderAfter = $Orders->Order->find('first', array(
			'contain' => array(
				'OrderInsurance',
				'OrderFee',
				'OrderRepack',
				'OrderBattery',
				'OrderMisaddressed',
				'OrderReturn',
				'OrderShipToUS',
				'OrderSubtotal',
				'OrderTotal',
			),
			'conditions' => array(
				'Order.orders_id' => $orderId
			)
		));

		$this->assertEquals('4.00', $orderAfter['OrderFee']['value']);
		$this->assertEquals('5.00', $orderAfter['OrderRepack']['value']);
		$this->assertEquals('5.00', $orderAfter['OrderBattery']['value']);
		$this->assertEquals('2.00', $orderAfter['OrderReturn']['value']);
		$this->assertEquals('1.00', $orderAfter['OrderMisaddressed']['value']);
		$this->assertEquals('2.00', $orderAfter['OrderShipToUS']['value']);
		$this->assertEquals('25.00', $orderAfter['OrderSubtotal']['value']);
		$this->assertEquals('$25.00', $orderAfter['OrderSubtotal']['text']);
		$this->assertEquals('25.00', $orderAfter['OrderTotal']['value']);
		$this->assertEquals('<b>$25.00</b>', $orderAfter['OrderTotal']['text']);
		$this->assertNotEquals($orderBefore['Order']['orders_status'], $orderAfter['Order']['orders_status']);
		$this->assertEquals('4', $orderAfter['Order']['orders_status']);
		$this->assertEquals('4', $orderAfter['Order']['billing_status']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i', $this->headers['Location']);
		$this->assertArrayNotHasKey('order', $this->vars);
		$this->assertSame($historyBefore+2, $historyAfter, 'Two new OrderStatusHistory records should have been added.');
		$this->assertSame('1', $historyLast['OrderStatusHistory']['customer_notified'], 'Field customer_notified should have been set to 1.');
	}

	public function testManagerChargeWithMissingFee() {
		$orderId = 8;
		$userId = 1;
		Configure::write('Security.admin.ips', false);

		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge')
			),
			'methods' => array(
				'emailFactory',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));

		$orderBefore = $Orders->Order->find('first', array(
			'conditions' => array('Order.orders_id' => $orderId)
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
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));
		$data = array(
			'submit' => 'charge',
			'OrderShipping' => array('value' => '1.00'),
			'OrderStorage' => array('value' => '1.00'),
			'OrderInsurance' => array('value' => '1.00'),
			'OrderFee' => array('value' => '5.00'),
		);

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$orderAfter = $Orders->Order->find('first', array(
			'contain' => array('OrderInsurance', 'OrderFee', 'OrderSubtotal', 'OrderTotal'),
			'conditions' => array('Order.orders_id' => $orderId)
		));

		$this->assertEquals('5.00', $orderAfter['OrderFee']['value']);
		$this->assertEquals('8', $orderAfter['OrderSubtotal']['value']);
		$this->assertEquals('8', $orderAfter['OrderTotal']['value']);
		$this->assertNotEquals($orderBefore['Order']['orders_status'], $orderAfter['Order']['orders_status']);
		$this->assertEquals('4', $orderAfter['Order']['orders_status']);
		$this->assertEquals('4', $orderAfter['Order']['billing_status']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i', $this->headers['Location']);
		$this->assertArrayNotHasKey('order', $this->vars);
	}
	/**
	 *
	 */
	public function testManagerChargePaymentFails() {
		$orderId = 1;
		$userId = 1;
		Configure::write('Security.admin.ips', false);
		Configure::write('ZebraLabel.auto', false);

		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge')
			),
			'methods' => array(
				'emailFactory',
				'printZebraLabel',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
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
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(false));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Orders
			->expects($this->never())
			->method('printZebraLabel');

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));
		$data = array(
			'submit' => 'charge',
			'OrderShipping' => array('orders_total_id' => 1, 'value' => '1.00'),
			'OrderStorage' => array('orders_total_id' => 2, 'value' => '1.00'),
			'OrderInsurance' => array('orders_total_id' => 3, 'value' => '1.00'),
			'OrderSubtotal' => array('orders_total_id' => 4, 'value' => '1.00'),
		);
		$historyBefore = $Orders->Order->OrderStatusHistory->find('count', ['conditions' => ['orders_id' => $orderId]]);

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$historyAfter = $Orders->Order->OrderStatusHistory->find('count', ['conditions' => ['orders_id' => $orderId]]);
		$historyLast = $Orders->Order->OrderStatusHistory->find('first', [
			'conditions' => ['orders_id' => $orderId],
			'order' => 'orders_status_history_id DESC',
		]);

		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i', $this->headers['Location']);
		$this->assertArrayNotHasKey('order', $this->vars);
		$this->assertSame($historyBefore+3, $historyAfter, 'Three new OrderStatusHistory records should have been added.');
		$this->assertSame(
			'1',
			$historyLast['OrderStatusHistory']['customer_notified'],
			'Field customer_notified should have been set to 1.'
		);
		$this->assertSame(
			'charge failed, email sent',
			$historyLast['OrderStatusHistory']['comments'],
			'Field comments should have a message.'
		);
	}

	/**
	 *
	 */
	public function testManagerChargeGETNormal() {
		$orderId = 1;
		$Orders = $this->setupManagerAuth(1);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('return' => 'view', 'method' => 'get'));

		$this->assertArrayNotHasKey('Location', $this->headers);
		$this->assertArrayHasKey('order', $this->vars);
		$this->assertInternalType('array', $this->vars['order']);
		$this->assertArrayHasKey('Order', $this->vars['order']);
		$this->assertArrayHasKey('Customer', $this->vars['order']);
		$this->assertInternalType('array', $this->vars['order']['Customer']);
		$this->assertArrayHasKey('customers_default_address_id', $this->vars['order']['Customer']);
		$this->assertArrayHasKey('OrderShipping', $this->vars['order']);
		$this->assertArrayHasKey('OrderStorage', $this->vars['order']);
		$this->assertArrayHasKey('OrderInsurance', $this->vars['order']);
		$this->assertArrayHasKey('OrderFee', $this->vars['order']);
		$this->assertEquals(Configure::read('FeeByWeight.0'), $this->vars['order']['OrderFee']['value']);
		$this->assertArrayHasKey('OrderSubtotal', $this->vars['order']);
		$this->assertArrayHasKey('OrderTotal', $this->vars['order']);
		$this->assertArrayHasKey('invoiceCustomer', $this->vars);
		$this->assertArrayHasKey('allowCharge', $this->vars);
		$this->assertArrayHasKey('feeRates', $this->vars);
		$this->assertArrayHasKey('allow', $this->vars['allowCharge']);
	}

	public function testManagerChargeGETSetsMissingFee() {
		$returnValue = '11.95';
		$OrderFee = $this->getMockForModel('OrderFee', ['getFee']);
		$OrderFee->expects($this->once())
			->method('getFee')
			->will($this->returnValue($returnValue));

		$orderId = 2;
		$Orders = $this->setupManagerAuth(2);
		$Orders->Order->OrderFee = $OrderFee;
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));

		$this->testAction($url, array('return' => 'view', 'method' => 'get'));

		$this->assertArrayHasKey('OrderFee', $this->vars['order']);
		$this->assertEquals($returnValue, $Orders->request->data['OrderFee']['value']);
	}
	/**
	 *
	 */
	public function testApiView() {
		$Orders = $this->setupApiAuth();
		$id = 1;

		$contents = $this->testAction('/api/orders/' . $id, array('return' => 'contents', 'method' => 'get'));

		$decoded = json_decode($contents, true);
		$this->assertTrue($decoded !== null, 'Failed to decode response as JSON');
		$this->assertArrayHasKey('data', $decoded);
		$this->assertArrayHasKey('id', $decoded['data']);
		$this->assertTrue($decoded['data']['id'] == $id);
		$this->assertArrayHasKey('orders_id', $decoded['data']['attributes']);
		$this->assertTrue($decoded['data']['attributes']['orders_id'] == $id);
	}

	/**
	 *
	 */
	public function testApiViewOrderNotExists() {
		$Orders = $this->setupApiAuth();
		$id = 999;
		$this->setExpectedException('NotFoundException');

		$this->testAction('/api/orders/' . $id, array('return' => 'contents', 'method' => 'get'));
	}

	/**
	 * Confirm the index method throws an exception if the request method
	 * isn't "get".
	 *
	 * @expectedException MethodNotAllowedException
	 *
	 * @return	void
	 */
	public function testIndexPut() {
		$customerId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'index',
		));

		$this->testAction($url, array('method' => 'put'));
	}

	/**
	 * Confirm the index method sets expected variables
	 *
	 * @return	void
	 */
	public function testIndex() {
		$customerId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'index',
		));

		$this->testAction($url, array('method' => 'get'));

		$this->assertArrayHasKey('orders', $this->vars);
		$this->assertArrayHasKey('customRequests', $this->vars);
		$this->assertArrayHasKey('OrderStatus', $this->vars['orders'][0]);
		$this->assertArrayHasKey('Customer', $this->vars['orders'][1]);
		$this->assertEquals($customerId, $this->vars['orders'][0]['Order']['customers_id']);
		$this->assertArrayHasKey('OrderTotal', $this->vars['orders'][0], '$orders should contain at least one OrderTotal.');
	}

	/**
	 * Confirm the view method throws a not found exception if the order
	 * can't be found.
	 *
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testViewInvalidId() {
		$customerId = 1;
		$orderId = 99999999999;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));

		$this->testAction($url, array('method' => 'get'));
	}

	/**
	 * Confirm that the employee_search method returns expected variables from
	 * calling the manager_search method.
	 *
	 * @return	void
	 */
	public function testEmployeeSearch() {
		$customerId = 1;
		$this->setupAuth($customerId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'employee' => true,
		));

		$this->testAction($url, array('method' => 'get'));
		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('customRequests', $this->vars);
	}

	/**
	 * Confirm that manager search changes results when supplied with query
	 * strings.
	 *
	 * @return	void
	 */
	public function testManagerSearchWithQueryStrings() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$data = array(
			'from_the_past' => '-10 years',
			'showStatus' => '1',
		);

		$this->testAction($url, array(
			'method' => 'get',
			'data' => $data,
		));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('customRequests', $this->vars);
		$this->assertArrayHasKey('date_purchased', $this->vars['results'][0]['Order']);

		$datePurchased = $this->vars['results'][0]['Order']['date_purchased'];
		$pastDate = date_create($data['from_the_past'])->format('Y-m-d H:i:s');
		$this->assertLessThan($datePurchased, $pastDate);

		$this->assertEquals($data['showStatus'], $this->vars['results'][0]['Order']['orders_status']);
	}

	/**
	 * Confirm that the `results` key is empty with no search results
	 *
	 * @return	void
	 */
	public function testManagerSearchResultsFalse() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		));

		$data = array(
			'q' => 'foo',
		);

		$this->testAction($url, array(
			'method' => 'get',
			'data' => $data,
		));

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertEmpty($this->vars['results']);
	}

	/**
	 * Confirm that the `results` key is false when getConditions() returns an
	 * empty value.
	 *
	 * @return	void
	 */
	public function testManagerSearchNoConditions() {
		$userId = 1;
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth' => ['user', 'login']
			],
			'methods' => ['getConditions'],
			'models' => [
				'Order' => ['sendStatusUpdateEmail'],
			],
		]);

		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders
			->expects($this->once())
			->method('getConditions')
			->will($this->returnValue([]));

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'search',
			'manager' => true,
		]);

		$data = [
			'q' => 'foo',
		];

		$this->testAction($url, [
			'method' => 'get',
			'data' => $data,
		]);

		$this->assertArrayHasKey('search', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey('customRequests', $this->vars);
		$this->assertFalse($this->vars['results']);
	}

	/**
	 * Confirm the manager_add method throws a not found exception if the customer
	 * can't be found.
	 *
	 * @expectedException NotFoundException
	 *
	 * @return	void
	 */
	public function testManagerAddInvalidCustomer() {
		$userId = 1;
		$customerId = 999999999;
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'weight_oz' => 2,
		));

		$this->testAction($url, array('method' => 'post', 'data' => $data));
	}

	/**
	 * Confirm that an order can be saved when additional addresses are used
	 * (customer address, delivery address, billing address) and insurance is
	 * declined.
	 *
	 * @return	void
	 */
	public function testManagerAddWithCustomersAddressIdSet() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', array(
			'methods' => array(
				'addPostage',
			),
			'models' => array(
				'CustomPackageRequest' => array('updateOrderId'),
			),
		));
		$Orders->expects($this->once())
			->method('addPostage')
			->will($this->returnValue(array()));
		$Orders->Order->CustomPackageRequest->expects($this->once())
			->method('updateOrderId')
			->will($this->returnValue(true));
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'customers_address_id' => 1,
			'delivery_address_id' => 1,
			'billing_address_id' => 1,
			'insurance_coverage' => 200,
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		));

		$orderCountBefore = $Customers->Order->find('count');
		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$orderCountAfter = $Customers->Order->find('count');
		$this->assertNotEquals($orderCountBefore, $orderCountAfter);
	}

	/**
	 * Confirm that _marshalAddressTo throws an exception if it's passed an
	 * invalid type.
	 *
	 * @expectedException InvalidArgumentException
	 *
	 * @return	void
	 */
	public function testMarshalAddressToWithInvalidType() {
		$Orders = $this->generate('TestOrders');
		$Orders->_marshalAddressTo('foo', 'bar');
	}

	/**
	 * Confirm that _marshalAddressTo will set `delivery_company` to the first
	 * 32 characters of full name (`entry_firstname` + `entry_lastname`) if
	 * `entry_company` is empty.
	 *
	 * @return	void
	 */
	public function testMarshalAddressToWithNoCompany() {
		$type = 'delivery';
		$address = array(
			'Address' => array(
				'entry_company' => '',
				'entry_firstname' => 'Lorem Jane Ipsum',
				'entry_lastname' => 'Dolar Rodriguez Felicidad',
				'entry_street_address' => 'Lorem ipsum dolor sit amet',
				'entry_suburb' => 'Lorem ipsum dolor sit amet',
				'entry_postcode' => 'Lorem ip',
				'entry_city' => 'Lorem ipsum dolor sit amet',
			),
			'Zone' => array(
				'zone_id' => '1',
				'zone_country_id' => '223',
				'zone_code' => 'AL',
				'zone_name' => 'Alabama'
			),
			'Country' => array(
				'countries_id' => '163',
				'countries_name' => 'Costa Rica',
				'countries_iso_code_2' => 'CR',
				'countries_iso_code_3' => 'CRI',
				'address_format_id' => '1'
			)
		);

		$name = $address['Address']['entry_firstname'] . ' ' . $address['Address']['entry_lastname'];
		$Orders = $this->generate('TestOrders');
		$result = $Orders->_marshalAddressTo($type, $address);
		$this->assertEquals(substr($name, 0, 32), $result['delivery_company']);
	}

	/**
	 * Confirm the API throws an exception if the order $id doesn't exist
	 *
	 * @return void
	 */
	public function testApiStatusOrderNotExists() {
		$Orders = $this->setupApiAuth();
		$id = 999;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'id' => "$id",
				'attributes' => array()
			)
		);
		$this->setExpectedException('NotFoundException', "Order not found");

		$this->testAction("/api/orders/changestatus", array(
			'method' => 'patch',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm that the API throws an exception if _changeOrderStatus()
	 * returns false.
	 *
	 * @return	void
	 */
	public function testApiStatusFails() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'id' => "$id",
				'attributes' => array()
			)
		);
		$Orders->expects($this->once())
			->method('_changeOrderStatus')
			->will($this->returnValue(false));

		$this->setExpectedException('BadRequestException', 'Unable to update order status.');

		$this->testAction("/api/orders/changestatus", array(
			'method' => 'patch',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm that a successful API post results in an HTTP 204
	 *
	 * @return void
	 */
	public function testApiStatus() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'id' => "$id",
				'attributes' => array(
					'orders_status' => '3',
					'status_history_comments' => '',
					'usps_track_num' => '123ABC',
					'notify_customer' =>  '0'
				)
			)
		);
		$Orders->expects($this->once())
			->method('_changeOrderStatus')
			->will($this->returnValue(true));
		$contents = $this->testAction("/api/orders/changestatus", array(
			'return' => 'contents',
			'method' => 'patch',
			'data' => json_encode($data),
		));
		$this->assertEquals(204, $Orders->response->statusCode());
	}

	/**
	 * Confirm the API throws an exception if the method is GET
	 *
	 * @return void
	 */
	public function testApiAddInvalidMethod() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$this->setExpectedException('MissingActionException');

		$this->testAction("/api/orders/{$id}/add", array('method' => 'get'));
	}

	/**
	 * Confirm the API throws an exception if the customers_id doesn't exist
	 *
	 * @return void
	 */
	public function testApiAddInvalidCustomerCustomersId() {
		$Orders = $this->setupApiAuth();
		$id = 999;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'carrier' => 'ups',
				),
			),
		);
		$this->setExpectedException('NotFoundException', "Customer $id not found");

		$this->testAction("/api/orders/{$id}/add", array(
			'method' => 'post',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm the API throws an exception if the customer billing_id doesn't exist
	 *
	 * @return void
	 */
	public function testApiAddInvalidCustomerBillingId() {
		$Orders = $this->setupApiAuth();
		$id = 'BT999';
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'carrier' => 'ups',
				),
			),
		);
		$this->setExpectedException('NotFoundException', "Customer $id not found");

		$this->testAction("/api/orders/{$id}/add", array(
			'method' => 'post',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm the API throws an exception if the data $type doesn't match the
	 * model.
	 *
	 * @return void
	 */
	public function testApiAddInvalidType() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'foo',
			),
		);
		$this->setExpectedException('BadRequestException', 'Invalid model');

		$this->testAction("/api/orders/{$id}/add", array(
			'method' => 'post',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm an order can be saved via API and returns a JSON response
	 * containing the newly saved order.
	 *
	 * @return void
	 */
	public function testApiAddSuccess() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login')
			),
			'methods' => array(
				'addPostage',
			),
			'models' => array(
				'CustomPackageRequest' => array('updateOrderId'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		$Orders->expects($this->once())
			->method('addPostage')
			->will($this->returnValue(true));
		$Orders->Order->CustomPackageRequest->expects($this->once())
			->method('updateOrderId')
			->will($this->returnValue(true));

		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'carrier' => 'ups',
					'inbound_tracking_number' => '1234567XYZ',
					'customers_address_id' => '1',
					'delivery_address_id' => '1',
					'billing_address_id' => '1',
					'width' => '2',
					'length' => '4',
					'depth' => '6',
					'weight_oz' => '8',
					'mail_class' => 'priority',
					'package_type' => 'rectparcel',
					'customs_description' => 'Testing',
				),
			),
		);

		$Order = ClassRegistry::init('Order');
		$before = $Order->find('count');

		$result = $this->testAction("/api/orders/{$id}/add", array(
			'method' => 'post',
			'data' => json_encode($data),
			'return' => 'contents',
		));

		$after = $Order->find('count');
		$order = $Order->findByOrdersId($Order->id);
		$this->assertNotEquals($before, $after);
		$this->assertEquals($data['data']['attributes']['inbound_tracking_number'], $order['Order']['ups_track_num']);

		$result = json_decode($result, true);
		$this->assertArrayHasKey('data', $result);
		$this->assertArrayHasKey('id', $result['data']);
		$this->assertArrayHasKey('orders_id', $result['data']['attributes']);
		$this->assertEquals($order['Order']['orders_id'], $result['data']['id']);
		$this->assertEquals($order['Order']['ups_track_num'], $result['data']['attributes']['ups_track_num']);
	}

	/**
	 * Confirm an order with missing data does not save via the API.
	 *
	 * @return void
	 */
	public function testApiAddFailure() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'carrier' => '',
					'inbound_tracking_number' => '1234567XYZ',
					'customers_address_id' => '1',
					'delivery_address_id' => '1',
					'billing_address_id' => '1',
					'width' => '2',
					'length' => '4',
					'depth' => '6',
					'weight_oz' => '8',
					'mail_class' => 'priority',
					'package_type' => 'rectparcel',
					'customs_description' => 'Testing',
				),
			),
		);

		$this->setExpectedException('BadRequestException', 'Error: You must select a carrier.');

		$Order = ClassRegistry::init('Order');
		$before = $Order->find('count');

		$this->testAction("/api/orders/{$id}/add", array(
			'method' => 'post',
			'data' => json_encode($data)
		));

		$after = $Order->find('count');
		$this->assertEquals($before, $after);
	}

	/**
	 * Confirm that _setAddressesForOrder() sets address data correctly.
	 *
	 * @return void
	 */
	public function testSetAddressesForOrderDefault() {
		$data = array(
			'Order' => array(
				'customers_address_id' => 1,
				'delivery_address_id' => 4,
				'billing_address_id' => 5,
			),
		);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_setAddressesForOrder($data, 1);
		$this->assertContains('Lorem ipsum', $result['Order']['customers_name']);
		$this->assertContains('First Last', $result['Order']['billing_company']);
		$this->assertEquals('Costa Rica', $result['Order']['delivery_country']);
	}

	/**
	 * Confirm that _setAddressesForOrder() will use default addresses.
	 *
	 * @return void
	 */
	public function testSetAddressesForOrderWillUseFallback() {
		$data = array(
			'Order' => array(
			),
		);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_setAddressesForOrder($data, 1, true);
		$this->assertContains('Lorem ipsum', $result['Order']['customers_name']);
		$this->assertContains('Customer 1 Address 1 Company', $result['Order']['billing_company']);
		$this->assertEquals('Costa Rica', $result['Order']['delivery_country']);
	}

	/**
	 * Test that _fallbackToDefaultAddresses will fail if the customer does not
	 * exist.
	 *
	 * @expectedException BadRequestException
	 */
	public function testFallbackToDefaultAddressesFailsWithInvalidCustomerId() {
		$data = array(
			'Order' => array(
				'customers_address_id' => 1,
			),
		);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_fallbackToDefaultAddresses($data, 999999999);
	}

	/**
	 * Confirm that _setDataForOrder() sets order data correctly with a
	 * supplied `insurance_coverage` value.
	 *
	 * @return void
	 */
	public function testSetDataForOrderWithInsuranceSet() {
		$customerId = 1;
		$data = array(
			'Order' => array(
				'insurance_coverage' => '25.00',
			),
		);
		$Order = ClassRegistry::init('Order');
		$customer = $Order->Customer->findByCustomersId($customerId);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_setDataForOrder($data, $customer);
		$this->assertEquals($customerId, $result['Order']['customers_id']);
		$this->assertEquals($data['Order']['insurance_coverage'], $result['Order']['insurance_coverage']);
		$this->assertContains('Lorem ipsum', $result['Order']['customers_telephone']);
		$this->assertEquals('someone@example.com', $result['Order']['customers_email_address']);
	}

	/**
	 * Confirm that _setDataForOrder() sets order data correctly when
	 * `insurance_coverage` is empty and the customer has a default insurance
	 * value set. The value can even be 0.00. Customers cannot have a null
	 * `insurnace_coverage` since the field type is decimal.
	 *
	 * @dataProvider provideDataForOrderWithInsuranceNotSet
	 * @return void
	 */
	public function testSetDataForOrderWithInsuranceNotSet($customerId) {
		$data = array(
			'Order' => array(
				'insurance_coverage' => '',
			),
		);
		$Order = ClassRegistry::init('Order');
		$customer = $Order->Customer->findByCustomersId($customerId);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_setDataForOrder($data, $customer);
		$this->assertEquals($customerId, $result['Order']['customers_id']);
		$this->assertEquals($customer['Customer']['insurance_amount'], $result['Order']['insurance_coverage']);
	}

	public function provideDataForOrderWithInsuranceNotSet() {
		return [
			[5],
			[6],
			[7],
		];
	}

	/**
	 * Confirm that _setDataForOrder() unsets `insurance_coverage` if the
	 * `insurance` key is set to 0.
	 *
	 * @return void
	 */
	public function testSetDataForOrderWithInsuranceFalse() {
		$customerId = 1;
		$data = array(
			'Order' => array(
				'insurance' => 'false',
			),
		);
		$Order = ClassRegistry::init('Order');
		$customer = $Order->Customer->findByCustomersId($customerId);
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_setDataForOrder($data, $customer);
		$this->assertArrayHasKey('insurance_coverage', $result['Order']);
		$this->assertEmpty($result['Order']['insurance_coverage']);
	}

	/**
	 * Confirm that _changeOrderStatus() throws an exception with message if
	 * required `order_status` key is missing.
	 *
	 * @return void
	 */
	public function testChangeOrderStatusNoOrderStatus() {
		$id = 1;
		$data = array();
		$this->setExpectedException('BadRequestException', 'Missing required key: orders_status');
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_changeOrderStatus($id, $data);
	}

	/**
	 * Confirm that _changeOrderStatus() throws an exception with message if
	 * `order_status` is `3` and `usps_track_num` is not set or empty
	 *
	 * @return void
	 */
	public function testChangeOrderStatusNoTrackingNumWhenShipped() {
		$id = 1;
		$data = array(
			'orders_status' => '3',
			'status_history_comments' => '',
			'usps_track_num' => '',
			'notify_customer' =>  '0'
		);
		$this->setExpectedException('BadRequestException', 'Tracking number required for shipped orders.');
		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_changeOrderStatus($id, $data);
	}

	/**
	 * Confirm that _changeOrderStatus() can actually change a record in the
	 * database.
	 *
	 * @return void
	 */
	public function testChangeOrderStatus() {
		$id = 1;
		$data = array(
			'orders_status' => '3',
			'status_history_comments' => '',
			'usps_track_num' => '12345ABCDE',
			'notify_customer' =>  '0'
		);

		$Order = ClassRegistry::init('Order');
		$before = $Order->findByOrdersId($id);
		$this->assertEquals($id, $before['Order']['orders_id']);
		$this->assertNotEquals($data['orders_status'], $before['Order']['orders_status']);

		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_changeOrderStatus($id, $data);

		$this->assertTrue($result);
		$after = $Order->findByOrdersId($id);
		$this->assertEquals($id, $after['Order']['orders_id']);
		$this->assertEquals($data['orders_status'], $after['Order']['orders_status']);
	}

	/**
	 * Confirm that _changeOrderStatus() throws an exception when it fails
	 * to update a record and that no records are actually updated.
	 *
	 * @return void
	 */
	public function testChangeOrderStatusFails() {
		$id = 1;
		$data = array(
			'orders_status' => 'foo',
			'status_history_comments' => '',
			'usps_track_num' => '',
			'notify_customer' =>  '0'
		);

		$this->setExpectedException('BadRequestException', 'Unable to update order status.');

		$Order = ClassRegistry::init('Order');
		$before = $Order->findByOrdersId($id);
		$this->assertEquals($id, $before['Order']['orders_id']);
		$this->assertNotEquals($data['orders_status'], $before['Order']['orders_status']);

		$TestOrders = $this->generate('TestOrders');
		$result = $TestOrders->_changeOrderStatus($id, $data);

		$after = $Order->findByOrdersId($id);
		$this->assertEquals($id, $after['Order']['orders_id']);
		$this->assertNotEquals($data['orders_status'], $after['Order']['orders_status']);
	}

	/**
	 * Confirm that manually charging an invoice customer doesn't engage the
	 * payment component, saves data correctly and redirects.
	 *
	 * @return	void
	 */
	public function testManagerChargeInvoiceSuccess() {
		$orderId = 9;
		$userId = 1;
		Configure::write('Security.admin.ips', false);
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
			),
			'methods' => array(
				'emailFactory',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));
		$orderBefore = $Orders->Order->find('first', array(
			'conditions' => array('Order.orders_id' => $orderId)
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
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		));
		$data = array(
			'submit' => 'charge',
			'Order' => array('orders_id' => $orderId),
			'OrderShipping' => array('value' => '1.00'),
			'OrderStorage' => array('value' => '1.00'),
			'OrderInsurance' => array('value' => '1.00'),
			'OrderFee' => array('value' => '5.00'),
			'OrderRepack' => array('value' => '10.00'),
			'OrderBattery' => array('value' => '0'),
		);

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$orderAfter = $Orders->Order->find('first', array(
			'contain' => array(
				'OrderInsurance',
				'OrderFee',
				'OrderSubtotal',
				'OrderTotal',
			),
			'conditions' => array(
				'Order.orders_id' => $orderId
			)
		));

		$this->assertEquals('5.00', $orderAfter['OrderFee']['value']);
		$this->assertEquals('18', $orderAfter['OrderSubtotal']['value']);
		$this->assertEquals('$18.00', $orderAfter['OrderSubtotal']['text']);
		$this->assertEquals('18', $orderAfter['OrderTotal']['value']);
		$this->assertEquals('<b>$18.00</b>', $orderAfter['OrderTotal']['text']);
		$this->assertEquals('1', $orderAfter['Order']['orders_status']);
		$this->assertEquals('5', $orderAfter['Order']['billing_status']);
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i', $this->headers['Location']);
		$this->assertArrayNotHasKey('order', $this->vars);
	}

	/**
	 * Confirm a manager can add an order for an invoice customer
	 *
	 * @return	void
	 */
	public function testManagerAddInvoiceCustomer() {
		$userId = 1;
		$customerId = 5;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', array(
			'methods' => array(
				'getUspsRates',
			),
			'models' => array(
				'CustomPackageRequest' => array('updateOrderId'),
			),
		));
		$Orders->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue([['@CLASSID' => '1', 'Rate' => '12.69']]));
		$Orders->Order->CustomPackageRequest->expects($this->once())
			->method('updateOrderId')
			->will($this->returnValue(true));
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		));
		$data['Order'] = $this->_getOrderCustomerData($data);

		$orderStorageOptions = array(
			'conditions' => array(
				'OrderStorage.class' => 'ot_custom',
			),
		);
		$orderRepackOptions = array(
			'conditions' => array(
				'OrderRepack.class' => 'ot_custom_1',
			)
		);
		$orderBatteryOptions = array(
			'conditions' => array(
				'OrderBattery.class' => 'ot_custom_2',
			)
		);
		$orderReturnOptions = array(
			'conditions' => array(
				'OrderReturn.class' => 'ot_custom_3',
			)
		);
		$orderMisaddressedOptions = array(
			'conditions' => array(
				'OrderMisaddressed.class' => 'ot_custom_4',
			)
		);
		$orderShipToUSOptions = array(
			'conditions' => array(
				'OrderShipToUS.class' => 'ot_custom_5',
			)
		);

		$orderCountBefore = $Customers->Order->find('count');
		$orderShippingCountBefore = $Customers->Order->OrderShipping->find('count');
		$orderStorageCountBefore = $Customers->Order->OrderStorage->find('count', $orderStorageOptions);
		$orderInsuranceCountBefore = $Customers->Order->OrderInsurance->find('count');
		$orderFeeCountBefore = $Customers->Order->OrderFee->find('count');
		$orderSubtotalCountBefore = $Customers->Order->OrderSubtotal->find('count');
		$orderTotalCountBefore = $Customers->Order->OrderTotal->find('count');
		$orderRepackCountBefore = $Customers->Order->OrderRepack->find('count', $orderRepackOptions);
		$orderBatteryCountBefore = $Customers->Order->OrderBattery->find('count', $orderBatteryOptions);
		$orderReturnCountBefore = $Customers->Order->OrderReturn->find('count', $orderReturnOptions);
		$orderMisaddressedCountBefore = $Customers->Order->OrderMisaddressed->find('count', $orderMisaddressedOptions);
		$orderShipToUSCountBefore = $Customers->Order->OrderShipToUS->find('count', $orderShipToUSOptions);

		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$order = $Customers->Order->find('first', array(
			'contain' => array(
				'OrderInsurance',
				'OrderFee',
				'OrderSubtotal',
				'OrderTotal',
				'OrderShipping',
			),
			'conditions' => array(
				'Order.orders_id' => $Customers->Order->getLastInsertId()
			)
		));
		$orderCountAfter = $Customers->Order->find('count');
		$orderShippingCountAfter = $Customers->Order->OrderShipping->find('count');
		$orderStorageCountAfter = $Customers->Order->OrderStorage->find('count', $orderStorageOptions);
		$orderInsuranceCountAfter = $Customers->Order->OrderInsurance->find('count');
		$orderFeeCountAfter = $Customers->Order->OrderFee->find('count');
		$orderSubtotalCountAfter = $Customers->Order->OrderSubtotal->find('count');
		$orderTotalCountAfter = $Customers->Order->OrderTotal->find('count');
		$orderRepackCountAfter = $Customers->Order->OrderRepack->find('count', $orderRepackOptions);
		$orderBatteryCountAfter = $Customers->Order->OrderBattery->find('count', $orderBatteryOptions);
		$orderReturnCountAfter = $Customers->Order->OrderReturn->find('count', $orderReturnOptions);
		$orderMisaddressedCountAfter = $Customers->Order->OrderMisaddressed->find('count', $orderMisaddressedOptions);
		$orderShipToUSCountAfter = $Customers->Order->OrderShipToUS->find('count', $orderShipToUSOptions);

		$this->assertEquals('5', $order['Order']['billing_status']);
		$this->assertEquals('1.75', $order['OrderInsurance']['value']);
		$this->assertEquals('12.69', $order['OrderShipping']['value']);
		$this->assertEquals('$12.69', $order['OrderShipping']['text']);
		$this->assertEquals('10.95', $order['OrderFee']['value']);
		$this->assertEquals('$10.95', $order['OrderFee']['text']);
		$this->assertEquals('25.39', $order['OrderSubtotal']['value']);
		$this->assertEquals('$25.39', $order['OrderSubtotal']['text']);
		$this->assertEquals('25.39', $order['OrderTotal']['value']);
		$this->assertEquals('<b>$25.39</b>', $order['OrderTotal']['text']);
		$this->assertEquals(($orderCountBefore + 1), $orderCountAfter, 'Should create exactly one Order record. Created: ' . ($orderCountAfter - $orderCountBefore));
		$this->assertEquals(($orderShippingCountBefore + 1), $orderShippingCountAfter, 'Should created exactly one OrderShipping record');
		$this->assertEquals(($orderStorageCountBefore + 1), $orderStorageCountAfter, 'Should created exactly one OrderStorage record');
		$this->assertEquals(($orderInsuranceCountBefore + 1), $orderInsuranceCountAfter, 'Should created exactly one OrderInsurance record');
		$this->assertEquals(($orderFeeCountBefore + 1), $orderFeeCountAfter, 'Should created exactly one OrderInsurance record');
		$this->assertEquals(($orderSubtotalCountBefore + 1), $orderSubtotalCountAfter, 'Should created exactly one OrderSubtotal record');
		$this->assertEquals(($orderTotalCountBefore + 1), $orderTotalCountAfter, 'Should created exactly one OrderTotal record');
		$this->assertEquals(($orderRepackCountBefore + 1), $orderRepackCountAfter, 'Should created exactly one OrderRepack record');
		$this->assertEquals(($orderBatteryCountBefore + 1), $orderBatteryCountAfter, 'Should created exactly one OrderBattery record');
		$this->assertEquals(($orderReturnCountBefore + 1), $orderReturnCountAfter, 'Should created exactly one OrderReturn record');
		$this->assertEquals(($orderMisaddressedCountBefore + 1), $orderMisaddressedCountAfter, 'Should created exactly one OrderMisaddressed record');
		$this->assertEquals(($orderShipToUSCountBefore + 1), $orderShipToUSCountAfter, 'Should created exactly one OrderShipToUS record');
		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i/charge', $this->headers['Location']);
	}

	/**
	 * Confirm the API throws an exception if the method is POST
	 *
	 * @return void
	 */
	public function testApiChargeInvalidMethod() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'update',
				),
			),
		);
		$this->setExpectedException('MissingActionException');

		$this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'post',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm the API throws an exception if the order $id doesn't exist
	 *
	 * @return void
	 */
	public function testApiChargeInvalidOrder() {
		$Orders = $this->setupApiAuth();
		$id = 999;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'update',
				),
			),
		);
		$this->setExpectedException('NotFoundException', "Invalid order number.");

		$this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm the API throws an exception if the data $type doesn't match the
	 * model.
	 *
	 * @return void
	 */
	public function testApiChargeInvalidType() {
		$Orders = $this->setupApiAuth();
		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'foo',
			),
		);
		$this->setExpectedException('BadRequestException', 'Invalid model');

		$this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm the API throws an exception if the order status is not allowed.
	 *
	 * @return void
	 */
	public function testApiChargeOrderStatusNotAllowed() {
		$Orders = $this->setupApiAuth();
		$id = 3;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'update',
				),
			),
		);
		$this->setExpectedException('BadRequestException', 'Orders cannot be charged while in status: Lorem ipsum dolor sit amet');

		$this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data)
		));
	}

	/**
	 * Confirm that the API can update an order and return the proper response.
	 *
	 * @return void
	 */
	public function testApiChargeUpdateSuccess() {
		$Orders = $this->setupApiAuth();
		$id = 8;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'update',
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$Order = ClassRegistry::init('Order');
		$before = $Order->OrderTotal->findByOrdersId($id);

		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
		$after = $Order->OrderTotal->findByOrdersId($id);

		$this->assertEquals('100.0000', $before['OrderTotal']['value']);
		$this->assertEquals('1079.0000', $after['OrderTotal']['value']);

		$result = json_decode($result, true);
		$this->assertArrayHasKey('data', $result);
		$this->assertArrayHasKey('id', $result['data']);
		$this->assertArrayHasKey('OrderStorage', $result['data']['relationships']);
		$this->assertArrayNotHasKey('payment_method', $result['data']['attributes']);
		$this->assertEquals($after['OrderTotal']['value'], $result['data']['relationships']['OrderTotal']['data']['value']);
		$this->assertEquals($data['data']['relationships']['OrderStorage']['data']['value'], $result['data']['relationships']['OrderStorage']['data']['value']);
	}

	/**
	 * Confirm that the API can charge an order and return the proper response.
	 *
	 * @return void
	 */
	public function testApiChargeSuccess() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge'),
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));

		$id = 8;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'charge',
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$Order = ClassRegistry::init('Order');
		$before = $Order->OrderTotal->findByOrdersId($id);

		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
		$after = $Order->OrderTotal->findByOrdersId($id);

		$this->assertEquals('100.0000', $before['OrderTotal']['value']);
		$this->assertEquals('1079.0000', $after['OrderTotal']['value']);

		$result = json_decode($result, true);
		$this->assertArrayHasKey('data', $result);
		$this->assertArrayHasKey('id', $result['data']);
		$this->assertArrayHasKey('OrderStorage', $result['data']['relationships']);
		$this->assertEquals($after['OrderTotal']['value'], $result['data']['relationships']['OrderTotal']['data']['value']);
		$this->assertEquals($data['data']['relationships']['OrderStorage']['data']['value'], $result['data']['relationships']['OrderStorage']['data']['value']);
		$this->assertArrayHasKey('payment_method', $result['data']['attributes']);
		$this->assertEquals('Payments Pro', $result['data']['attributes']['payment_method']);
	}

	/**
	 * Confirm that the charge API can update order and billing status.
	 *
	 * @return void
	 */
	public function testApiChargeSuccessAndUpdateStatus() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge'),
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));

		$id = 8;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'charge',
					'orders_status' => 3,
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$Order = ClassRegistry::init('Order');
		$before = $Order->findByOrdersId($id);

		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
		$after = $Order->findByOrdersId($id);

		$this->assertSame('3', $after['Order']['orders_status']);
		$this->assertSame('3', $after['Order']['billing_status']);
	}

	/**
	 * Confirm that the API throws an exception if Order::recordPayment() fails.
	 *
	 * @return void
	 */
	public function testApiChargeSuccessButRecordPaymentFails() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge'),
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
				'logBaseSerializerException',
			),
			'models' => array(
				'Order' => array('recordPayment', 'sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('recordPayment')
			->will($this->returnValue(false));
		$Orders->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->identicalTo('The charge was successful but could not be recorded.'),
				$this->identicalTo('Order'),
				$this->identicalTo('orders'),
				$this->anything()
			);

		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'charge',
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$this->setExpectedException('BaseSerializerException');
		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
	}

	/**
	 * Confirm that the API throws an exception if payment fails.
	 *
	 * @return void
	 */
	public function testApiChargePaymentFailure() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge'),
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
				'logBaseSerializerException',
			),
			'models' => array(
				'Order' => array('recordPayment', 'sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Payment
			->expects($this->once())
			->method('charge')
			->will($this->returnValue(false));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Orders->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->identicalTo('Charge Error.'),
				$this->identicalTo('Order'),
				$this->identicalTo('orders'),
				$this->anything()
			);

		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'charge',
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$this->setExpectedException('BaseSerializerException');
		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
	}

	/**
	 * Confirm that the API throws an exception if saving fails.
	 *
	 * @return void
	 */
	public function testApiChargeSaveFailure() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
				'Payment' => array('charge'),
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
				'logBaseSerializerException',
			),
			'models' => array(
				'Order' => array('saveOrderForCharge', 'sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Order
			->expects($this->once())
			->method('saveOrderForCharge')
			->will($this->returnValue(false));
		$Orders->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->identicalTo('The amounts could not be saved before charging the card. The card was not charged.'),
				$this->identicalTo('Order'),
				$this->identicalTo('orders'),
				$this->anything()
			);

		$id = 1;
		$data = array(
			'data' => array(
				'type' => 'orders',
				'attributes' => array(
					'submit' => 'charge',
				),
				'relationships' => array(
					'OrderStorage' => array(
						'data' => array(
							'value' => '999',
						),
					),
				),
			),
		);

		$this->setExpectedException('BaseSerializerException');
		$result = $this->testAction("/api/orders/{$id}/charge", array(
			'method' => 'patch',
			'data' => json_encode($data),
		));
	}

	protected function setupAuth($userId) {
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Activity' => ['record'],
				'Auth' => array('user', 'login'),
				'Payment' => array('getPaymentLib')
			),
		));
		$Orders->Order->Customer->CustomerReminder = $this->getMockForModel('CustomerReminder', array('clearRecord'));

		$user = ClassRegistry::init('Customer')->find('first', array(
			'contain' => array(),
			'conditions' => array('customers_id' => $userId),
		));
		$user = $user['Customer'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		return $Orders;
	}

	protected function setupManagerAuth($userId) {
		Configure::write('Security.admin.ips', false);

		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login')
			),
			'methods' => array('emailFactory', 'referer'),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
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

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		return $Orders;
	}

	protected function setupApiAuth() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login')
			),
			'methods' => array(
				'emailFactory',
				'_changeOrderStatus',
			),
			'models' => array(
				'Order' => array('sendStatusUpdateEmail'),
			),
		));

		$user = array(
			'role' => 'api',
		);
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};

		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));

		return $Orders;
	}

	/**
	 * Confirm that the manger report with accessed by GET returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerReportGet() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'report',
			'manager' => true,
		));

		$this->testAction($url, array(
			'method' => 'get',
		));

		$this->assertArrayNotHasKey('interval', $this->vars);
		$this->assertArrayNotHasKey('results', $this->vars);
		$this->assertArrayHasKey('validIntervals', $this->vars);
		$this->assertArrayHasKey('validSortFields', $this->vars);
		$this->assertArrayHasKey('statusFilterOptions', $this->vars);
		$this->assertArrayHasKey('isManager', $this->vars);
	}

	/**
	 * Confirm that the manger report with POSTed data returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerReportPost() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'report',
			'manager' => true,
		));

		$data = array(
			'interval' => 'year',
			'from_date' => '2015-01-01 00:00:00',
			'to_date' => '2015-12-31 00:00:00'
		);

		$this->testAction($url, array(
			'method' => 'post',
			'data' => $data,
		));

		$this->assertArrayHasKey('interval', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey(0, $this->vars['results']);
		$this->assertEquals(10, $this->vars['results'][0]['total']);
		$this->assertArrayHasKey('validIntervals', $this->vars);
		$this->assertArrayHasKey('validSortFields', $this->vars);
		$this->assertArrayHasKey('statusFilterOptions', $this->vars);
		$this->assertArrayHasKey('isManager', $this->vars);
	}

	/**
	 * Confirm that the requestAction request to manager_statustotals returns
	 * the correct data.
	 *
	 * @return void
	 */
	public function testManagerStatusTotals() {
		$userId = 1;
		$this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'statustotals',
			'manager' => true,
		));
		$result = $this->testAction($url);
		$this->assertEquals(4, count($result));
		$this->assertContains('Lorem ipsum', key($result[0]));
		$this->assertEquals(2, $result[1]['Lorem ipsum dolor sit amet']);
		$this->assertEquals(1, $result[2]['Lorem ipsum dolor sit amet']);
	}

	/**
	 * Confirm the method throws an exception if called directly and not
	 * via a request.
	 *
	 * @return void
	 */
	public function testManagerStatusTotalsNotRequested() {
		$Orders = $this->generate('Orders');
		$this->setExpectedException('ForbiddenException');

		$Orders->manager_statustotals();
	}

	/**
	 * Confirm that a BadRequestException is thrown when `weight_lb` is present
	 * and `weight_oz` is invalid.
	 *
	 * @return	void
	 */
	public function testManagerAddInvalidWeightData() {
		$userId = 1;
		$customerId = 1;
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'weight_oz' => 'foo',
			'weight_lb' => '',
		));

		$this->setExpectedException('BadRequestException', 'An invalid key exists in the request: weight_lb');

		$this->testAction($url, array('method' => 'post', 'data' => $data));
	}

	/**
	 * Confirm that when `submit => postage` a charge is not made, a request
	 * for USPS rates is initiated, $rates variable is set, and no redirect
	 * occurs.
	 *
	 * @return void
	 */
	public function testManagerChargeGetUspsRates() {
		$userId = 1;
		Configure::write('Security.admin.ips', false);
		Configure::write('ShippingApis.Usps.userId', $userId);
		Configure::write('ShippingApis.Rates.backend', 'Usps');
		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Auth' => array('user', 'login'),
			),
			'methods' => array(
				'getUspsRates',
			),
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
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders
			->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue('USPS rates array'));
		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'charge',
			'id' => 1,
			'manager' => true,
		));

		$data = array('submit' => 'postage');
		$this->testAction($url, array('method' => 'post', 'data' => $data));

		$this->assertArrayHasKey('rates', $this->vars);
		$this->assertEquals('USPS rates array', $this->vars['rates']);
		$this->assertArrayNotHasKey('Location', $this->headers, 'should not redirect');
	}

	/**
	 * Confirm that an order's `OrderShipping` values can be updated by calling
	 * addPostage() with an order id.
	 *
	 * @return void
	 */
	public function testAddPostageUspsPriority() {
		$orderId = 1;
		$Orders = $this->generate('TestOrders', array(
			'methods' => array(
				'getUspsRates',
			),
		));
		$rates = array(
			0 => array(
				'@CLASSID' => '4',
				'MailService' => 'Standard Post',
				'Rate' => '35.35'
			),
			1 => array(
				'@CLASSID' => '2',
				'MailService' => 'Priority Mail Express',
				'Rate' => '35.35'
			),
			2 => array(
				'@CLASSID' => '1',
				'MailService' => 'Priority Mail',
				'Rate' => '8.85'
			),
		);
		$Orders
			->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue($rates));

		$options = array(
			'conditions' => array(
				'Order.orders_id' => $orderId,
			),
			'contain' => array(
				'OrderShipping',
			),
		);
		$before = $Orders->Order->find('first', $options);
		$this->assertEquals($orderId, $before['OrderShipping']['orders_id']);
		$this->assertEquals('$77.90', $before['OrderShipping']['text']);
		$this->assertEquals('77.9000', $before['OrderShipping']['value']);

		$result = $Orders->addPostage($orderId);
		$this->assertInternalType('array', $result);

		$after = $Orders->Order->find('first', $options);
		$this->assertEquals($orderId, $after['OrderShipping']['orders_id']);
		$this->assertEquals('$8.85', $after['OrderShipping']['text']);
		$this->assertEquals('8.85', $after['OrderShipping']['value']);
	}

	/**
	 * Confirm that an order's `OrderShipping` values can be updated by calling
	 * addPostage() with an order id when mail class is PARCEL.
	 *
	 * @return void
	 */
	public function testAddPostageUspsParcel() {
		$id = 1;
		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['find', 'usesFedex'],
			],
			'methods' => [
				'getUspsRates',
			],
		]);
		$rates = [
			0 => [
				'@CLASSID' => '1058',
				'MailService' => 'Ground Advantage',
				'Rate' => '35.35'
			],
		];

		$order = [
			'Order' => [
				'mail_class' => 'PARCEL',
			],
			'OrderShipping' => [
				'orders_total_id' => $id,
			],
		];

		$Orders->Order->expects($this->once())
			->method('find')
			->will($this->returnValue($order));
		$Orders->Order->expects($this->once())
			->method('usesFedex')
			->with($order)
			->will($this->returnValue(false));
		$Orders->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue($rates));

		$result = $Orders->addPostage($id);

		$this->assertSame($id, $result['OrderShipping']['orders_total_id']);
		$this->assertSame($rates[0]['Rate'], $result['OrderShipping']['value']);
	}

	/**
	 * Confirm that when an order is attempting to be made for a partial signup customer
	 * a flash message is shown and a redirect occurs.
	 *
	 * @return	void
	 */
	public function testManagerAddPartialSignupCustomer() {
		$userId = 1;
		$customerId = 7;
		$Customers = $this->setupManagerAuth($userId);

		$Orders = $this->generate('Orders', array(
			'components' => array(
				'Flash' => array('set'),
			),
		));
		$Orders->Flash
			->expects($this->once())
			->method('set');

		$url = Router::url(array(
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		));
		$data = array('Order' => array(
			'customers_id' => $customerId,
			'weight_oz' => 2,
			'length' => 2,
			'width' => 2,
			'depth' => 2,
		));

		$this->testAction($url, array('method' => 'post', 'data' => $data));
		$this->assertArrayHasKey('Location', $this->headers, 'should redirect');
	}

	/**
	 * Combines supplied order $data with the customer address required fields
	 * to successfully create an order record.
	 *
	 * @param array $data Existing data
	 * @param int $id An order id
	 * @return array The combined data
	 */
	protected function _getOrderCustomerData($data = [], $id = 1) {
		$Order = ClassRegistry::init('Order');
		$fields = $Order->getOrderAddressFields();
		$customerData = $Order->find('first', [
			'fields' => $fields,
			'conditions' => ['orders_id' => $id],
		]);
		return array_merge($customerData['Order'], $data['Order']);
	}

	/**
	 * Confirm that the expected chain of methods are called when a label
	 * printing job is successful.
	 *
	 * @return void
	 */
	public function testPrintZebraLabelSuccess() {
		$id = 1;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initZebraLabel',
			],
		]);
		$ZebraLabel = $this->getMockBuilder('ZebraLabel')
			->disableOriginalConstructor()
			->getMock(['printLabel']);
		$Orders->expects($this->once())
			->method('initZebraLabel')
			->will($this->returnValue($ZebraLabel));
		$ZebraLabel->expects($this->once())
			->method('printLabel')
			->will($this->returnValue(true));
		$result = $Orders->printZebraLabel($id);
	}

	/**
	 * Confirm that the ZebraLabel lib will throw an exception if required
	 * config variables are not passed printZebraLabel() will not proceed.
	 *
	 * @return void
	 */
	public function testPrintZebraLabelException() {
		Configure::delete('ZebraLabel.method');
		$id = 1;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initZebraLabel',
			],
		]);
		$this->setExpectedException('BadMethodCallException');
		$ZebraLabel = $this->getMockBuilder('ZebraLabel')
			->getMock(['printLabel']);
		$Orders->expects($this->once())
			->method('initZebraLabel')
			->will($this->returnValue($ZebraLabel));
		$ZebraLabel->expects($this->never())
			->method('printLabel');
		$result = $Orders->printZebraLabel($id);
	}

	/**
	 * Confirm that when prepareLabelData() is supplied with an $Order array
	 * it prepares and outputs data in the expected format with expected values.
	 *
	 * @return void
	 */
	public function testPrepareLabelData() {
		$id = 1;
		$Orders = $this->generate('TestOrders');
		$Order = ClassRegistry::init('Order');
		$order = $Order->findOrderForCharge($id);
		$result = $Orders->prepareLabelData($order);
		$this->assertArrayHasKey('header', $result);
		$this->assertArrayHasKey('body', $result);
		$this->assertArrayHasKey('footer', $result);
		$this->assertArrayHasKey('size', $result['header']);
		$this->assertArrayHasKey('size', $result['body']);
		$this->assertArrayHasKey('size', $result['footer']);
		$this->assertContains($order['Customer']['billing_id'], $result['header']['content']);
		$this->assertContains($order['Order']['delivery_street_address'], $result['body']['content']);
		$this->assertContains($order['Order']['orders_id'], $result['footer']['content']);
	}

	/**
	 * Confirm that when manager_print_label is requested via AJAX and printZebraLabel()
	 * does not throw an exception the expected "zpl data" is returned.
	 *
	 * @return void
	 */
	public function testManagerPrintLabelSuccess() {
		$orderId = 1;
		$userId = 1;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printZebraLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printZebraLabel')
			->will($this->returnValue('zpl data'));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_label',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertSame('zpl data', $result);
	}

	/**
	 * Confirm that when manager_print_label is requested via AJAX and printZebraLabel()
	 * returns null the method also returns null.
	 *
	 * @return void
	 */
	public function testManagerPrintLabelFailure() {
		$orderId = 1;
		$userId = 1;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printZebraLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printZebraLabel')
			->will($this->returnValue(null));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_label',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertNull($result);
	}

	/**
	 * Confirm that when manager_print_label is NOT requested via AJAX the method
	 * returns null.
	 *
	 * @return void
	 */
	public function testManagerPrintLabelNotAjax() {
		$orderId = 1;
		$userId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_label',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertNull($result);
	}

	/**
	 * Confirm that when employee_print_label is requested via AJAX and printZebraLabel()
	 * does not throw an exception the expected "zpl data" is returned.
	 *
	 * @return void
	 */
	public function testEmployeePrintLabelSuccess() {
		$orderId = 1;
		$userId = 2;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printZebraLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printZebraLabel')
			->will($this->returnValue('zpl data'));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_label',
			'id' => $orderId,
			'employee' => true,
		]);
		$result = $this->testAction($url);
		$this->assertSame('zpl data', $result);
	}

	/**
	 * Confirm that when employee_print_label is requested via AJAX and printZebraLabel()
	 * returns null the method also returns null.
	 *
	 * @return void
	 */
	public function testEmployeePrintLabelFailure() {
		$orderId = 1;
		$userId = 2;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printZebraLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printZebraLabel')
			->will($this->returnValue(null));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_label',
			'id' => $orderId,
			'employee' => true,
		]);
		$result = $this->testAction($url);
		$this->assertNull($result);
	}

	/**
	 * Confirm an instance of class ZebraLabel is created by initZebraLabel()
	 *
	 * @return void
	 */
	public function testInitZebraLabelRate() {
		$Orders = $this->generate('TestOrders', [
		]);
		$config = [
			'method' => 'foo',
		];
		$this->assertInstanceOf('ZebraLabel', $Orders->initZebraLabel($config));
	}

	/**
	 * Confirm when a customer has a non-default value for `insurance_amount`
	 * the correct value populates the field in `manager_add`.
	 *
	 * @dataProvider provideManagerAddView
	 * @return void
	 */
	public function testManagerAddView($customerId, $insurance) {
		$userId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);
		$result = $this->testAction($url, ['method' => 'get', 'return' => 'view']);
		$this->assertArrayHasKey('customer', $this->vars);
		$this->assertSame($insurance, $this->vars['customer']['Customer']['insurance_amount']);
		$this->assertRegExp('/value="' . $insurance . '"/', $result);
	}

	public function provideManagerAddView() {
		return [
			[4, '100.00'],
			[5, '50.00'],
			[7, '0.00'],
		];
	}

	/*
	 * Confirm that an order's `OrderShipping` values can be updated by calling
	 * addPostage() with an order id.
	 *
	 * @return void
	 */
	public function testAddPostageFedex() {
		$orderId = 1;
		$Orders = $this->generate('TestOrders', [
			'methods' => ['getFedexRates'],
			'models' => ['Order' => ['usesFedex']],
		]);
		$rates = [
			0 => [
				'@CLASSID' => 'FedEx',
				'MailService' => 'FEDEX GROUND',
				'Rate' => '12.69'
			],
		];

		$Orders->Order->expects($this->once())
			->method('usesFedex')
			->will($this->returnValue(true));
		$Orders->expects($this->once())
			->method('getFedexRates')
			->will($this->returnValue($rates));

		$options = [
			'conditions' => [
				'Order.orders_id' => $orderId,
			],
			'contain' => [
				'OrderShipping',
			],
		];
		$before = $Orders->Order->find('first', $options);
		$this->assertEquals($orderId, $before['OrderShipping']['orders_id']);
		$this->assertEquals('$77.90', $before['OrderShipping']['text']);
		$this->assertEquals('77.9000', $before['OrderShipping']['value']);

		$result = $Orders->addPostage($orderId);
		$this->assertInternalType('array', $result);

		$after = $Orders->Order->find('first', $options);
		$this->assertEquals($orderId, $after['OrderShipping']['orders_id']);
		$this->assertEquals('$12.69', $after['OrderShipping']['text']);
		$this->assertEquals('12.69', $after['OrderShipping']['value']);
	}

	/**
	 * Confirm an instance of class Usps is created by initUsps()
	 *
	 * @return void
	 */
	public function testInitUsps() {
		$Orders = $this->generate('TestOrders');
		$this->assertInstanceOf('Usps', $Orders->initUsps());
	}

	/**
	 * Confirm an instance of class Fedex is created by initFedex()
	 *
	 * @return void
	 */
	public function testInitFedex() {
		$Orders = $this->generate('TestOrders');
		$this->assertInstanceOf('Fedex', $Orders->initFedex());
	}

	/**
	 * Confirm that when an order has been determined to use FedEx for shipping
	 * by Order::usesFedex() the `mail_class` value is updated upon order
	 * saving or charging.
	 *
	 * @return void
	 */
	public function testManagerChargeFedex() {
		$orderId = 8;
		$userId = 1;
		Configure::write('Security.admin.ips', false);

		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'checkIfOrderCanBeCharged',
				'usesFedex',
			],
			'models' => [
				'Order' => ['usesFedex'],
			],
		]);

		$orderBefore = $Orders->Order->find('first', [
			'conditions' => ['Order.orders_id' => $orderId]
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->Order->expects($this->once())
			->method('usesFedex')
			->will($this->returnValue(true));

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		]);

		$data = [
			'submit' => 'update',
			'Order' => ['orders_id' => $orderId],
		];

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$orderAfter = $Orders->Order->find('first', [
			'contain' => [],
			'conditions' => [
				'Order.orders_id' => $orderId
			]
		]);
		$this->assertSame('Lorem ipsum d', $orderBefore['Order']['mail_class']);
		$this->assertSame('FEDEX', $orderAfter['Order']['mail_class']);
	}

	/**
	 * Confirm that when manager_print_fedex is requested via AJAX and printFedexLabel()
	 * does not throw an exception the method returns the raw zpl data.
	 *
	 * @return void
	 */
	public function testManagerPrintFedexLabelSuccess() {
		$orderId = 1;
		$userId = 1;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printFedexLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printFedexLabel')
			->will($this->returnValue('zpl label'));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertSame('zpl label', $result);
	}

	/**
	 * Confirm that when manager_print_fedex is requested via AJAX and printFedexLabel()
	 * returns false the method returns 'failure'
	 *
	 * @return void
	 */
	public function testManagerPrintFedexLabelFailure() {
		$orderId = 1;
		$userId = 1;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printFedexLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printFedexLabel')
			->will($this->returnValue(null));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertNull($result);
	}

	/**
	 * Confirm that when manager_print_fedex is NOT requested via AJAX the method
	 * returns null.
	 *
	 * @return void
	 */
	public function testManagerPrintFedexLabelNotAjax() {
		$orderId = 1;
		$userId = 1;
		$Orders = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => $orderId,
			'manager' => true,
		]);
		$result = $this->testAction($url);
		$this->assertNull($result);
	}

	/**
	 * Confirm that when employee_print_fedex is requested via AJAX and printFedexLabel()
	 * does not throw an exception the method returns the raw zpl data.
	 *
	 * @return void
	 */
	public function testEmployeePrintFedexLabelSuccess() {
		$orderId = 1;
		$userId = 2;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$Orders = $this->generate('TestOrders', [
			'components' => [
				'Auth' => ['user', 'login'],
			],
			'methods' => [
				'printFedexLabel',
			],
		]);
		$user = ClassRegistry::init('Admin')->find('first', [
			'contain' => [],
			'conditions' => ['id' => $userId],
		]);
		$user = $user['Admin'];
		$authUserSingle = function($field) use ($user) {
			$userField = (isset($user[$field]) ? $user[$field] : null);
			return (!$field) ? $user : $userField;
		};
		$Orders->Auth
			->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback($authUserSingle));
		$Orders->expects($this->once())
			->method('printFedexLabel')
			->will($this->returnValue('zpl label'));
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'print_fedex',
			'id' => $orderId,
			'employee' => true,
		]);
		$result = $this->testAction($url);
		$this->assertSame('zpl label', $result);
	}

	/**
	 * Confirm that printFedexLabel() returns bool false if supplied with an
	 * invalid or missing order id.
	 *
	 * @return void
	 */
	public function testPrintFedexLabelInvalidOrder() {
		$orderId = 'foo';
		$Orders = $this->generate('TestOrders');
		$result = $Orders->printFedexLabel($orderId);
		$this->assertFalse($result);
	}

	/**
	 * Confirm that printFedexLabel() will attempt to get a new label via the
	 * Fedex lib if $reprint is false or not set.
	 *
	 * @return void
	 */
	public function testPrintFedexLabel() {
		$orderId = 2;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initFedex',
			],
			'models' => [
				'OrderData' => ['fetchOrderData']
			],
		]);
		$Fedex = $this->getMockBuilder('Fedex')
			->disableOriginalConstructor()
			->setMethods(['printLabel'])
			->getMock();
		$Orders->Order->OrderData->expects($this->never())
			->method('fetchOrderData');
		$Orders->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($Fedex));
		$Fedex->expects($this->once())
			->method('printLabel')
			->will($this->returnValue('raw zpl data'));

		$result = $Orders->printFedexLabel($orderId);
		$this->assertSame('raw zpl data', $result);
	}

	/**
	 * Confirm that when printFedexLabel() is called with $reprint = true the
	 * expected method is called and bool true is returned.
	 *
	 * @return void
	 */
	public function testPrintFedexLabelReprint() {
		$orderId = 2;
		$Orders = $this->generate('TestOrders', [
			'models' => [
				'OrderData' => ['fetchOrderData']
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('fetchOrderData')
			->will($this->returnValue('raw zpl data'));
		$result = $Orders->printFedexLabel($orderId, true);
		$this->assertSame('raw zpl data', $result);
	}

	/**
	 * Confirm that when `ShippingApis.Fedex.label.type` is not ZPLII the
	 * method returns a boolean result and not raw zpl data.
	 *
	 * @return void
	 */
	public function testPrintFedexLabelNotZpl() {
		Configure::write('ShippingApis.Fedex.label.type', 'foo');
		$orderId = 2;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initFedex',
			],
			'models' => [
				'OrderData' => ['fetchOrderData']
			],
		]);
		$Fedex = $this->getMockBuilder('Fedex')
			->disableOriginalConstructor()
			->setMethods(['printLabel'])
			->getMock();
		$Orders->Order->OrderData->expects($this->never())
			->method('fetchOrderData');
		$Orders->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($Fedex));
		$Fedex->expects($this->once())
			->method('printLabel')
			->will($this->returnValue('raw zpl data'));

		$result = $Orders->printFedexLabel($orderId);
		$this->assertInternalType('bool', $result);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that if fetching saved zpl data, a new label request is made.
	 *
	 * @return void
	 */
	public function testPrintFedexLabelReprintFails() {
		$orderId = 2;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initFedex',
			],
			'models' => [
				'OrderData' => ['fetchOrderData']
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('fetchOrderData')
			->will($this->returnValue(false));
		$Fedex = $this->getMockBuilder('Fedex')
			->disableOriginalConstructor()
			->setMethods(['printLabel'])
			->getMock();
		$Orders->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($Fedex));
		$Fedex->expects($this->once())
			->method('printLabel')
			->will($this->returnValue('newly fetched raw zpl data'));

		$result = $Orders->printFedexLabel($orderId, true);

		$this->assertSame('newly fetched raw zpl data', $result);
	}

	/**
	 * Confirm that when a label delete request is successful a flash message is
	 * displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testManagerDeleteLabelSuccess() {
		$orderId = 1;
		$userId = 1;
		$this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth',
				'Flash' => ['set'],
			],
			'models' => [
				'OrderData' => ['clearOrderData'],
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('clearOrderData')
			->will($this->returnValue(true));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The FedEx label has been removed.');

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete_label',
			'id' => $orderId,
			'manager' => true,
		]);
		$this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm that when a label delete request fails a flash message is
	 * displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testManagerDeleteLabelFailure() {
		$orderId = 1;
		$userId = 1;
		$this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth',
				'Flash' => ['set'],
			],
			'models' => [
				'OrderData' => ['clearOrderData'],
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('clearOrderData')
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The FedEx label could not be removed.');

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete_label',
			'id' => $orderId,
			'manager' => true,
		]);
		$this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm that if an $id is missing the `clearOrderData` method is not
	 * called and the expected flash message is displayed.
	 *
	 * @return void
	 */
	public function testManagerDeleteLabelWithoutId() {
		$orderId = null;
		$userId = 1;
		$this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth',
				'Flash' => ['set'],
			],
			'models' => [
				'OrderData' => ['clearOrderData'],
			],
		]);
		$Orders->Order->OrderData->expects($this->never())
			->method('clearOrderData');
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The FedEx label could not be found and was not removed.');

		$Orders->manager_delete_label(null);
	}

	/**
	 * Confirm that when a label delete request is successful a flash message is
	 * displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testEmployeeDeleteLabelSuccess() {
		$orderId = 1;
		$userId = 2;
		$this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth',
				'Flash' => ['set'],
			],
			'models' => [
				'OrderData' => ['clearOrderData'],
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('clearOrderData')
			->will($this->returnValue(true));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The FedEx label has been removed.');

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete_label',
			'id' => $orderId,
			'employee' => true,
		]);
		$this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm that when a label delete request fails a flash message is
	 * displayed and a redirect occurs.
	 *
	 * @return void
	 */
	public function testEmployeeDeleteLabelFailure() {
		$orderId = 1;
		$userId = 2;
		$this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'components' => [
				'Auth',
				'Flash' => ['set'],
			],
			'models' => [
				'OrderData' => ['clearOrderData'],
			],
		]);
		$Orders->Order->OrderData->expects($this->once())
			->method('clearOrderData')
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The FedEx label could not be removed.');

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete_label',
			'id' => $orderId,
			'employee' => true,
		]);
		$this->testAction($url);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm that when an external rate query request returns a null or empty
	 * array value, the order is still saved and the expected flash message is
	 * displayed.
	 *
	 * @return void
	 */
	public function testManagerAddRateQueryFailure() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$msg = 'IMPORTANT: The order has been created but postage was NOT automatically added.';
		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'methods' => [
				'getUspsRates',
			],
			'models' => [
				'OrderTotal' => ['updateTotal'],
				'CustomPackageRequest' => ['updateOrderId'],
			],
			'components' => [
				'Flash' => ['set'],
			],
		]);
		$Orders->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue([]));
		$Orders->Order->CustomPackageRequest->expects($this->once())
			->method('updateOrderId')
			->will($this->returnValue(true));
		$Orders->Order->OrderTotal->expects($this->exactly(2))
			->method('updateTotal')
			->will($this->returnValue(true));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with($msg);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);
		$data = ['Order' => [
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
		]];
		$data['Order'] = $this->_getOrderCustomerData($data);

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$this->assertArrayHasKey('Location', $this->headers);
		$this->assertStringMatchesFormat('%s/manager/orders/%i/charge', $this->headers['Location']);
	}

	/**
	 * Confirm that when getUspsRates() returns anything other than an array
	 * containing a `@CLASSID` key and `Rate` key the addPostage() method
	 * does not emit any warnings or notices and returns bool false.
	 *
	 * @dataProvider provideAddPostageRateQueryFailure
	 * @return void
	 */
	public function testAddPostageRateQueryFailure($result, $msg = '') {
		$orderId = 1;
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'getUspsRates',
			],
		]);
		$Orders
			->expects($this->once())
			->method('getUspsRates')
			->will($this->returnValue($result));

		$result = $Orders->addPostage($orderId);
		$this->assertFalse($result);
	}

	public function provideAddPostageRateQueryFailure() {
		return [
			[[]],
			[null],
			[false],
			['foo'],
			[0],
			[27],
			[[
				'not @CLASSID' => 1,
				'Rate' => 17,
			]],
			[[
				'@CLASSID' => 1,
				'not Rate' => 17,
			]],
		];
	}

	/**
	 * Confirm that when a manager attempts to add an order for a customer with
	 * incomplete address data, the expected flash message is displayed and a
	 * redirect occurs.
	 *
	 * @return void
	 */
	public function testManagerAddMissingAddressData() {
		$userId = 1;
		$customerId = 1;
		$this->setupManagerAuth($userId);

		$Orders = $this->generate('Orders', [
			'models' => [
				'Address' => ['find'],
			],
			'components' => [
				'Flash' => ['set'],
			],
		]);

		$Orders->Order->Customer->Address->expects($this->once())
			->method('find')
			->with('list')
			->will($this->returnValue([]));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('This customer has insufficient address data for an order to be created.');

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);

		$this->testAction($url, ['method' => 'get']);
		$this->assertArrayHasKey('Location', $this->headers, 'Should redirect');
	}

	/**
	 * Confirm when order data has the `insurance` key set and it's value is
	 * false, the resulting order data does NOT have the `insurance_coverage`
	 * key set.
	 *
	 * @return void
	 */
	public function testManagerAddNoInsurance() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';

		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => ['saveOrder'],
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);

		$data = ['Order' => [
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
			'insurance' => false,
		]];
		$data['Order'] = $this->_getOrderCustomerData($data);

		$Orders->Order->expects($this->once())
			->method('saveOrder')
			->will($this->returnValue(false));

		$this->testAction($url, ['method' => 'post', 'data' => $data]);
		$this->assertArrayNotHasKey(
			'insurance_coverage',
			$Orders->data['Order'],
			'Should not have `insurance_coverage` key if `insurance` is false'
		);
	}

	/**
	 * Confirm when saving an order fails the `inbound_tracking_number` validation
	 * error is set with the value of the order's inbound shipping method error.
	 *
	 * @dataProvider provideManagerAddValidationErrors
	 * @return void
	 */
	public function testManagerAddValidationErrors($errorKey, $errorVal) {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';

		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => ['checkForInvoiceCustomer', 'saveOrder'],
			],
			'components' => [
				'Flash' => ['set'],
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);

		$data = ['Order' => [
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
			'insurance' => false,
		]];
		$data['Order'] = $this->_getOrderCustomerData($data);

		$Orders->Order->expects($this->once())
			->method('saveOrder')
			->will($this->returnValue(false));
		$Orders->Order->expects($this->once())
			->method('checkForInvoiceCustomer')
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The order could not be saved. Please, try again.');

		$Orders->Order->validationErrors[$errorKey] = $errorVal;

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$this->assertArrayHasKey($errorKey, $Orders->Order->validationErrors);
		$this->assertArrayHasKey('inbound_tracking_number', $Orders->Order->validationErrors);
		$this->assertSame(
			$Orders->Order->validationErrors[$errorKey],
			$Orders->Order->validationErrors['inbound_tracking_number'],
			'Value of `$errorKey` should match value of `inbound_tracking_number`'
		);
	}

	public function provideManagerAddValidationErrors() {
		return [
			['fedex_track_num', 'fedex error'],
			['ups_track_num', 'ups error'],
			['usps_track_num_in', 'usps error'],
			['dhl_track_num', 'dhl error'],
		];
	}

	/**
	 * Confirm that when an order does not have a valid shipping address the
	 * validation error is set correctly.
	 *
	 * @return void
	 */
	public function testManagerAddInvalidShipping() {
		$userId = 1;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';

		$Customers = $this->setupManagerAuth($userId);
		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => ['checkForInvoiceCustomer', 'saveOrder', 'validShipping'],
			],
			'components' => [
				'Flash' => ['set'],
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'manager' => true,
			'customerId' => $customerId,
		]);

		$data = ['Order' => [
			'customers_id' => $customerId,
			'inbound_tracking_number' => $trackingNum,
			'carrier' => 'ups',
			'width' => '2',
			'length' => '4',
			'depth' => '6',
			'weight_oz' => '8',
			'mail_class' => 'priority',
			'package_type' => 'rectparcel',
			'customs_description' => 'Testing',
			'insurance' => false,
		]];
		$data['Order'] = $this->_getOrderCustomerData($data);

		$Orders->Order->expects($this->never())
			->method('saveOrder');
		$Orders->Order->expects($this->once())
			->method('checkForInvoiceCustomer')
			->will($this->returnValue(false));
		$Orders->Order->expects($this->exactly(2))
			->method('validShipping')
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The order could not be saved. Please, try again.');

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$this->assertArrayHasKey('delivery_address_id', $Orders->Order->validationErrors);
		$this->assertSame(
			$Orders->Order->validationErrors['delivery_address_id'][0],
			'A US address is required for FedEx shipments.',
			'Should match the expected validation error'
		);
	}

	/**
	 * Confirm an employee level admin can use the manager_add method.
	 *
	 * @return void
	 */
	public function testEmployeeAddGET() {
		$userId = 2;
		$customerId = 1;
		$trackingNum = '1Z9999999999999999';
		$Customers = $this->setupManagerAuth($userId);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'add',
			'employee' => true,
			'customerId' => $customerId,
		]);

		$orderCountBefore = $Customers->Order->find('count');

		$this->testAction($url, ['method' => 'get']);

		$orderCountAfter = $Customers->Order->find('count');

		$this->assertEquals($orderCountBefore, $orderCountAfter, 'Should not create or remove Order records');
		$this->assertArrayNotHasKey('Location', $this->headers, 'Should not redirect');
		$this->assertArrayHasKey('customers', $this->vars);
		$this->assertArrayHasKey('orderStatuses', $this->vars);
		$this->assertArrayHasKey('requests', $this->vars);
	}

	/**
	 * Confirm when an order to be deleted does not exist the expected exception
	 * is thrown.
	 *
	 * @return void
	 */
	public function testManagerDeleteOrderNotFound() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);

		$Order = $this->getMockForModel('Order', ['exists']);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete',
			'manager' => true,
			$orderId
		]);

		$Order->expects($this->once())
			->method('exists')
			->with(null)
			->will($this->returnValue(false));

		$this->setExpectedException('NotFoundException', 'Invalid order');
		$this->testAction($url, ['method' => 'post']);
	}

	/**
	 * Confirm that when a delete fails, the expected flash message is displayed
	 * and a redirect occurs to the customer view as $customerId is set.
	 *
	 * @return void
	 */
	public function testManagerDeleteFails() {
		$userId = 1;
		$orderId = 1;
		$customerId = 7;
		$this->setupManagerAuth($userId);

		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => [
					'exists', 'delete']
				],
			'components' => [
				'Flash' => ['set']
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'delete',
			'manager' => true,
			$orderId, $customerId
		]);

		$Orders->Order->expects($this->once())
			->method('exists')
			->with(null)
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('delete')
			->with(null)
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The order could not be deleted. Please, try again.');

		$this->testAction($url, ['method' => 'post']);
		$this->assertStringEndsWith('/manager/customers/view/' . $customerId, $this->headers['Location']);
	}

	/**
	 * Confirm when an order to be charged does not exist the expected exception
	 * is thrown.
	 *
	 * @return void
	 */
	public function testManagerChargeOrderNotFound() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);

		$Order = $this->getMockForModel('Order', ['exists']);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		]);

		$Order->expects($this->once())
			->method('exists')
			->with(null)
			->will($this->returnValue(false));

		$this->setExpectedException('NotFoundException', 'Invalid order number.');
		$this->testAction($url, ['method' => 'post']);
	}

	/**
	 * Confirm that when an order should use Fedex for rates the expected methods
	 * are called.
	 *
	 * @return void
	 */
	public function testManagerChargeFedexRateBackend() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);

		$data = [
			'Order' => [
				'orders_id' => $orderId,
			],
			'submit' => 'postage',
		];

		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => [
					'exists',
					'findOrderForCharge',
					'checkIfOrderCanBeCharged',
					'checkForInvoiceCustomer',
					'usesFedex',
				]
			],
			'methods' => [
				'_prepareChargeData',
				'getFedexRates',
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		]);

		$order = [
			'Customer' => ['canary'],
			'OrderTotal' => ['canary'],
		];

		$Orders->Order->expects($this->once())
			->method('exists')
			->with(null)
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('findOrderForCharge')
			->with($orderId)
			->will($this->returnValue($order));
		$Orders->Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->with($order)
			->will($this->returnValue(['allow' => true]));
		$Orders->expects($this->once())
			->method('_prepareChargeData')
			->with($order)
			->will($this->returnValue($order));
		$Orders->Order->expects($this->once())
			->method('usesFedex')
			->with($order)
			->will($this->returnValue(true));
		$Orders->expects($this->once())
			->method('getFedexRates')
			->with($order)
			->will($this->returnValue($order));

		$this->testAction($url, ['method' => 'post', 'data' => $data]);

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('invoiceCustomer', $this->vars);
		$this->assertArrayHasKey('allowCharge', $this->vars);
		$this->assertArrayHasKey('rates', $this->vars);
	}

	/**
	 * Confirm that when an order passes all checks but save fails the expected
	 * flash message is displayed.
	 *
	 * @return void
	 */
	public function testManagerChargeSaveOrderFails() {
		$userId = 1;
		$orderId = 1;
		$this->setupManagerAuth($userId);

		$data = [
			'Order' => [
				'orders_id' => $orderId,
			],
			'OrderShipping' => 'canary',
			'OrderStorage' => 'canary',
			'OrderInsurance' => 'canary',
			'OrderFee' => 'canary',
			'OrderRepack' => 'canary',
			'OrderBattery' => 'canary',
			'OrderReturn' => 'canary',
			'OrderMisaddressed' => 'canary',
			'OrderShipToUS' => 'canary',
			'OrderSubtotal' => 'canary',
			'submit' => 'update',
		];

		$Orders = $this->generate('Orders', [
			'models' => [
				'Order' => [
					'exists',
					'findOrderForCharge',
					'checkIfOrderCanBeCharged',
					'checkForInvoiceCustomer',
					'usesFedex',
					'saveOrderForCharge',
				]
			],
			'methods' => [
				'_prepareChargeData',
				'getFedexRates',
			],
			'components' => [
				'Flash' => ['set'],
			],
		]);

		$url = Router::url([
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'manager' => true,
		]);

		$order = [
			'Customer' => ['canary'],
			'OrderTotal' => ['canary'],
		];

		$Orders->Order->expects($this->once())
			->method('exists')
			->with(null)
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('findOrderForCharge')
			->with($orderId)
			->will($this->returnValue($order));
		$Orders->Order->expects($this->once())
			->method('checkIfOrderCanBeCharged')
			->with($order)
			->will($this->returnValue(['allow' => true]));
		$Orders->expects($this->once())
			->method('_prepareChargeData')
			->with($order)
			->will($this->returnValue($order));
		$Orders->Order->expects($this->once())
			->method('usesFedex')
			->with($order)
			->will($this->returnValue(true));
		$Orders->expects($this->never())
			->method('getFedexRates');
		$Orders->Order->expects($this->once())
			->method('saveOrderForCharge')
			->will($this->returnValue(false));
		$Orders->Flash->expects($this->once())
			->method('set')
			->with('The amounts could not be saved before charging the card. The card was not charged');

		$this->testAction($url, ['method' => 'post', 'data' => $data]);
	}

	/**
	 * Confirm an employee can use the manager_charge method.
	 *
	 * @return void
	 */
	public function testEmployeeChargeGet() {
		$orderId = 1;
		$Orders = $this->setupManagerAuth(2);
		$url = Router::url([
			'controller' => 'orders',
			'action' => 'charge',
			'id' => $orderId,
			'employee' => true,
		]);

		$this->testAction($url, ['method' => 'get']);

		$this->assertArrayHasKey('order', $this->vars);
		$this->assertArrayHasKey('Customer', $this->vars['order']);
		$this->assertArrayHasKey('customers_default_address_id', $this->vars['order']['Customer']);
		$this->assertArrayHasKey('OrderShipping', $this->vars['order']);
		$this->assertArrayHasKey('OrderStorage', $this->vars['order']);
		$this->assertArrayHasKey('OrderInsurance', $this->vars['order']);
		$this->assertArrayHasKey('OrderFee', $this->vars['order']);
		$this->assertArrayHasKey('OrderSubtotal', $this->vars['order']);
		$this->assertArrayHasKey('OrderTotal', $this->vars['order']);
		$this->assertArrayHasKey('invoiceCustomer', $this->vars);
		$this->assertArrayHasKey('allowCharge', $this->vars);
		$this->assertArrayHasKey('feeRates', $this->vars);
		$this->assertArrayHasKey('allow', $this->vars['allowCharge']);
	}

	/**
	 * Confirm that when an API charge request is made for an invoice customer,
	 * the payment `charge` method is NOT called and the expected invoice
	 * methods are.
	 *
	 * @return void
	 */
	public function testApiChargeInvoiceCustomerSuccess() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$this->setupApiAuth();

		$Orders = $this->generate('Orders', [
			'components' => [
				'Payment' => ['charge'],
				'Auth',
			],
			'methods' => [
				'emailFactory',
				'_changeOrderStatus',
				'sendStatusUpdateEmail',
				'_sendChargeResponse',
			],
			'models' => [
				'Order' =>[
					'sendStatusUpdateEmail',
					'checkForInvoiceCustomer',
					'recordInvoicePayment',
				],
			],
		]);

		$Orders->Payment
			->expects($this->never())
			->method('charge');
		$Orders->Order
			->expects($this->once())
			->method('checkForInvoiceCustomer')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('recordInvoicePayment')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('sendStatusUpdateEmail')
			->will($this->returnValue(true));
		$Orders
			->expects($this->once())
			->method('_sendChargeResponse')
			->will($this->returnValue(true));

		$id = 8;
		$data = [
			'data' => [
				'type' => 'orders',
				'attributes' => [
					'submit' => 'charge',
				],
				'relationships' => [
					'OrderStorage' => [
						'data' => [
							'value' => '999',
						],
					],
				],
			],
		];

		$result = $this->testAction("/api/orders/{$id}/charge", [
			'method' => 'patch',
			'data' => json_encode($data),
		]);
	}

	public function testApiChargeInvoiceCustomerFailure() {
		$_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
		$this->setupApiAuth();

		$Orders = $this->generate('Orders', [
			'components' => [
				'Payment' => ['charge'],
				'Auth',
			],
			'methods' => [
				'emailFactory',
				'_changeOrderStatus',
				'sendStatusUpdateEmail',
				'_sendChargeResponse',
				'logBaseSerializerException',
			],
			'models' => [
				'Order' =>[
					'sendStatusUpdateEmail',
					'checkForInvoiceCustomer',
					'recordInvoicePayment',
				],
			],
		]);

		$Orders->Payment
			->expects($this->never())
			->method('charge');
		$Orders->Order
			->expects($this->once())
			->method('checkForInvoiceCustomer')
			->will($this->returnValue(true));
		$Orders->Order
			->expects($this->once())
			->method('recordInvoicePayment')
			->will($this->returnValue(false));
		$Orders->Order
			->expects($this->never())
			->method('sendStatusUpdateEmail');
		$Orders->expects($this->never())
			->method('_sendChargeResponse');
		$Orders->expects($this->once())
			->method('logBaseSerializerException')
			->with(
				$this->identicalTo('Order was successfully invoiced, but there was a problem saving details.'),
				$this->identicalTo('Order'),
				$this->identicalTo('orders'),
				$this->anything()
			);

		$id = 8;
		$data = [
			'data' => [
				'type' => 'orders',
				'attributes' => [
					'submit' => 'charge',
				],
				'relationships' => [
					'OrderStorage' => [
						'data' => [
							'value' => '999',
						],
					],
				],
			],
		];

		$this->setExpectedException('BaseSerializerException', 'Error');

		$result = $this->testAction("/api/orders/{$id}/charge", [
			'method' => 'patch',
			'data' => json_encode($data),
		]);
	}

	/**
	 * Confirm that ehn an `orders_total_id` is not set, the default order fee
	 * is added by `addDefaultOrderFee`.
	 *
	 * @return void
	 */
	public function testPrepareChargeDataMissingOrderTotalId() {
		$order = [
			'OrderFee' => ['orders_total_id' => null],
			'Order' => ['orders_status' => 1],
		];

		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['addDefaultOrderFee'],
			],
			'methods' => [
				'enforceOrderTotalKeys',
			],
		]);

		$Orders->Order->expects($this->once())
			->method('addDefaultOrderFee')
			->will($this->returnValue('canary'));
		$Orders->expects($this->once())
			->method('enforceOrderTotalKeys')
			->will($this->returnValue($order));

		$result = $Orders->_prepareChargeData($order);

		$this->assertSame('canary', $result['OrderFee']);
	}

	/**
	 * Confirm when an order status request is made with the `notify_customer`
	 * key set the `sendStatusUpdateEmail` method is called.
	 *
	 * @return void
	 */
	public function testChangeOrderStatusNotifyCustomer() {
		$id = 1;
		$data = [
			'orders_status' => 2,
			'notify_customer' => 1,
		];

		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['markAs', 'sendStatusUpdateEmail'],
			],
		]);

		$Orders->Order->expects($this->once())
			->method('markAs')
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('sendStatusUpdateEmail')
			->with($id);

		$result = $Orders->_changeOrderStatus($id, $data);

		$this->assertTrue($result);
	}

	/**
	 * Confirm the expected exception is thrown if an address does not belong to
	 * the customer.
	 *
	 * @return void
	 */
	public function testSetAddressForOrderDoesNotBelongToCustomer() {
		$customerId = 1;
		$addressCustomersId = 99999;
		$data = ['Order' => ['delivery_address_id' => 1]];
		$address = ['Address' => ['customers_id' => $addressCustomersId]];

		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Address' => ['get'],
			],
		]);

		$Orders->Order->Customer->Address->expects($this->once())
			->method('get')
			->will($this->returnValue($address));

		$this->setExpectedException(
			'BadRequestException',
			'Requested address # ' . $addressCustomersId . ' does not belong to customer.'
		);

		$result = $Orders->_setAddressesForOrder($data, $customerId);
	}

	/**
	 * Confirm that if address validation fails, the method returns false.
	 *
	 * @return void
	 */
	public function testCheckOrSetAddressValidationFails() {
		$customerId = 1;
		$addressCustomersId = 99999;
		$data = [
			'Customer' => ['customers_default_address_id' => 'custom'],
		];

		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Address' => ['set', 'validates'],
			],
		]);

		$Orders->Order->Customer->Address->expects($this->once())
			->method('set');
		$Orders->Order->Customer->Address->expects($this->once())
			->method('validates')
			->will($this->returnValue(false));

		$Orders->data = $data;

		$result = $Orders->_checkOrSetAddress();

		$this->assertFalse($result);
	}

	/**
	 * Confirm the method can instantiate an instance of the Usps lib and call
	 * the getRates() method.
	 *
	 * @return void
	 */
	public function testGetUspsRates() {
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initUsps',
			],
		]);

		$order = ['Order' => []];

		$Usps = $this->getMockBuilder('Usps')
			->disableOriginalConstructor()
			->setMethods(['getRates'])
			->getMock();

		$Orders->expects($this->once())
			->method('initUsps')
			->will($this->returnValue($Usps));
		$Usps->expects($this->once())
			->method('getRates')
			->with($order)
			->will($this->returnValue('canary'));

		$result = $Orders->getUspsRates($order);

		$this->assertSame('canary', $result);
	}

	/**
	 * Confirm the method can instantiate an instance of the Fedex lib and call
	 * the getRate() method.
	 *
	 * @return void
	 */
	public function testGetFedexRates() {
		$Orders = $this->generate('TestOrders', [
			'methods' => [
				'initFedex',
			],
		]);

		$order = ['Order' => []];

		$Fedex = $this->getMockBuilder('Fedex')
			->disableOriginalConstructor()
			->setMethods(['getRate'])
			->getMock();

		$Orders->expects($this->once())
			->method('initFedex')
			->will($this->returnValue($Fedex));
		$Fedex->expects($this->once())
			->method('getRate')
			->with($order)
			->will($this->returnValue('canary'));

		$result = $Orders->getFedexRates($order);

		$this->assertSame('canary', $result);
	}

	/**
	 * Confirm that if `default_postal_type` is not set for a customer a mail
	 * class will not be set by this method.
	 *
	 * @return void
	 */
	public function testSetMailClass() {
		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['mailClassFromCustomer'],
			],
			'methods' => [
				'initFedex',
			],
		]);

		$customer = ['Customer' => ['default_postal_type' => null]];

		$Orders->Order->expects($this->never())
			->method('mailClassFromCustomer');

		$result = $Orders->_setMailClass($customer);

		$this->assertEmpty($result);
	}

	/**
	 * Confirm the return value is the expected string if recording the charge
	 * fails.
	 *
	 * @return void
	 */
	public function testProcessChargeRecordFails() {
		$id = 1;
		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['addressForPayment', 'recordPayment'],
			],
			'components' => [
				'Payment' => ['charge'],
			],
			'methods' => [
				'log',
			],
		]);

		$order = [
			'OrderTotal' => ['value' => 1],
			'Customer' => [],
		];
		$Orders->Order->id = $id;

		$Orders->Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue('canary'));
		$Orders->Payment->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('recordPayment')
			->will($this->returnValue(false));
		$Orders->expects($this->once())
			->method('log')
			->with(
				$this->stringContains('OrdersController::_processCharge'),
				$this->identicalTo('orders')
			);

		$result = $Orders->_processCharge(false, $order);

		$this->assertSame('not recorded', $result);
	}

	/**
	 * Confirm the return value is boolean true if recording the charge is
	 * successful.
	 *
	 * @return void
	 */
	public function testProcessChargeRecordSuccess() {
		$id = 1;
		$Orders = $this->generate('TestOrders', [
			'models' => [
				'Order' => ['addressForPayment', 'recordPayment'],
			],
			'components' => [
				'Payment' => ['charge'],
			],
			'methods' => [
				'log',
			],
		]);

		$order = [
			'OrderTotal' => ['value' => 1],
			'Customer' => [],
		];
		$Orders->Order->id = $id;

		$Orders->Order->expects($this->once())
			->method('addressForPayment')
			->will($this->returnValue('canary'));
		$Orders->Payment->expects($this->once())
			->method('charge')
			->will($this->returnValue(true));
		$Orders->Order->expects($this->once())
			->method('recordPayment')
			->will($this->returnValue(true));
		$Orders->expects($this->never())
			->method('log');

		$result = $Orders->_processCharge(false, $order);

		$this->assertTrue($result);
	}

	/**
	 * Confirm the `printZebraLabel()` method is called if configured for auto
	 * printing.
	 *
	 * @return void
	 */
	public function testMarkFailedPaymentAutoPrint() {
		$id = 1;
		$data = ['Order' => []];
		Configure::write('ZebraLabel.auto', true);
		$Orders = $this->generate('TestOrders', [
			'models' => ['Order' => ['markAs']],
			'methods' => ['printZebraLabel'],
		]);

		$Orders->expects($this->once())
			->method('printZebraLabel')
			->with($id);
		$Orders->Order->expects($this->once())
			->method('markAs')
			->will($this->returnValue(true));

		$result = $Orders->_markFailedPayment($id);

		$this->assertTrue($result);
	}

	/**
	 * Confirm beforeFilter does not enabled the Security component for requests
	 * other than `pay_manually`.
	 *
	 * @return void
	 */
	public function testBeforeFilterNotPayManually() {
		$Orders = $this->generate('Orders');

		$this->assertFalse($Orders->Components->enabled('Security'));

		$Orders->request->params['action'] = 'foobar';
		$Orders->beforeFilter();

		$this->assertFalse($Orders->Components->enabled('Security'));
		$this->assertNull($Orders->Security);
	}

	/**
	 * Confirm beforeFilter enables the Security component for `pay_manually`
	 * requests and sets the expected Security properties.
	 *
	 * @return void
	 */
	public function testBeforeFilterPayManually() {
		$Orders = $this->generate('Orders');

		$this->assertFalse($Orders->Components->enabled('Security'));

		$Orders->request->params['action'] = 'pay_manually';
		$Orders->beforeFilter();

		$this->assertTrue($Orders->Components->enabled('Security'));
		$this->assertTrue($Orders->Security->csrfUseOnce);
		$this->assertSame('blackhole', $Orders->Security->blackHoleCallback);
	}

	/**
	 * Confirm the security blackhole method sets the expected flash message
	 * and redirects.
	 *
	 * @return void
	 */
	public function testBlackhole() {
		$Orders = $this->generate('Orders', [
			'methods' => ['redirect'],
			'components' => ['Flash' => ['set']],
		]);
		$Orders->request->here = '/foo';

		$Orders->Flash->expects($this->once())
			->method('set')
			->with('Your payment is currently being processed, please check your payment status.');
		$Orders->expects($this->once())
			->method('redirect')
			->with('/foo');

		$Orders->blackhole('foo');
	}
}
