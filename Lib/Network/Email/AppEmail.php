<?php
/**
 * AppEmail file. Extends the CakeEmail class to provide shortcut methods for
 * common use cases. Allows all email-sending-related code to be consolidated
 * here.
 *
 */
App::uses('CakeEmail', 'Network/Email');
App::uses('LogTrait', 'Lib/Log');

/**
 * AppEmail class.
 *
 * Usage:
 *
 * App::uses('AppEmail', 'Lib/Network/Email');
 * $result = new AppEmail()->callShortcutSenderMethod($recipient, $viewVars);
 *
 */
class AppEmail extends CakeEmail {

	use LogTrait;

	/**
	 * sendForgotPassword
	 *
	 * Sends emails with links to reset a customers password
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `requestId` to be set
	 * @return bool true if email sent, false otherwise
	 */
	public function sendForgotPassword($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('forgot_password')
			->to($recipients)
			->from(Configure::read('Email.Address.support'))
			->subject(__('Your Password Reset Link'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them of a status change or additional comment
	 * Should not be used when the status has been marked as "shipped". See `sendShipped()`
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, `orderId` and 'status' to be set with optional `subject`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendStatusUpdate($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('order_status_update')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.order'), $viewVars['orderId']) . __('Status Update'))
			->viewVars($viewVars);
		if (!empty($viewVars['subject'])) {
			$this->subject($viewVars['subject']);
		}
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that their order has been shipped
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendShipped($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('order_shipped')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.order'), $viewVars['orderId']) . __('Shipped'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer with the body $body.
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `body` and `name`
	 * @param array $subject optional subject of message.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendBlank($recipients, $viewVars = array(), $subject = '') {
		$this
			->config('default')
			->template('blank')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject($subject)
			->viewVars($viewVars);
		if (!empty($viewVars['subject'])) {
			$this->subject($viewVars['subject']);
		}
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that their order has failed automatic payment
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendFailedPayment($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('order_failed_payment')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.order'), $viewVars['orderId']) . __('Awaiting Payment'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends a welcome email.
	 *
	 * @param array $recipients Array of who to send the email to.
	 * @param array $viewVars View variables.
	 * @return bool True if email sent, false otherwise.
	 */
	public function sendWelcome($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('welcome')
			->to($recipients)
			->from(Configure::read('Email.Address.support'))
			->subject(__('Welcome to APO Box Shipping'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that their credit card expires next month
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendCreditCardExpires($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('customer_card_expiring')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.customer') . ' - Credit Card Expiring'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that they have a package
	 * awaiting payment.
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendAwaitingPaymentAlert($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('order_awaiting_payment')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.customer') . ' - Package Awaiting Payment'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that they have not completed
	 * the signup process (missing address data).
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendPartialSignupAlert($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('customer_partial_signup')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.customer') . ' - Complete Your Registration'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends an email to a customer informing them that their credit card expired
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `orderId`.
	 * @return bool true if email sent, false otherwise
	 */
	public function sendCreditCardExpired($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('customer_card_expired')
			->to($recipients)
			->from(Configure::read('Email.Address.noreply'))
			->subject(__(Configure::read('Email.Subjects.customer') . ' - Credit Card Expired'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sends emails with links to confirm account closure.
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `requestId` to be set
	 * @return bool true if email sent, false otherwise
	 */
	public function sendConfirmClose($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('confirm_close')
			->to($recipients)
			->from(Configure::read('Email.Address.support'))
			->subject(__('Confirm Closing Your Account'))
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Generic method intented for admin/manager messages
	 *
	 * @param array $recipients array of who to send the email to
	 * @param array $viewVars expects `firstName`, `lastName`, and `requestId` to be set
	 * @return bool true if email sent, false otherwise
	 */
	public function sendManagerMessage($recipients, $viewVars = array()) {
		$this
			->config('default')
			->template('manager_message', 'manager')
			->to($recipients)
			->from(Configure::read('Email.Address.support'))
			->subject($viewVars['subject'])
			->viewVars($viewVars);
		return $this->sendEmail($this);
	}

	/**
	 * Sending method which will catch and log any SMTP or email related
	 * exceptions that are thrown if the email can't be sent.
	 *
	 * @param object $email A configured email object
	 * @return array The result of CakeEmail::send()
	 */
	public function sendEmail($email) {
		try {
			$result = $email->send();
		} catch (Exception $e) {
			$this->log($e->getMessage() . ' sending to: ' . json_encode($email->to()), 'email-error');
			return false;
		}
		return $result;
	}
}
