<?php
App::uses('LogTrait', 'Lib/Log');

class TestLogTrait {
	use LogTrait;
}

class LogTraitTest extends CakeTestCase {
	public function setUp() {
		$this->Subject = $this->getMockBuilder('TestLogTrait')
			->setMethods(['cakeLogWrite'])
			->getMock();
	}

	/**
	 * Test the log method.
	 *
	 * @dataProvider provideTestLog
	 * @return void
	 */
	public function testLog($input, $expected) {
		$this->Subject->expects($this->once())
			->method('cakeLogWrite')
			->will($this->returnArgument(1));

		$this->assertSame($expected, $this->Subject->log($input));
	}

	/**
	 * Data provider for testLog.
	 *
	 * @return array
	 */
	public function provideTestLog() {
		return [
			[
				'foo',
				'foo',
			],
			[
				['array', 'bar'],
				"Array\n(\n    [0] => array\n    [1] => bar\n)\n",
			],
		];
	}

	/**
	 * Integration test logging.
	 *
	 * Warning: This is a useful test, but is polluting (it generates output)
	 * so is best left commented out unless needed to run manually. If more of
	 * these type of tests get created, they could be moved into their own test
	 * suite and ran manually.
	 *
	 * @return void
	 */
	// public function testLogIntegration() {
	// 	$this->Subject = new TestLogTrait();
	//
	// 	$this->assertTrue($this->Subject->log('foo'));
	// 	$this->assertTrue($this->Subject->log(['array', 'bar']));
	// }
}
