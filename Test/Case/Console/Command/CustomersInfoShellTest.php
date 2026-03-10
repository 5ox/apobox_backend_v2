<?php
App::uses('CustomersInfoShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class CustomersInfoShellTest
 */
class CustomersInfoShellTest extends ShellTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [];

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Shell = $this->getMockBuilder('CustomersInfoShell')
			->setMethods(['_out'])
			->getMock();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Dispatch, $this->Shell);
	}

	/**
	 * Confirm the shell outputs the expected messages if findMissingCustomersInfo
	 * returns no results.
	 *
	 * @return void
	 */
	public function testAddDateNoCustomers() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$Customer = $this->getMockForModel('Customer', ['findMissingCustomersInfo']);

		$Customer->expects($this->once())
			->method('findMissingCustomersInfo')
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No customer info records in need of updating.'),
				$this->identicalTo('info')
			);

		$this->Shell->add_date();
	}

	/**
	 * Confirm the shell outputs the expected messages if findMissingCustomersInfo
	 * returns results in dry-run mode and that no customers accounts are updated
	 * by updateAccountCreated().
	 *
	 * @return void
	 */
	public function testAddDateWithCustomersDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			'12345' => '2015-07-02 13:07:07',
			'67890' => '2016-08-02 13:07:07',
		];
		$Customer = $this->getMockForModel('Customer', ['findMissingCustomersInfo']);
		$CustomersInfo = $this->getMockForModel('CustomersInfo', ['updateAccountCreated']);

		$Customer->expects($this->once())
			->method('findMissingCustomersInfo')
			->will($this->returnValue($data));

		$CustomersInfo->expects($this->never())
			->method('updateAccountCreated');

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('DRY RUN: ' . count($data) . ' customers would be updated.'),
				$this->identicalTo('warning')
			);

		$this->Shell->add_date();
	}

	/**
	 * Confirm the shell outputs the expected messages if findMissingCustomersInfo
	 * returns results and that customers accounts are updated by
	 * updateAccountCreated().
	 *
	 * @return void
	 */
	public function testAddDateWithCustomersSuccess() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			'12345' => '2015-07-02 13:07:07',
			'67890' => '2016-08-02 13:07:07',
		];
		$Customer = $this->getMockForModel('Customer', ['findMissingCustomersInfo']);
		$CustomersInfo = $this->getMockForModel('CustomersInfo', ['updateAccountCreated']);

		$Customer->expects($this->once())
			->method('findMissingCustomersInfo')
			->will($this->returnValue($data));

		$CustomersInfo->expects($this->once())
			->method('updateAccountCreated')
			->with($data)
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo(count($data) . ' customers sucessfully updated with account created dates.'),
				$this->identicalTo('info')
			);

		$this->Shell->add_date();
	}

	/**
	 * Confirm the shell outputs the expected messages if findMissingCustomersInfo
	 * returns results and that customers accounts are updated by
	 * updateAccountCreated() which in turn produces errors.
	 *
	 * @return void
	 */
	public function testAddDateWithCustomersErrors() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$errorId = '12345';
		$data = [
			$errorId => '2015-07-02 13:07:07',
			'67890' => '2016-08-02 13:07:07',
		];
		$Customer = $this->getMockForModel('Customer', ['findMissingCustomersInfo']);
		$CustomersInfo = $this->getMockForModel('CustomersInfo', ['updateAccountCreated']);

		$Customer->expects($this->once())
			->method('findMissingCustomersInfo')
			->will($this->returnValue($data));

		$CustomersInfo->expects($this->once())
			->method('updateAccountCreated')
			->with($data)
			->will($this->returnValue([$errorId]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('There was an error updating account created date for customer ' . $errorId . '.'),
				$this->identicalTo('error')
			);

		$this->Shell->add_date();
	}
}
