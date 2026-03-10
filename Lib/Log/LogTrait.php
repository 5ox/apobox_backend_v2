<?php
App::uses('CakeLog', 'Log');

trait LogTrait {

	/**
	 * Convenience method to write a message to CakeLog. See CakeLog::write()
	 * for more information on writing to logs.
	 *
	 * @param string $message Log message
	 * @param int $type Error type constant. Defined in app/Config/core.php.
	 * @param null|string|array $scope The scope(s) a log message is being created in.
	 *    See CakeLog::config() for more information on logging scopes.
	 * @return bool Success of log write
	 */
	public function log($message, $type = LOG_ERR, $scope = null) {
		if (!is_string($message)) {
			$message = print_r($message, true);
		}

		return $this->cakeLogWrite($type, $message, $scope);
	}

	/**
	 * Wrapper method for calling static CakeLog::write method.
	 *
	 * @param int $type Error type constant. Defined in app/Config/core.php.
	 * @param string $message Log message
	 * @param null|string|array $scope The scope(s) a log message is being created in.
	 *    See CakeLog::config() for more information on logging scopes.
	 * @return bool Success of log write
	 */
	protected function cakeLogWrite($type, $message, $scope) {
		return CakeLog::write($type, $message, $scope);
	}
}
