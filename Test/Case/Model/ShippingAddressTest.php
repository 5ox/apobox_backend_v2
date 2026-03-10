<?php
App::uses('ShippingAddress', 'Model');

/**
 * ShippingAddress Test Case
 *
 */
class ShippingAddressTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.address',
		'app.customer',
		'app.zone',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->ShippingAddress = ClassRegistry::init('ShippingAddress');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->ShippingAddress);

		parent::tearDown();
	}

	/**
	 * Test that Address validation is properly merged during __construct()
	 */
	public function testValidateIsCorrect() {
		$this->assertArrayHasKey('customers_id', $this->ShippingAddress->validate);
		$this->assertArrayHasKey('inList', $this->ShippingAddress->validate['entry_city']);
		$this->assertArrayHasKey('notBlank', $this->ShippingAddress->validate['entry_city']);
		$this->assertArrayHasKey('inList', $this->ShippingAddress->validate['entry_zone_id']);
		$this->assertArrayHasKey('notBlank', $this->ShippingAddress->validate['entry_zone_id']);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testFindFirst() {
		$expectedId = '4';
		$result = $this->ShippingAddress->find('first');

		$this->assertArrayHasKey('ShippingAddress', $result);
		$this->assertEqual($expectedId, $result['ShippingAddress']['address_book_id']);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testFindAll() {
		$expectedCount = 8;
		$result = $this->ShippingAddress->find('all');

		$this->assertArrayHasKey('0', $result);
		$this->assertCount($expectedCount, $result);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testFindCount() {
		$expected = 8;
		$result = $this->ShippingAddress->find('count');

		$this->assertEqual($expected, $result);
	}

	/**
	 *
	 *
	 * @return	void
	 */
	public function testFindList() {
		$result = $this->ShippingAddress->find('list');

		$this->assertArrayHasKey(4, $result);
		$this->assertArrayHasKey(5, $result);
		$this->assertArrayHasKey(7, $result);
	}

}
