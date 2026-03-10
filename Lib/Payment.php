<?php

/**
 * Class: Payment
 *
 */
class Payment {

	/**
	 * The PayPal\Rest\ApiContext object.
	 *
	 * @var mixed
	 */
	public $apiContext = null;

	/**
	 * __construct
	 *
	 * @param string $clientId The client id/username
	 * @param string $clientSecret The client secret/password
	 * @param string $mode live or sandbox
	 * @return void
	 */
	public function __construct($clientId, $clientSecret, $mode) {
		$oauthCredential = new PayPal\Auth\OAuthTokenCredential(
			$clientId,
			$clientSecret
		);

		$this->apiContext = new PayPal\Rest\ApiContext($oauthCredential);
		$this->apiContext->setConfig(array('mode' => $mode));
	}

	/**
	 * Stores $card in PayPal Vault.
	 *
	 * @param array $card The card data
	 * @return mixed String of card ID from PayPal, false on failure.
	 * @throws Exception
	 */
	public function storeCard(array $card) {
		$creditCard = $this->getCreditCard($card);

		try {
			$creditCard->create($this->apiContext);
			return $creditCard->id;
		} catch (\PayPal\Exception\PayPalConnectionException $exception) {
			throw new Exception(
				$this->getMessageFromExceptionData($exception->getData()),
				$exception->getCode(),
				$exception
			);
		}
	}

	/**
	 * With given billing address and card, authorizes card for a $1 charge.
	 *
	 * @param array $card The card data
	 * @param array $billingAddress The customer billing address
	 * @return bool True if card could be authorized, otherwise false.
	 * @throws Exception
	 */
	public function authorizeCard(array $card, array $billingAddress) {
		$fi = $this->getFundingInstrument($card, $billingAddress);
		if (!$fi) {
			return false;
		}
		$payment = $this->authorizeFundingInstrument($fi);

		try {
			$payment->create($this->apiContext);
			if ($payment->state != 'approved') {
				return false;
			}
		} catch (\PayPal\Exception\PayPalConnectionException $exception) {
			throw new Exception(
				$this->getMessageFromExceptionData($exception->getData()),
				$exception->getCode(),
				$exception
			);
		}

		return true;
	}

	/**
	 * authorizeAndStoreCard
	 *
	 * @param array $card The card data
	 * @param array $address The customer address
	 * @return bool True if card could be authorized and stored, otherwise false.
	 * @throws Exception
	 */
	public function authorizeAndStoreCard(array $card, array $address) {
		if (!$this->authorizeCard($card, $address)) {
			return false;
		}
		return $this->storeCard($card);
	}

	/**
	 * chargeCard
	 *
	 * @param mixed $card The card data
	 * @param array $options Optional options to merge with the defaults
	 * @return bool Payment ID if card was charged, otherwise false.
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public function chargeCard($card, $options = array()) {
		$defaultOptions = array(
			'address' => array(),
			'method' => 'credit_card',
			'details' => array(),
			'currency' => 'USD',
			'description' => '',
			'intent' => 'sale',
		);
		$options = Hash::merge($defaultOptions, $options);

		if (empty($options['total'])) {
			throw new InvalidArgumentException('Missing index \'total\' or value at \'total\' is empty in argument 2.');
		}

		$fi = $this->getFundingInstrument($card, $options['address']);
		$payer = $this->getPayer($fi, $options['method']);
		$details = $this->getDetails($options['details']);
		$amount = $this->getAmount($options['total'], $options['currency'], $details);
		$transaction = $this->getTransaction($amount, $options['description']);
		$payment = $this->getPayment($payer, $transaction, $options['intent']);

		try {
			$payment->create($this->apiContext);
			if ($payment->state == 'approved') {
				return $payment->id;
			}
			return false;
		} catch (\PayPal\Exception\PayPalConnectionException $exception) {
			throw new Exception(
				$this->getMessageFromExceptionData($exception->getData()),
				$exception->getCode(),
				$exception
			);
		}
	}

	/**
	 * Return a funding instrument
	 *
	 * @param mixed $card String or array of card data
	 * @param array $address The billing address
	 * @return PayPal\Api\FundingInstrument
	 */
	public function getFundingInstrument($card, $address = array()) {
		if (is_string($card)) {
			return $this->getFundingInstrumentUsingToken($card);
		}
		if (!empty($card['card_token']) && is_string($card['card_token'])) {
			return $this->getFundingInstrumentUsingToken($card['card_token']);
		}

		return $this->getFundingInstrumentUsingCard($card, $address);
	}

