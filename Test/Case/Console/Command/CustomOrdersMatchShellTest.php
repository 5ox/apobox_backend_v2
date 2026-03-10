<?php
App::uses('CustomOrdersMatchShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class CustomOrdersMatchShellTest
 */
class CustomOrdersMatchShellTest extends ShellTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [];

	/**
	 * setUp test case
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Shell = $this->getMockBuilder('CustomOrdersMatchShell')
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
	 * Confirm the shell outputs the expected messages if findMatchingRequests
	 * returns results.
	 *
	 * @return void
	 */
	public function testOrdersNotEmpty() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$requests = [
			1 => 9,
			2 => 'foo',
			'string' => 'bar',
		];

		$CustomPackageRequest = $this->getMockForModel('CustomPackageRequest', [
			'findMatchingRequests'
		]);
		$CustomPackageRequest->expects($this->once())
			->method('findMatchingRequests')
			->with($this->identicalTo($dryRun))
			->will($this->returnValue($requests));

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo('Update ID 1 with order number 9'),
				$this->identicalTo('comment')
			);
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo('Update ID 2 with order number foo'),
				$this->identicalTo('comment')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo('Update ID string with order number bar'),
				$this->identicalTo('comment')
			);

		$this->Shell->orders();
	}

	/**
	 * Confirm the shell outputs the expected messages if findMatchingRequests
	 * returns no results.
	 *
	 * @return void
	 */
	public function testOrdersEmpty() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$requests = [ ];

		$CustomPackageRequest = $this->getMockForModel('CustomPackageRequest', [
			'findMatchingRequests'
		]);
		$CustomPackageRequest->expects($this->once())
			->method('findMatchingRequests')
			->with($this->identicalTo($dryRun))
			->will($this->returnValue($requests));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No matching records found.'),
				$this->identicalTo('info')
			);

		$this->Shell->orders();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndUpdateStatus
	 * returns results.
	 *
	 * @return void
	 */
	public function testStatusNotEmpty() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$requests = [
			1 => 9,
			2 => 'foo',
			'string' => 'bar',
		];

		$CustomPackageRequest = $this->getMockForModel('CustomPackageRequest', [
			'findAndUpdateStatus'
		]);
		$CustomPackageRequest->expects($this->once())
			->method('findAndUpdateStatus')
			->with($this->identicalTo($dryRun))
			->will($this->returnValue($requests));

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo('Update ID 1 to order status 9'),
				$this->identicalTo('comment')
			);
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo('Update ID 2 to order status foo'),
				$this->identicalTo('comment')
			);
		$this->Shell->expects($this->at(2))
			->method('_out')
			->with(
				$this->identicalTo('Update ID string to order status bar'),
				$this->identicalTo('comment')
			);

		$this->Shell->status();
	}

	/**
	 * Confirm the shell outputs the expected messages if findAndUpdateStatus
	 * returns no results.
	 *
	 * @return void
	 */
	public function testStatusEmpty() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$requests = [];

		$CustomPackageRequest = $this->getMockForModel('CustomPackageRequest', [
			'findAndUpdateStatus'
		]);
		$CustomPackageRequest->expects($this->once())
			->method('findAndUpdateStatus')
			->with($this->identicalTo($dryRun))
			->will($this->returnValue($requests));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No matching records found.'),
				$this->identicalTo('info')
			);

		$this->Shell->status();
	}
}
