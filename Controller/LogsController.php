<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

/**
 * Logs Controller
 *
 */
class LogsController extends AppController {

	/**
	 * Models
	 *
	 * @var	array
	 */
	public $uses = [];

	/**
	 * logDir
	 *
	 * @var mixed
	 */
	protected $_logDir = LOGS;

	/**
	 * Reads in and sets the specified log file.
	 *
	 * @param string $log The log
	 * @return void
	 */
	public function manager_view($log = 'email') {
		$file = $this->getFile($this->_logDir . $log . '.log');

		$logFile = 'There is currently no email log data to display.';
		if ($file->exists()) {
			$logFile = $file->read();
		}

		$this->set(compact('log', 'logFile'));
	}

	/**
	 * Instantiate a new File object
	 *
	 * @param string $file The pull path to a file
	 * @return object A file object
	 * @codeCoverageIgnore It's a wrapper for the Utility::File
	 */
	protected function getFile($file) {
		return new File($file);
	}
}
