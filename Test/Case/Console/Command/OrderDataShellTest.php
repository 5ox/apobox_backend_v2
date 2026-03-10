<?php
App::uses('OrderDataShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class OrderDataShellTest
 */
class OrderDataShellTest extends ShellTestCase {

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
		$this->Shell = $this->getMockBuilder('OrderDataShell')
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
	 * Confirm the shell outputs the expected messages if the record find call
	 * returns no results.
	 *
	 * @return void
	 */
	public function testPurgeNoRecords() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$this->Shell->args[0] = 'fedex-zpl';
		$OrderData = $this->getMockForModel('OrderData', ['find', 'delete']);

		$OrderData->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('list'),
				$this->isType('array')
			)
			->will($this->returnValue([]));
		$OrderData->expects($this->never())
			->method('delete');

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No records are in need of purging.'),
				$this->identicalTo('info')
			);

		$this->Shell->purge();
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results in dry-run mode and that delete is not called.
	 *
	 * @return void
	 */
	public function testPurgeWithRecordsDryRun() {
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$this->Shell->args[0] = 'fedex-zpl';
		$id = 1;
		$orderId = 12345;
		$data = [
			$id => $orderId,
		];
		$OrderData = $this->getMockForModel('OrderData', ['find', 'delete']);

		$OrderData->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('list'),
				$this->isType('array')
			)
			->will($this->returnValue($data));
		$OrderData->expects($this->never())
			->method('delete');

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo("Removing id {$id} for order #{$orderId}"),
				$this->identicalTo('info')
			);
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(PHP_EOL . 'DRY RUN'),
				$this->identicalTo('error')
			);

		$this->Shell->purge();
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results and that delete is called.
	 *
	 * @return void
	 */
	public function testPurgeWithRecords() {
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$this->Shell->args[0] = 'fedex-zpl';
		$id = 1;
		$orderId = 12345;
		$data = [
			$id => $orderId,
		];
		$OrderData = $this->getMockForModel('OrderData', ['find', 'delete']);

		$OrderData->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('list'),
				$this->isType('array')
			)
			->will($this->returnValue($data));
		$OrderData->expects($this->once())
			->method('delete')
			->with(
				$this->identicalTo($id),
				$this->identicalTo(false)
			);

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo("Removing id {$id} for order #{$orderId}"),
				$this->identicalTo('info')
			);

		$this->Shell->purge();
	}
}
