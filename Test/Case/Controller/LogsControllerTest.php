<?php
App::uses('LogsController', 'Controller');

/**
 * LogsController Test Case
 *
 */
class LogsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = [];

	/**
	 * Confirm that if a log file can't be found the method should throw the
	 * expected exception.
	 *
	 * @return void
	 */
	public function testManagerViewLogNotFound() {
		$Logs = $this->generate('Logs', [
			'methods' => [
				'getFile',
			],
		]);
		$File = $this->getMockBuilder('File', ['exists'])
			->setConstructorArgs(['testing'])
			->getMock();
		$Logs->expects($this->once())
			->method('getFile')
			->will($this->returnValue($File));
		$url = Router::url([
			'controller' =>'logs',
			'action' => 'view',
			'manager' => true,
		]);
		$this->testAction($url);
		$this->assertSame('email', $this->vars['log']);
		$this->assertSame('There is currently no email log data to display.', $this->vars['logFile']);
	}

	/**
	 * Confirm that the expected methods are called, no exception is thrown, and
	 * the correct vars are set.
	 *
	 * @return void
	 */
	public function testManagerViewLogFound() {
		$Logs = $this->generate('Logs', [
			'methods' => [
				'getFile',
			],
		]);
		$File = $this->getMockBuilder('File', ['exists'])
			->setConstructorArgs(['testing'])
			->getMock();
		$Logs->expects($this->once())
			->method('getFile')
			->will($this->returnValue($File));
		$File->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		$url = Router::url([
			'controller' =>'logs',
			'action' => 'view',
			'manager' => true,
		]);
		$this->testAction($url);
		$this->assertSame('email', $this->vars['log']);
	}
}