	/**
	 * Creates a funding instruments with a card token.
	 *
	 * @param mixed $card String or array of card data
	 * @return PayPal\Api\FundingInstrument
	 */
	protected function getFundingInstrumentUsingToken($card) {
		$creditCardToken = new PayPal\Api\CreditCardToken();
		$creditCardToken->setCreditCardId($card);

		$fi = new PayPal\Api\FundingInstrument();
		return $fi->setCreditCardToken($creditCardToken);
	}

	/**
	 * Creates a funding instruments with a credit card.
	 *
	 * @param mixed $card String or array of card data
	 * @param array $address The billing address
	 * @return PayPal\Api\FundingInstrument
	 */
	protected function getFundingInstrumentUsingCard($card, $address) {
		$address = $this->massageAddressData($address);
		$creditCard = $this->getCreditCard($card);

		if (!empty($address)) {
			$creditCard->setBillingAddress($this->getAddress($address));
		}

		$fi = new PayPal\Api\FundingInstrument();
		return $fi->setCreditCard($creditCard);
	}

	/**
	 * getCreditCard
	 *
	 * @param array $card The card data
	 * @return mixed a PayPal\Api\CreditCard instance or false on failure.
	 * @throws BadMethodCallException
	 */
	public function getCreditCard(array $card) {
		$card = $this->massageCardData($card);
		$requiredKeys = array(
			'number',
			'expire_month',
			'expire_year',
			'cvv2',
			'first_name',
			'last_name',
		);
		foreach ($requiredKeys as $key) {
			if (empty($card[$key])) {
				throw new BadMethodCallException('Missing key "' . $key . '" or value of "' . $key . '" is empty.');
			}
		}

		$cardValidation = Inacho\CreditCard::validCreditCard($card['number']);

		$creditCard = new PayPal\Api\CreditCard();
		$creditCard = $creditCard
			->setNumber($card['number'])
			->setType($cardValidation['type'])
			->setExpireMonth($card['expire_month'])
			->setExpireYear($card['expire_year'])
			->setFirstName($card['first_name'])
			->setLastName($card['last_name']);

		if ($card['cvv2'] !== 'not_used') {
			$creditCard->setCvv2($card['cvv2']);
		}

		return $creditCard;
	}

	/**
	 * getAddress
	 *
	 * @param array $billingAddress The billing address
	 * @return mixed a PayPal\Api\Address instance or false on failure.
	 * @throws BadMethodCallException
	 */
	public function getAddress(array $billingAddress) {
		$requiredKeys = array(
			'line1',
			'city',
			'state',
			'country_code',
			'postal_code',
		);
		foreach ($requiredKeys as $key) {
			if (empty($billingAddress[$key])) {
				throw new BadMethodCallException('Missing key "' . $key . '" or value of key "' . $key . '" is empty.');
			}
		}

		$address = new PayPal\Api\Address();
		$address = $address
			->setLine1($billingAddress['line1'])
			->setLine2($billingAddress['line2'])
			->setCity($billingAddress['city'])
			->setCountryCode($billingAddress['country_code'])
			->setPostalCode($billingAddress['postal_code'])
			->setState($billingAddress['state']);

		return $address;
	}

	/**
	 * getPayer
	 *
	 * @param mixed $fi The funding instrument
	 * @param string $method The payment method
	 * @return PayPal\Api\Payer
	 */
	public function getPayer($fi, $method = 'credit_card') {
		$payer = new PayPal\Api\Payer();
		$payer->setPaymentMethod($method);
		$payer->setFundingInstruments(array($fi));

		return $payer;
	}

	/**
	 * getAmount
	 *
	 * @param int $total The charge total
	 * @param string $currency The currency
	 * @param mixed $details The optional charge details
	 * @return PayPal\Api\Amount
	 */
	public function getAmount($total, $currency = 'USD', $details = null) {
		$amount = new PayPal\Api\Amount();
		$amount->setCurrency($currency);
		$amount->setTotal($total);

		if (!empty($details)) {
			$amount->setDetails($details);
		}

		return $amount;
	}

	/**
	 * getTransaction
	 *
	 * @param int $amount The transaction amount
	 * @param string $description The transaction description
	 * @return PayPal\Api\Transaction
	 */
	public function getTransaction($amount, $description = '') {
		$transaction = new PayPal\Api\Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription($description);

		return $transaction;
	}

	/**
	 * getPayment
	 *
	 * @param mixed $payer The payer
	 * @param mixed $transactions The transactions
	 * @param string $intent The intent
	 * @return PayPal\Api\Payment
	 */
	public function getPayment($payer, $transactions, $intent = 'authorize') {
		$payment = new PayPal\Api\Payment();
		$payment->setIntent($intent);
		$payment->setPayer($payer);
		if (!is_array($transactions)) {
			$transactions = array($transactions);
		}
		$payment->setTransactions($transactions);

		return $payment;
	}

