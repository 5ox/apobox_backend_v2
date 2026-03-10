<?php
App::uses('AppEmail', 'Lib/Network/Email');

/**
 * AppEmail Test Case
 *
 */
class AppEmailTest extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Email = $this->getMock('AppEmail', array('sendEmail', 'deliver'));
		Configure::write('Email.Subjects.order', 'APO Box Shipping Package %s: ');
		Configure::write('Email.Subjects.customer', 'APO Box Message');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Email);
		parent::tearDown();
	}

	/**
	 * Test that the AppEmail class implements a logging method. This is most
	 * likely inherited from LogTrait.
	 *
	 * @return void
	 */
	public function testHasLogMethod() {
		$AppEmail = new AppEmail();

		$this->assertTrue(method_exists($AppEmail, 'log'));
	}

	/**
	 * Confirm that the convenience method for sending a password reminder
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testForgotPassword() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendForgotPassword($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendForgotPassword() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendForgotPassword().'
		);
		$this->assertEquals(
			array('template' => 'forgot_password', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendForgotPassword().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendForgotPassword().'
		);
		$this->assertEquals(
			'Your Password Reset Link',
			$this->Email->subject(),
			'The subject must match the one defined in sendForgotPassword().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a status update
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testStatusUpdate() {
		$recipients = array();
		$vars = array('orderId' => '12345');
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendStatusUpdate($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendStatusUpdate() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendStatusUpdate().'
		);
		$this->assertEquals(
			array('template' => 'order_status_update', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendStatusUpdate().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendStatusUpdate().'
		);
		$this->assertEquals(
			__('APO Box Shipping Package %s: ', $vars['orderId']) . __('Status Update'),
			$this->Email->subject(),
			'The subject must match the one defined in sendStatusUpdate().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a status update
	 * email with subject sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testStatusUpdateWithSubject() {
		$recipients = array();
		$vars = array('subject' => 'Test Subject', 'orderId' => '12345');
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendStatusUpdate($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendStatusUpdate() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendStatusUpdate().'
		);
		$this->assertEquals(
			array('template' => 'order_status_update', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendStatusUpdate().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendStatusUpdate().'
		);
		$this->assertEquals(
			$vars['subject'],
			$this->Email->subject(),
			'The subject must match the one defined in sendStatusUpdate().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a shipped notification
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testShipped() {
		$recipients = array();
		$vars = array('orderId' => '12345');
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendShipped($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendShipped() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendShipped().'
		);
		$this->assertEquals(
			array('template' => 'order_shipped', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendShipped().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendShipped().'
		);
		$this->assertEquals(
			__('APO Box Shipping Package %s: ', $vars['orderId']) . __('Shipped'),
			$this->Email->subject(),
			'The subject must match the one defined in sendShipped().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a blank
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testBlank() {
		$recipients = array();
		$vars = array('subject' => 'blank');
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendBlank($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendBlank() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendBlank().'
		);
		$this->assertEquals(
			array('template' => 'blank', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendBlank().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendBlank().'
		);
		$this->assertEquals(
			$vars['subject'],
			$this->Email->subject(),
			'The subject must match the one defined in sendBlank().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a failed payment
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testFailedPayment() {
		$recipients = array();
		$vars = array('orderId' => '12345');
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendFailedPayment($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendShipped() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendFailedPayment().'
		);
		$this->assertEquals(
			array('template' => 'order_failed_payment', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendFailedPayment().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendFailedPayment().'
		);
		$this->assertEquals(
			__('APO Box Shipping Package %s: ', $vars['orderId']) . __('Awaiting Payment'),
			$this->Email->subject(),
			'The subject must match the one defined in sendFailedPayment().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a welcome
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testWelcome() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendWelcome($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendWelcome() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendWelcome().'
		);
		$this->assertEquals(
			array('template' => 'welcome', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendWelcome().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendWelcome().'
		);
		$this->assertEquals(
			'Welcome to APO Box Shipping',
			$this->Email->subject(),
			'The subject must match the one defined in sendWelcome().'
		);
	}

	/**
	 * Confirm that the convenience method for sending an expired card
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testCreditCardExpires() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendCreditCardExpires($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendCreditCardExpires() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendCreditCardExpires().'
		);
		$this->assertEquals(
			array('template' => 'customer_card_expiring', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendCreditCardExpires().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendCreditCardExpires().'
		);
		$this->assertEquals(
			__(Configure::read('Email.Subjects.customer') . ' - Credit Card Expiring'),
			$this->Email->subject(),
			'The subject must match the one defined in sendCreditCardExpires().'
		);
	}

	/**
	 * Confirm that the convenience method for sending an awaiting payment
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testAwaitingPaymentAlert() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendAwaitingPaymentAlert($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendAwaitingPaymentAlert() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendAwaitingPaymentAlert().'
		);
		$this->assertEquals(
			array('template' => 'order_awaiting_payment', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendAwaitingPaymentAlert().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendAwaitingPaymentAlert().'
		);
		$this->assertEquals(
			__(Configure::read('Email.Subjects.customer') . ' - Package Awaiting Payment'),
			$this->Email->subject(),
			'The subject must match the one defined in sendAwaitingPaymentAlert().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a partial signup
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testPartialSignupAlert() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendPartialSignupAlert($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendPartialSignupAlert() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendPartialSignupAlert().'
		);
		$this->assertEquals(
			array('template' => 'customer_partial_signup', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendPartialSignupAlert().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendPartialSignupAlert().'
		);
		$this->assertEquals(
			__(Configure::read('Email.Subjects.customer') . ' - Complete Your Registration'),
			$this->Email->subject(),
			'The subject must match the one defined in sendPartialSignupAlert().'
		);
	}

	/**
	 * Confirm that the convenience method for sending an expired card
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testCreditCardExpired() {
		$recipients = array();
		$vars = array();
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendCreditCardExpired($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendCreditCardExpired() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendCreditCardExpired().'
		);
		$this->assertEquals(
			array('template' => 'customer_card_expired', 'layout' => 'default'),
			$this->Email->template(),
			'The template must match the one defined in sendCreditCardExpired().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendCreditCardExpired().'
		);
		$this->assertEquals(
			__(Configure::read('Email.Subjects.customer') . ' - Credit Card Expired'),
			$this->Email->subject(),
			'The subject must match the one defined in sendCreditCardExpired().'
		);
	}

	/**
	 * Confirm that the convenience method for sending an account closure
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testConfirmClose() {
		$recipients = [];
		$vars = [];
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendConfirmClose($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendConfirmClose() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendConfirmClose().'
		);
		$this->assertEquals(
			['template' => 'confirm_close', 'layout' => 'default'],
			$this->Email->template(),
			'The template must match the one defined in sendConfirmClose().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendConfirmClose().'
		);
		$this->assertEquals(
			'Confirm Closing Your Account',
			$this->Email->subject(),
			'The subject must match the one defined in sendConfirmClose().'
		);
	}

	/**
	 * Confirm that the convenience method for sending a manager
	 * email sets the proper values. (Config only! Not "view vars" such as
	 * to/from/subject which are equivelent to View .ctp's.)
	 *
	 * @return void
	 */
	public function testManagerMessage() {
		$recipients = [];
		$vars = ['subject' => 'test subject'];
		$this->Email->expects($this->once())
			->method('sendEmail')
			->will($this->returnValue('canary'));

		$result = $this->Email->sendManagerMessage($recipients, $vars);

		$this->assertEquals(
			'canary',
			$result,
			'Return value of sendManagerMessage() must be the result of underlying CakeEmail::send().'
		);
		$this->assertEquals(
			(new EmailConfig)->default,
			$this->Email->config(),
			'The loaded config must match the one defined in sendManagerMessage().'
		);
		$this->assertEquals(
			['template' => 'manager_message', 'layout' => 'manager'],
			$this->Email->template(),
			'The template must match the one defined in sendManagerMessage().'
		);
		$this->assertEquals(
			$recipients,
			$this->Email->to(),
			'The recipient list must match the one provided to sendManagerMessage().'
		);
		$this->assertEquals(
			'test subject',
			$this->Email->subject(),
			'The subject must match the one defined in sendManagerMessage().'
		);
	}

	/**
	 * Confirm that if email sending fails, the `log` method is called and
	 * `sendEmail()` returns false.
	 *
	 * @return void
	 */
	public function testSendEmailException() {
		$AppEmail = $this->getMockBuilder('AppEmail')
			->setMethods(['log'])
			->getMock();
		$Email = $this->getMockBuilder('CakeEmail')
			->setMethods(['send'])
			->getMock();

		$Email->expects($this->once())
			->method('send')
			->will($this->throwException(new Exception));
		$AppEmail->expects($this->once())
			->method('log');

		$result = $AppEmail->sendEmail($Email);

		$this->assertFalse($result);
	}

	/**
	 * Confirm that if email sending succeeds, the log is not written to and
	 * the method returns the expected result.
	 *
	 * @return void
	 */
	public function testSendEmailSuccess() {
		$AppEmail = $this->getMockBuilder('AppEmail')
			->setMethods(['log'])
			->getMock();
		$Email = $this->getMockBuilder('CakeEmail')
			->setMethods(['send'])
			->getMock();

		$Email->expects($this->once())
			->method('send')
			->will($this->returnValue('canary'));
		$AppEmail->expects($this->never())
			->method('log');

		$result = $AppEmail->sendEmail($Email);

		$this->assertSame('canary', $result);
	}
}
