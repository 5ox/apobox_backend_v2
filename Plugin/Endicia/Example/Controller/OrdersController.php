<?php
App::uses('AppController', 'Controller');
App::uses('Endicia', 'Lib');

class ExampleController extends AppController {

	/**
	 * The $rateBackend to use if configure var `ShippingApis.Rates.backend` is
	 * not set. Options are either `Usps` or `Endicia`.
	 *
	 * @var string
	 */
	protected $_defaultRateBackend = 'Usps';

	/**
	 * Allows a manager to review, edit totals, and charge an existing order.
	 *
	 * @param $id The order id.
	 */
	public function manager_charge($id = null) {
		if ($this->request->data['submit'] == 'postage') {
			$rateBackend = Configure::check('ShippingApis.Rates.backend') ? Configure::read('ShippingApis.Rates.backend') : $this->_defaultRateBackend;
			$rates = $this->{'get' . $rateBackend . 'Rates'}($order);
			$this->request->data['OrderTotal'] = $order['OrderTotal'];
			$this->set(compact('order', 'invoiceCustomer', 'allowCharge', 'rates'));
			return;
		}

		if ($this->request->data['submit'] == 'label') {
			$postageLabel = $this->getEndiciaLabel($order);
			if (!$postageLabel) {
				$this->Flash->set('There was an error generating the label.');
			}
			$this->request->data['OrderTotal'] = $order['OrderTotal'];
			$this->set(compact('order', 'invoiceCustomer', 'allowCharge', 'postageLabel'));
			return;
		}
	}

	/**
	 * Prepares a request array of required fields and queries the Endicia API
	 * to find all available rates based on the order package data.
	 *
	 * @param array $order An order
	 * @return array $rates The available rates for the package
	 */
	protected function getEndiciaRates($order) {
		$Endicia = $this->initEndicia();
		return $Endicia->getRates($order);
	}

	/**
	 * Uses $order data to fetch a label from the Endicia API with on-the-fly
	 * postage calculated. The label is saved as a pdf.
	 *
	 * @param array $order The order
	 * @return mixed The URL for the label image or false on failure
	 */
	protected function getEndiciaLabel($order) {
		$Endicia = $this->initEndicia();
		return $Endicia->getLabel($order);
	}

	/**
	 * Initialize an instance of the Endicia class.
	 *
	 * @param array $config The config
	 * @return object $Endicia
	 */
	protected function initEndicia($config = array()) {
		return new Endicia($config);
	}
}