	/**
	 * getDetails
	 *
	 * @param array $details The details
	 * @return PayPal\Api\Details
	 */
	public function getDetails($details) {
		$defaultDetails = array(
			'subtotal' => '0.00',
			'tax' => '0.00',
			'shipping' => '0.00',
		);
		$detail = array_merge($defaultDetails, $details);

		$details = new PayPal\Api\Details();
		$details->setSubtotal($detail['subtotal']);
		$details->setTax($detail['tax']);
		$details->setShipping($detail['shipping']);

		return $details;
	}

	/**
	 * authorizeFundingInstrument
	 *
	 * @param mixed $fi The funding instrument
	 * @return PayPal\Api\FundingInstrument
	 */
	protected function authorizeFundingInstrument($fi) {
		$payer = $this->getPayer($fi);
		$details = $this->getDetails([
			'subtotal' => '1.00',
			'tax' => '0.00',
			'shipping' => '0.00',
		]);
		$amount = $this->getAmount('1.00', 'USD', $details);
		$transaction = $this->getTransaction($amount, 'APO Box card authorization.');
		$payment = $this->getPayment($payer, [$transaction]);

		return $payment;
	}

	/**
	 * getMessageFromExceptionData
	 *
	 * @param mixed $data The response data
	 * @return string
	 */
	private function getMessageFromExceptionData($data) {
		if (!is_array($data)) {
			$data = json_decode($data, true);
		}

		if (!empty($data['error'])) {
			return $data['error_description'];
		}

		if ($data['name'] == 'INTERNAL_SERVICE_ERROR') {
			return 'There was a problem with the authorization service. Service said "' . $data['message'] . '"';
		}

		if ($data['name'] == 'VALIDATION_ERROR') {
			return 'There was an error validating the credit card.';
		}

		return $data['message'];
	}

	/**
	 * Puts credit card data into proper format for payment provider.
	 *
	 * @param array $card The card data from the model.
	 * @return array Formatted card data.
	 */
	protected function massageCardData($card) {
		$map = array(
			'cc_number' => 'number',
			'cc_expires_year' => 'expire_year',
			'cc_expires_month' => 'expire_month',
			'cc_firstname' => 'first_name',
			'cc_lastname' => 'last_name',
			'cc_cvv' => 'cvv2',
		);

		$card = $this->mapData($card, $map);
		if ($card['expire_year'] < 2000) {
			$card['expire_year'] = (string)($card['expire_year'] + 2000);
		}
		// Ensure no leading '0's
		$card['expire_month'] = sprintf('%d', $card['expire_month']);
		return $card;
	}

	/**
	 * Puts address data into proper format for payment provider.
	 *
	 * @param array $address The address data from the model.
	 * @return array Formatted address data.
	 * @throws BadMethodCallException
	 */
	protected function massageAddressData($address) {
		if (!$this->addressHasValidCountry($address)) {
			throw new BadMethodCallException('Improper country for billing.');
		}

		if (empty($address['Zone']['zone_code'])) {
			throw new BadMethodCallException('Missing zone code in address.');
		}
		if (empty($address['Address']) || !is_array($address['Address'])) {
			throw new BadMethodCallException('Missing or incorrectly formatted address array.');
		}

		return array(
			'line1' => $address['Address']['entry_street_address'],
			'line2' => $address['Address']['entry_suburb'],
			'city' => $address['Address']['entry_city'],
			'state' => $address['Zone']['zone_code'],
			'country_code' => $address['Country']['countries_iso_code_2'],
			'postal_code' => $address['Address']['entry_postcode'],
		);
	}

	/**
	 * Checks that a country code is set, and that is in the list of allowed
	 * countries.
	 *
	 * @param array $address The address data from the model.
	 * @return bool
	 */
	protected function addressHasValidCountry($address) {
		return (
			!empty($address['Country']['countries_iso_code_2'])
			&& in_array(
				$address['Country']['countries_iso_code_2'],
				Configure::read('PayPal.allowedBillingCountries')
			)
		);
	}

	/**
	 * Convert array keys in data based on a map array.
	 *
	 * @param array $data The data.
	 * @param array $map The map: from => to.
	 * @return array The data with converted keys.
	 */
	protected function mapData(array $data, array $map) {
		$mapped = array();
		foreach ($data as $key => $value) {
			if (!empty($map[$key])) {
				$mapped[$map[$key]] = $value;
			}
		}

		return $mapped;
	}
}
