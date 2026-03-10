<?php
App::uses('Country', 'Model');

/**
 * Country Test Case
 *
 */
class CountryTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.country',
		'app.zone',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->Country = ClassRegistry::init('Country');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Country);
		parent::tearDown();
	}

	/**
	 * Test that countries with zones are returned.
	 *
	 * @return void
	 */
	public function testFindWithZones() {
		$result = $this->Country->findWithZones();
		$this->assertArrayHasKey(38, $result);
		$this->assertArrayHasKey(223, $result);
		$this->assertArrayNotHasKey(250, $result);
		$this->assertArrayNotHasKey(2, $result);
	}
}
