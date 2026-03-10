<?php
App::uses('ExpiringCardsShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class ExpiringCardsShellTest
 */
class ExpiringCardsShellTest extends ShellTestCase {

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
		$this->Shell = $this->getMockBuilder('ExpiringCardsShell')
			->setMethods(['_out', 'taskFactory'])
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
	 * Confirm taskFactory can return an instance of the QueuedTask class.
	 *
	 * @return void
	 */
	public function testTaskFactory() {
		$Shell = new ExpiringCardsShell();
		$this->assertInstanceOf('QueuedTask', $Shell->taskFactory());
	}

	/**
	 * Confirm the shell outputs the expected messages if findExpiringCreditCards
	 * returns no results.
	 *
	 * @return void
	 */
	public function testMainNoCustomers() {
		$Customer = $this->getMockForModel('Customer', ['findExpiringCreditCards']);

		$Customer->expects($this->once())
			->method('findExpiringCreditCards')
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No expiring customer cards found.'),
				$this->identicalTo('info')
			);

		$this->Shell->main();
	}

	/**
	 * Confirm the shell outputs the expected messages if findExpiringCreditCards
	 * returns results and that an email job is submitted to the queue.
	 *
	 * @return void
	 */
	public function testMainWithCustomers() {
		$customersId = '12345';
		$data = [
			$customersId => [
				'customers_email_address' => 'foo@bar.com',
				'customers_fullname' => 'Foo Bar',
			],
		];
		$Customer = $this->getMockForModel('Customer', ['findExpiringCreditCards']);

		$Task = $this->getMockBuilder('QueuedTask')
			->setMethods(['createJob'])
			->getMock();

		$Customer->expects($this->once())
			->method('findExpiringCreditCards')
			->will($this->returnValue($data));

		$Task->expects($this->once())
			->method('createJob')
			->with(
				$this->identicalTo('AppEmail'),
				$this->isType('array'),
				$this->isNull(),
				$this->identicalTo('ExpiringCardsShell::sendCreditCardExpires'),
				$data[$customersId]['customers_email_address']
			);

		$this->Shell->expects($this->at(0))
			->method('taskFactory')
			->will($this->returnValue($Task));

		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					'Credit card expiration notice sent to customer #' .
					$customersId . ' (' . $data[$customersId]['customers_email_address'] . ').'
				),
				$this->identicalTo('')
			);

		$this->Shell->main();
	}
}
