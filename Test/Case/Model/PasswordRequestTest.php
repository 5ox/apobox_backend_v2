<?php
App::uses('PasswordRequest', 'Model');

/**
 * PasswordRequest Test Case
 *
 */
class PasswordRequestTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.password_request',
		'app.customer',
		'app.address',
		'app.zone',
		'app.order',
		'app.order_status',
		'app.order_status_history',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->PasswordRequest = ClassRegistry::init('PasswordRequest');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->PasswordRequest);

		parent::tearDown();
	}

	/**
	 * testPlaceholder method
	 *
	 * @return	void
	 */
	public function testDeleteExpired() {
		$this->PasswordRequest = $this->getMockForModel('PasswordRequest', array('getDatetime'));
		$this->PasswordRequest->expects($this->once())
			->method('getDatetime')
			->will($this->returnValue(date_create('2015-05-05 09:00:00')));
		$countBefore = $this->PasswordRequest->find('count');

		$this->PasswordRequest->deleteExpired();

		$countAfter = $this->PasswordRequest->find('count');

		$this->assertNotEquals($countBefore, $countAfter, 'Test should delete requests');
		$this->assertEquals(1, $countAfter, 'Too many or too little requests deleted. Check fixture data.');
	}

	/**
	 * Confirm that the validFor() method sets the validFor property correctly
	 * when supplied with an arguement.
	 *
	 * @return	void
	 */
	public function testValidForwithArg() {
		$time = 'foo';
		$this->assertNotEquals($time, $this->PasswordRequest->validFor);
		$this->PasswordRequest->validFor($time);
		$this->assertEquals($time, $this->PasswordRequest->validFor);
	}

}
