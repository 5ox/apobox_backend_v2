<?php
App::uses('Component', 'Controller');
App::uses('Payment', 'Lib');
App::uses('Order', 'Model');

/**
 * Uses the "Payment" library to abstract some common controller tasks
 */
class PaymentComponent extends Component {

	/**
	 * components
	 *
	 * @var array
	 */
	public $components = array(
		'Session',
		'Auth',
	);

	/**
	 * The Payment library object
	 *
	 * @var mixed
	 */
	public $paymentLib = null;

	/**
	 * sessionKey
	 *
	 * @var string
	 */
	public $sessionKey = 'Payment';

	/**
	 * Holds a string of last error messaged
	 *
	 * @var mixed
	 */
	protected $lastErrorMessage = false;

	/**
	 * startup
	 *
	 * @param Controller $controller The controller
	 * @return void
	 */
	public function startup(Controller $controller) {
		parent::startup($controller);
		$this->paymentLib = $this->getPaymentLib();
	}

	/**
	 * shutdown
	 *
	 * @param Controller $controller The controller
	 * @return void
	 */
	public function shutdown(Controller $controller) {
		parent::shutdown($controller);
		$this->paymentLib = null;
	}

	/**
	 * getPaymentLib
	 *
	 * @return object A paymentLib instance
	 * @throws InternalErrorException
	 */
	public function getPaymentLib() {
		if ($this->paymentLib != null) {
			return $this->paymentLib;
		}
		$clientId = Configure::read('PayPal.clientId');
		$clientSecret = Configure::read('PayPal.clientSecret');
		$mode = Configure::read('PayPal.mode');
		if (empty($clientId) || empty($clientSecret)) {
			throw new InternalErrorException('Payment gateway not properly configured');
		}
		return new Payment($clientId, $clientSecret, $mode);
	}

	/**
	 * Returns the last error message
	 *
	 * @return string The last error message
	 */
	public function lastErrorMessage() {
		return $this->lastErrorMessage;
	}

	/**
	 * Charge a credit card (array) or a stored card (string) with a simple single transaction.
	 *
	 * This method contains logic duplicated in the Order model. All of the
	 * duplicated logic should be moved into a more general `Lib/Payment` class.
	 *
	 * @param mixed $card credit card data in an array or a string of a stored card ID to charge.
	 * @param array $options any options that Lib/Payment::chargeCard accepts.
	 * @return mixed False on failure, Payment ID on success.
	 */
	public function charge($card, $options = array()) {
		$card = $this->prepareCardForCharge($card);
		try {
			$result = $this->paymentLib->chargeCard($card, $options);
			return $result;
		} catch (Exception $exception) {
			$this->lastErrorMessage = $exception->getMessage();
			$this->log('PaymentComponent::charge: ' . $this->lastErrorMessage, 'orders');
			return false;
		}
	}

	/**
	 * Sets up the customer model and passes the card info to initialize
	 * it for a charge. This is only necessary for old style encrypted cards.
	 *
	 * @param array $card The card details.
	 * @return array The modified card details.
	 */
	protected function prepareCardForCharge($card) {
		return ClassRegistry::init('Customer')->initForCharge($card);
	}

	/**
	 * Method to check if the logged in customer has orders
	 * with a status of "Awaiting Payment" (2)
	 *
	 * @return bool
	 */
	public function customerHasOrdersAwaitingPayment() {
		$customerId = $this->Auth->user('customers_id');
		if ($customerId == null) {
			return false;
		}
		if ($this->Session->read($this->sessionKey . '.hasOrdersAwaitingPayment')) {
			return true;
		}

		$Order = ClassRegistry::init('Order');

		$awaitingPayment = $Order->find('awaitingPayments', array('conditions' => array('customers_id' => $customerId)));

		return (bool)count($awaitingPayment);
	}

	/**
	 * getCardType
	 *
	 * @param mixed $number The credit card number to check
	 * @return mixed The card type or false
	 */
	public function getCardType($number) {
		$cardInfo = Inacho\CreditCard::validCreditCard($number);
		if (!empty($cardInfo['type'])) {
			return $cardInfo['type'];
		}

		return false;
	}

	/**
	 * cardNumberIsValid
	 *
	 * @param mixed $number The credit card number to check
	 * @return bool
	 */
	public function cardNumberIsValid($number) {
		$cardValidation = Inacho\CreditCard::validCreditCard($number);
		if ($cardValidation['valid']) {
			return true;
		}

		return false;
	}
}
