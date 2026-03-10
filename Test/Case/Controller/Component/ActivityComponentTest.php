<?php
App::uses('ComponentCollection', 'Controller');
App::uses('Component', 'Controller');
App::uses('ActivityComponent', 'Controller/Component');
App::uses('CustomersInfo', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');

/**
 * Class: ASDF145ControllerTest
 */
class ASDF145ControllerTest extends Controller {
	public $paginate = null;
}

/**
 * Class: TestActivityComponent
 */
class TestActivityComponent extends ActivityComponent {
	public function lastLoginCount($id) {
		return parent::lastLoginCount($id);
	}
}

/**
 * Class: ActivityComponentTest
 */
class ActivityComponentTest extends CakeTestCase {

	public $fixtures = array(
		'app.customer',
		'app.customers_info',
	);

	public function setUp() {
		parent::setUp();
		$this->testIp = '123.456.123.456';
		$Collection = new ComponentCollection();
		$this->Activity = $this->getMockBuilder('ActivityComponent')
			->setConstructorArgs([$Collection])
			->setMethods(['log'])
			->getMock();
		$this->componentStartup();
		$this->CustomersInfo = ClassRegistry::init('CustomersInfo');
	}

	public function tearDown() {
		unset($this->Activity);

		parent::tearDown();
	}

	protected function componentStartup() {
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		$this->Controller = new ASDF145ControllerTest($CakeRequest, $CakeResponse);
		$this->Controller->request = $this->getMock('CakeRequest', ['clientIp']);
		$this->Controller->request->expects($this->any())
			->method('clientIp')
			->will($this->returnValue($this->testIp));
		$this->Activity->initialize($this->Controller);
		$this->Activity->startup($this->Controller);
	}

	public function testRecordRegister() {
		$userId = 2;

		$result = $this->Activity->record('register', $userId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_created'];

		$this->assertTrue((bool)$result);
		$this->assertSame($this->testIp, $after['IP_signup']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordLoginHappy() {
		$userId = 1;

		$result = $this->Activity->record('login', $userId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_of_last_logon'];

		$this->assertTrue((bool)$result);
		$this->assertSame('2', $after['customers_info_number_of_logons']);
		$this->assertSame($this->testIp, $after['IP_lastlogon']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordLoginNew() {
		$userId = 2;

		$result = $this->Activity->record('login', $userId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_of_last_logon'];

		$this->assertTrue((bool)$result);
		$this->assertSame('3', $after['customers_info_number_of_logons']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordEditDefault() {
		$userId = 1;

		$result = $this->Activity->record('edit', $userId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_last_modified'];

		$this->assertTrue((bool)$result);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordEditAddress() {
		$userId = 1;

		$result = $this->Activity->record('edit', $userId, 'addresses');

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_last_modified'];

		$this->assertTrue((bool)$result);
		$this->assertSame($this->testIp, $after['IP_addressbook_update']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordEditCc() {
		$userId = 1;

		$result = $this->Activity->record('edit', $userId, 'payment_info');

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_last_modified'];

		$this->assertTrue((bool)$result);
		$this->assertSame($this->testIp, $after['IP_cc_update']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordEditNew() {
		$userId = 2;

		$result = $this->Activity->record('edit', $userId, 'payment_info');

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_last_modified'];

		$this->assertTrue((bool)$result);
		$this->assertSame($this->testIp, $after['IP_cc_update']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordInvalidActivity() {
		$this->Activity->expects($this->once())
			->method('log')
			->with(
				$this->stringContains('ActivityComponent::record'),
				$this->identicalTo('customers')
			);
		$this->assertFalse($this->Activity->record('invalid', 1));
	}

	public function testRecordClose() {
		$userId = 1;

		$result = $this->Activity->record('close', $userId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];
		$time = $after['customers_info_date_account_closed'];

		$this->assertTrue((bool)$result);
		$this->assertSame('1', $after['customers_info_number_of_logons']);
		$this->assertLessThanOrEqual(time(), strtotime($time));
		$this->assertGreaterThan(strtotime('1 minute ago'), strtotime($time));
	}

	public function testRecordSource() {
		$userId = 2;
		$sourceId = 5;

		$result = $this->Activity->record('source', $userId, $sourceId);

		$this->CustomersInfo->id = $userId;
		$after = $this->CustomersInfo->read(null, $userId)['CustomersInfo'];

		$this->assertTrue((bool)$result);
		$this->assertSame($sourceId, (int)$after['customers_info_source_id']);
	}

	/**
	 * Confirm the method returns `0` if no records are found.
	 *
	 * @return void
	 */
	public function testLastLoginCountEmpty() {
		$id = 9999;
		$this->Activity = $this->getMockBuilder('TestActivityComponent')
			->setConstructorArgs([new ComponentCollection])
			->setMethods(null)
			->getMock();
		$this->componentStartup();

		$result = $this->Activity->lastLoginCount($id);

		$this->assertSame(0, $result);
	}
}
