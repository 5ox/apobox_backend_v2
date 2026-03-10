<?php
App::uses('AppEmail', 'Lib/Network/Email');
App::uses('QueueTask', 'Queue.Console/Command/Task');

/**
 * Class: QueueAppEmailTask
 *
 * @see QueueTask
 */
class QueueAppEmailTask extends QueueTask {

	/**
	 * Task runner to send email using AppEmail
	 *
	 * @param array $data The message data consisting of [recipient] and [vars] keys
	 * @param mixed $id The id
	 * @return bool The result of the AppEmail method or false if missing required elements
	 */
	public function run($data, $id = null) {
		if (!isset($data['method']) || !isset($data['recipient'])) {
			return false;
		}

		$email = $this->emailFactory();
		if (!in_array($data['method'], get_class_methods($email))) {
			return false;
		}

		$subject = isset($data['subject']) ? $data['subject'] : null;
		$vars = isset($data['vars']) ? $data['vars'] : [];

		return (bool)$email->{$data['method']}($data['recipient'], $vars, $subject);
	}

	/**
	 * Instantiates and returns an instance of the application's email
	 * handler class, AppEmail.
	 *
	 * @param string $config The name of the CakeEmail config class to use.
	 * @return AppEmail Instance of the subclassed CakeEmail class.
	 */
	public function emailFactory($config = null) {
		return new AppEmail($config);
	}
}
