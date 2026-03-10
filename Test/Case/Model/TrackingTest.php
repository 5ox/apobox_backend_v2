<?php
App::uses('Tracking', 'Model');

/**
 * Tracking Test Case
 *
 */
class TrackingTest extends CakeTestCase {

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
	 * testPlaceholder method
	 *
	 * @return	void
	 */
	public function testPlaceholder() {
		$this->assertInternalType('string', 'No methods to test - dummy test for code coverage');
	}
}
