<?php
App::uses('InvalidInsuranceShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class: TestInvalidInsuranceShell - access to protected methods for direct
 * testing.
 */
class TestInvalidInsuranceShell extends InvalidInsuranceShell {
	public function updateOrderTotal($orderId, $amount) {
		return parent::updateOrderTotal($orderId, $amount);
	}
}

/**
 * Class InvalidInsuranceShellTest
 */
class InvalidInsuranceShellTest extends ShellTestCase {

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
		$this->Shell = $this->getMockBuilder('InvalidInsuranceShell')
			->setMethods(['_out', 'updateOrderTotal'])
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
	public function testMainNoMatches() {
		$Order = $this->getMockForModel('Order', ['find']);

		$Order->expects($this->once())
			->method('find')
			->will($this->returnValue([]));

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('No suspicious insurance_coverage records found.'),
				$this->identicalTo('info')
			);

		$this->Shell->main();

		$this->assertFalse($Order->id);
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results in dry-run mode and that the update cannot proceed
	 * because the existing `insurance_coverage` value can't be parsed as
	 * a number.
	 *
	 * @return void
	 */
	public function testMainWithMatchDryRunCoverageNotUpdated() {
		$orderId = 12345;
		$coverage = 'foo';
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			['Order' => [
				'orders_id' => $orderId,
				'insurance_coverage' => $coverage,
			]],
		];
		$Order = $this->getMockForModel('Order', ['find']);

		$Order->expects($this->once())
			->method('find')
			->will($this->returnValue($data));

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo("Order #{$orderId} cannot be automatically updated from '{$coverage}'"),
				$this->identicalTo('warning')
			);
		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(PHP_EOL . 'DRY RUN'),
				$this->identicalTo('error')
			);

		$this->Shell->main();

		$this->assertFalse($Order->id);
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results in dry-run mode and that the update can proceed,
	 * but does not actually occur.
	 *
	 * @return void
	 */
	public function testMainWithMatchDryRunCoverageUpdated() {
		$orderId = 12345;
		$coverage = '1,000';
		$fixed = '1000.00';
		$dryRun = true;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			['Order' => [
				'orders_id' => $orderId,
				'insurance_coverage' => $coverage,
			]],
		];
		$Order = $this->getMockForModel('Order', ['find', 'saveField']);

		$Order->expects($this->once())
			->method('find')
			->will($this->returnValue($data));
		$Order->expects($this->never())
			->method('saveField');

		$this->Shell->expects($this->never())
			->method('updateOrderTotal');

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo("Order #{$orderId} updated from {$coverage} to {$fixed}"),
				$this->identicalTo('comment')
			);

		$this->Shell->main();

		$this->assertSame($orderId, $Order->id);
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results in and that the update can proceed even if the
	 * call to updateOrderTotal fails.
	 *
	 * @return void
	 */
	public function testMainWithMatchDryCoverageUpdatedOrderTotalFailed() {
		$orderId = 12345;
		$coverage = '1,000';
		$fixed = '1000.00';
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			['Order' => [
				'orders_id' => $orderId,
				'insurance_coverage' => $coverage,
			]],
		];
		$Order = $this->getMockForModel('Order', ['find', 'saveField']);

		$Order->expects($this->once())
			->method('find')
			->will($this->returnValue($data));
		$Order->expects($this->once())
			->method('saveField')
			->with('insurance_coverage', $fixed)
			->will($this->returnValue(true));

		$this->Shell->expects($this->at(0))
			->method('updateOrderTotal')
			->with($orderId, $fixed)
			->will($this->returnValue(false));

		$this->Shell->expects($this->at(1))
			->method('_out')
			->with(
				$this->identicalTo(
					"Order #{$orderId} updated from {$coverage} to {$fixed} but OrderTotal update failed"
				),
				$this->identicalTo('error')
			);

		$this->Shell->main();

		$this->assertSame($orderId, $Order->id);
	}

	/**
	 * Confirm the shell outputs the expected messages if the find call
	 * returns results in and that the update fails.
	 *
	 * @return void
	 */
	public function testMainWithMatchDryCoverageUpdateFailed() {
		$orderId = 12345;
		$coverage = '1,000';
		$fixed = '1000.00';
		$dryRun = false;
		$this->Shell->params['dry-run'] = $dryRun;
		$data = [
			['Order' => [
				'orders_id' => $orderId,
				'insurance_coverage' => $coverage,
			]],
		];
		$Order = $this->getMockForModel('Order', ['find', 'saveField']);

		$Order->expects($this->once())
			->method('find')
			->will($this->returnValue($data));
		$Order->expects($this->once())
			->method('saveField')
			->with('insurance_coverage', $fixed)
			->will($this->returnValue(false));

		$this->Shell->expects($this->never())
			->method('updateOrderTotal');

		$this->Shell->expects($this->at(0))
			->method('_out')
			->with(
				$this->identicalTo(
					"Order #{$orderId} failed to update from {$coverage} to {$fixed}"
				),
				$this->identicalTo('error')
			);

		$this->Shell->main();

		$this->assertSame($orderId, $Order->id);
	}

	/**
	 * Confirm the method returns false if the updateAll call fails.
	 *
	 * @return void
	 */
	public function testUpdateOrderTotalFails() {
		$orderId = 12345;
		$amount = '50.00';
		$fee = '14.65';
		$fields = [
			'OrderTotal.text' => "'$$fee'",
			'OrderTotal.value' => "{$fee}00"
		];
		$conditions = [
			'OrderTotal.orders_id' => $orderId,
			'OrderTotal.class' => 'ot_insurance'
		];
		$Shell = $this->getMockBuilder('TestInvalidInsuranceShell')
			->setMethods(null)
			->getMock();

		$Insurance = $this->getMockForModel('Insurance', ['getFeeForCoverageAmount']);
		$OrderTotal = $this->getMockForModel('OrderTotal', ['updateAll']);

		$Insurance->expects($this->once())
			->method('getFeeForCoverageAmount')
			->will($this->returnValue($fee));

		$OrderTotal->expects($this->once())
			->method('updateAll')
			->with(
				$this->identicalTo($fields),
				$this->identicalTo($conditions)
			)
			->will($this->returnValue(false));

		$result = $Shell->updateOrderTotal($orderId, $amount);

		$this->assertFalse($result);
	}

	/**
	 * Confirm the method returns the expected value if everything works.
	 *
	 * @return void
	 */
	public function testUpdateOrderTotalSuccess() {
		$orderId = 12345;
		$amount = '50.00';
		$fee = '14.65';
		$fields = [
			'OrderTotal.text' => "'$$fee'",
			'OrderTotal.value' => "{$fee}00"
		];
		$conditions = [
			'OrderTotal.orders_id' => $orderId,
			'OrderTotal.class' => 'ot_insurance'
		];
		$Shell = $this->getMockBuilder('TestInvalidInsuranceShell')
			->setMethods(null)
			->getMock();

		$Insurance = $this->getMockForModel('Insurance', ['getFeeForCoverageAmount']);
		$OrderTotal = $this->getMockForModel('OrderTotal', ['updateAll', 'updateTotal']);

		$Insurance->expects($this->once())
			->method('getFeeForCoverageAmount')
			->will($this->returnValue($fee));

		$OrderTotal->expects($this->once())
			->method('updateAll')
			->with(
				$this->identicalTo($fields),
				$this->identicalTo($conditions)
			)
			->will($this->returnValue(true));
		$OrderTotal->expects($this->once())
			->method('updateTotal')
			->with($this->identicalTo($orderId))
			->will($this->returnValue('canary'));

		$result = $Shell->updateOrderTotal($orderId, $amount);

		$this->assertSame('canary', $result);
	}
}
