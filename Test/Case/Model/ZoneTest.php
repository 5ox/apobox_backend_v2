<?php
App::uses('Zone', 'Model');

/**
 * Zone Test Case
 *
 */
class ZoneTest extends CakeTestCase {

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
		$this->Zone = ClassRegistry::init('Zone');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Zone);

		parent::tearDown();
	}

	/**
	 * testFindZonesWithCountries
	 *
	 * @return void
	 */
	public function testFindZonesWithCountries() {
		$result = $this->Zone->findZonesWithCountries();
		$this->assertArrayHasKey('United States', $result);
		$this->assertArrayHasKey('Canada', $result);
		$this->assertContains('California', $result['United States']);
	}
}
