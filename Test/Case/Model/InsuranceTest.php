<?php
App::uses('Insurance', 'Model');

/**
 * Insurance Test Case
 *
 */
class InsuranceTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.insurance',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->Insurance = ClassRegistry::init('Insurance');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Insurance);

		parent::tearDown();
	}

	/**
	 * Confirm the correct coverage amount per range is returned depending
	 * on supplied value.
	 *
	 * @dataProvider provideGetFeeForCoverageAmount
	 * @return void
	 */
	public function testGetFeeForCoverageAmount($expected, $amount) {
		$result = $this->Insurance->getFeeForCoverageAmount($amount);
		$this->assertEquals($expected, $result);
	}

	public function provideGetFeeForCoverageAmount() {
		return [
			[1.75, .01],
			[1.75, 50],
			[2.25, 50.01],
			[2.25, 100],
			[2.75, 100.01],
			[2.75, 200],
			[4.70, 200.01],
			[4.70, 300],
			[5.70, 300.01],
			[5.70, 400],
			[6.70, 400.01],
			[6.70, 456.78],
			[6.70, 500],
			[7.70, 500.01],
			[7.70, 600],
			[11.70, 1000],
			[false, 1000.01],
			[0.00, 0.00],
			[1.75, '25.25'],
			[false, 'foo'],
			[11.70, '1,000'],
			[false, '$1,000'],
		];
	}

	/**
	 * testGetFeeForCoverage method
	 *
	 * @return	void
	 */
	public function testGetFeeForCoverageAmountWhenTableLacksIntergrity() {
		// Create a gap between a records `amount_to` and the records with the next highest range's `amount_min`.
		$this->Insurance->id = 5;
		$this->Insurance->saveField('amount_to', 399.99);

		$expected = false;
		$result = $this->Insurance->getFeeForCoverageAmount(400.00);

		$this->assertSame($expected, $result);
	}


	/**
	 * testCheckIntegrity method
	 *
	 * @return	void
	 */
	public function testCheckIntegrity() {
		$expected = true;
		$result = $this->Insurance->checkIntegrity();

		$this->assertSame($expected, $result);
	}

	/**
	 * testCheckIntegrityWithBadData method
	 *
	 * @return	void
	 */
	public function testCheckIntegrityWithBadData() {
		// Create a gap between a records `amount_to` and the records with the next highest range's `amount_from`.
		$this->Insurance->id = 5;
		$this->Insurance->set('amount_to', 350.00);
		$this->Insurance->save();

		$expected = false;
		$result = $this->Insurance->checkIntegrity();

		$this->assertSame($expected, $result);
	}

}
