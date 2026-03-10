<?php
App::uses('OrderFee', 'Model');

/**
 * OrderFee Test Case
 *
 */
class OrderFeeTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testGetFee method
	 *
	 * @param int Weight in ounces
	 * @param float Expected fee
	 * @return	void
	 * @dataProvider provideGetFee
	 */
	public function testGetFee($weight, $expected) {
		$feeSchedule = [
			0 => 2.95,
			5 => 3.95,
			10 => 4.95,
			15 => 5.95,
		];
		$OrderFee = $this->getMockForModel('OrderFee', ['feeSchedule']);
		$OrderFee->expects($this->once())
			->method('feeSchedule')
			->will($this->returnValue($feeSchedule));

		$this->assertEquals($expected, $OrderFee->getFee($weight));
	}

	/**
	 * Data provider for testGetFee
	 *
	 * @return array
	 */
	public function provideGetFee() {
		return [
			[
				0,
				2.95,
			],
			[
				1,
				2.95,
			],
			[
				4,
				2.95,
			],
			[
				5,
				3.95,
			],
			[
				6,
				3.95,
			],
			[
				9,
				3.95
			],
			[
				9,
				3.95
			],
			[
				10,
				4.95
			],
			[
				11,
				4.95
			],
			[
				14,
				4.95
			],
			[
				15,
				5.95
			],
		];
	}
}
