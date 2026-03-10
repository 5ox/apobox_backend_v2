<?php
App::uses('CakeTestCase', 'TestSuite');
App::uses('QueueAppEmailTask', 'Console/Command/Task');

/**
 * Class QueueAppEmailTaskTest
 */
class QueueAppEmailTaskTest extends CakeTestCase {

	/**
	 * setUp test case
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->QueueAppEmailTask = new QueueAppEmailTask;

		$this->validMethods = [
			'sendForgotPassword',
			'sendStatusUpdate',
			'sendShipped',
		];
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->QueueAppEmailTask);
	}

	/**
	 * Confirm that if any of the required $data keys are missing the method
	 * will abort and return false.
	 *
	 * @param array $data The data to test with
	 * @param mixed $assert What to assert: True | False
	 * @param string $msg The test msg
	 * @return void
	 * @dataProvider provideTestRunMissingRequiredKeys
	 */
	public function testRunMissingRequiredKeys($data, $assert, $msg = '') {
		$result = $this->QueueAppEmailTask->run($data);
		$this->{'assert' . $assert}($result);
	}

	/**
	 * provideTestRunMissingRequiredKeys
	 *
	 * @return array
	 */
	public function provideTestRunMissingRequiredKeys() {
		return [
			[
				[
					'foo' => 'bar',
					'recipient' => 'foo@bar.com',
				],
				'False',
				'should be false if key `method` is not set',
			],
			[
				[
					'method' => 'foo',
					'bar' => 'baz',
				],
				'False',
				'should be false if key `recipient` is not set',
			],
		];
	}

	/**
	 * Confirm that when the required keys are present but the supplied `method`
	 * key is invalid the task will abort and return false.
	 *
	 * @param array $data The data to test with
	 * @param mixed $assert What to assert: True | False
	 * @param string $msg The test msg
	 * @return void
	 * @dataProvider provideTestRunInvalidMethod
	 */
	public function testRunInvalidMethod($data, $assert, $msg = '') {
		$AppEmail = $this->getMockBuilder('AppEmail')->setMethods($this->validMethods)->getMock();
		$Email = $this->getMock('QueueAppEmailTask', ['emailFactory']);

		$Email->expects($this->once())
			->method('emailFactory')
			->will($this->returnValue($AppEmail));

		$result = $Email->run($data);
		$this->{'assert' . $assert}($result);
	}

	/**
	 * provideTestRunMissingRequiredKeys
	 *
	 * @return array
	 */
	public function provideTestRunInvalidMethod() {
		return [
			[
				[
					'method' => 'foo',
					'recipient' => 'foo@bar.com',
				],
				'False',
				'should be false if `method` value is not valid',
			],
			[
				[
					'method' => 'bar',
					'recipient' => 'foo@bar.com',
				],
				'False',
				'should be false if `method` value is not valid',
			],
		];
	}

	/**
	 * Confirm that the `run` method will return the expected result based on
	 * the underlying return value of the the called App::Email method.
	 *
	 * @param array $data The data to test with
	 * @param mixed $expected The return value the mocked $AppEmail will return
	 * @param mixed $assert What to assert: True | False
	 * @param string $msg The test msg
	 * @return void
	 * @dataProvider provideTestRunCompletes
	 */
	public function testRunCompletes($data, $expected, $assert, $msg = '') {
		$AppEmail = $this->getMockBuilder('AppEmail')->setMethods($this->validMethods)->getMock();
		$Email = $this->getMock('QueueAppEmailTask', ['emailFactory']);

		$Email->expects($this->once())
			->method('emailFactory')
			->will($this->returnValue($AppEmail));

		$AppEmail->expects($this->once())
			->method($data['method'])
			->with(
				$data['recipient'],
				isset($data['vars']) ? $data['vars'] : [],
				isset($data['subject']) ? $data['subject'] : null
			)
			->will($this->returnValue($expected));

		$result = $Email->run($data);
		$this->{'assert' . $assert}($result);
	}

	/**
	 * provideTestRunCompletes
	 *
	 * @return array
	 */
	public function provideTestRunCompletes() {
		return [
			[
				[
					'method' => 'sendForgotPassword',
					'recipient' => 'foo@bar.com',
				],
				false,
				'False',
				'should be false if $email->[method] returns false',
			],
			[
				[
					'method' => 'sendForgotPassword',
					'recipient' => 'foo@bar.com',
				],
				true,
				'True',
				'should be true if $email->[method] returns true',
			],
			[
				[
					'method' => 'sendForgotPassword',
					'recipient' => 'foo@bar.com',
					'subject' => 'Foo Bar',
				],
				false,
				'False',
				'should be false if $email->[method] returns false with subject set',
			],
			[
				[
					'method' => 'sendForgotPassword',
					'recipient' => 'foo@bar.com',
					'subject' => 'Foo Bar',
				],
				true,
				'True',
				'should be true if $email->[method] returns true with subject set',
			],
			[
				[
					'method' => 'sendForgotPassword',
					'recipient' => 'foo@bar.com',
					'vars' => ['foo' => 'bar'],
				],
				true,
				'True',
				'should be true if $email->[method] returns true with vars[] set',
			],
		];
	}

	/**
	 * Confirm `emailFactory()` can return an instance of the `AppEmail` class.
	 *
	 * @return void
	 */
	public function testEmailFactory() {
		$this->assertInstanceOf('AppEmail', $this->QueueAppEmailTask->emailFactory());
	}
}
