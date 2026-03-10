<?php
/**
 * AppShell Parent class for all application Shells.
 *
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 */
class AppShell extends Shell {

	/**
	 * _out
	 *
	 * Wrapper around Shell::out() to provide consistent formatting.
	 *
	 * @param string $msg The message to be sent to the console.
	 * @param string $style The Shell output style (''/error/warning/info/comment/question) to use for the message.
	 * @param int $level The Shell::QUIET/NORMAL/VERBOSE output level.
	 * @return int|bool Returns the number of bytes returned from writing to stdout.
	 * @access protected
	 */
	protected function _out($msg, $style = 'error', $level = Shell::NORMAL) {
		if (empty($style)) {
			$format = '%1$s';
		} else {
			$format = '<%2$s>%1$s</%2$s>';
		}
		$msg = sprintf($format, $msg, $style);
		return $this->out($msg, 1, $level);
	}
}
