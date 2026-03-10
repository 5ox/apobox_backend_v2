<?php
/**
 * Orders
 */

App::uses('ActivityComponent', 'Controller/Component');
App::uses('AppController', 'Controller');
App::uses('EndiciaXml', 'Lib');
App::uses('Fedex', 'Lib');
App::uses('Security', 'Controller/Component');
App::uses('Usps', 'Lib');
App::uses('ZebraLabel', 'Lib');

/**
 * Orders Controller
 *
 * @property	Order	$Order
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class OrdersController extends AppController {

	/**
	 * Components
	 *
	 * @var	array
	 */
	public $components = array(
		'Activity',
		'Paginator',
		'Payment',
	);

	/**
	 * Helpers
	 *
	 * @var	array
	 */
	public $helpers = array(
		'Paginator',
		'Tracking',
		'Number',
		'Report',
	);

	/**
	 * The $rateBackend to use if configure var `ShippingApis.Rates.backend` is
	 * not set. Options are currently only `Usps`.
	 *
	 * @var string
	 */
	protected $_defaultRateBackend = 'Usps';

	/**
	 * Load and configure the security component for `pay_manually` to help
	 * prevent multiple for submissions with the `csrfUseOnce` setting.
	 *
	 * @return void
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		if ($this->request->params['action'] == 'pay_manually') {
			$this->Security = $this->Components->load('Security');
			$this->Security->csrfUseOnce = true;
			$this->Security->blackHoleCallback = 'blackhole';
		}
	}

	/**
	 * Used only for `pay_manually` and called when the payment form is resubmitted
	 * with the same token.
	 *
	 * @param string $type The type of security violation
	 * @return mixed
	 */
	public function blackhole($type) {
		$this->Flash->set('Your payment is currently being processed, please check your payment status.');
		return $this->redirect($this->request->here);
	}

	/**
	 * index method
	 *
	 * @return	void
	 */
	public function index() {
		$this->request->allowMethod('get');
		$this->Paginator->settings = [
			'contain' => [
				'OrderStatus',
				'Customer' => ['customers_id', 'billing_id', 'customers_email_address'],
				'OrderTotal',
			],
			'conditions' => ['Order.customers_id' => $this->Auth->user('customers_id')],
			'order' => ['Order.date_purchased' => 'DESC']
		];
		$results = $this->Paginator->paginate();
		$userIsManager = $this->_userIsManager();
		$customRequests = $this->Order->CustomPackageRequest->findAllForOrder($results);
		$this->set(compact('userIsManager', 'customRequests'));
		$this->set('orders', $results);
	}

	/**
	 * view
	 *
	 * @param mixed $id The order id
	 * @return void
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 */
	public function view($id = null) {
		$this->request->allowMethod('get');
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('The order could not be found.'));
		}
		$this->Order->id = $id;
		if ($this->Order->field('customers_id') != $this->Auth->user('customers_id')) {
			throw new ForbiddenException(__('The order does not belong to you.'));
		}
		$options = array(
			'conditions' => array(
				'Order.' . $this->Order->primaryKey => $id
			),
			'contain' => Array(
				'Customer',
				'OrderStatus',
				'OrderStatusHistory',
				'CustomPackageRequest',
			),
		);
		$order = $this->Order->find('first', $options);
		$currentStatusHistory = $this->Order->OrderStatusHistory->findCurrentStatusForOrderId($id);
		$statusHistories = $this->Order->OrderStatusHistory->findStatusesForOrderId(
			$id,
			array('contain' => array('OrderStatus'))
		);
		$orderCharges = $this->Order->OrderTotal->findAllChargesForOrderId($id);
		$this->set(compact('order', 'currentStatusHistory', 'statusHistories', 'orderCharges'));
	}

	/**
	 * pay_manually
	 *
	 * @param mixed $id The order id
	 * @return mixed
	 * @throws NotFoundException
	 * @throws BadRequestException
	 */
	public function pay_manually($id = null) {
		$this->request->allowMethod(array('get', 'post', 'put'));
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(sprintf('Order #%d could not be found.', $id));
		}
		$order = $this->Order->find('first', array(
			'contain' => array(
				'OrderTotal',
			),
			'conditions' => array('Order.orders_id' => $id),
		));

		if ($this->request->is('post') || $this->request->is('put')) {
			if (empty($this->request->data['Customer'])) {
				throw new BadRequestException('Customer payment must be provided.');
			}
			if (empty($this->request->data['Address'])) {
				throw new BadRequestException('Billing address must be provided.');
			}
			if (empty($this->request->data['Customer']['customers_default_address_id'])) {
				$this->request->data['Customer']['customers_default_address_id'] = 'custom';
			}
			$this->request->data['Customer']['customers_id'] = $order['Order']['customers_id'];
			// Validate using Customer validation rules
			$this->Order->Customer->set($this->request->data);
			$customerValidateFields = [
				'cc_firstname',
				'cc_lastname',
				'cc_number',
				'cc_number_encrypted',
				'cc_expires_month',
				'cc_expires_year',
				'cc_cvv',
				'card_token',
			];
			$address = $this->_checkOrSetAddress();
			if ($address && $this->Order->Customer->validates(['fieldList' => $customerValidateFields])) {
				// Get data to pass to charge method
				$card = $this->request->data['Customer'];
				unset($card['save']);
				$options = array(
					'address' => $address,
					'total' => sprintf('%2.f', $order['OrderTotal']['value']),
					'description' => 'Order #' . $this->Order->id
				);
				// Charge the full amount
				if ($this->Payment->charge($card, $options)) {
					// Charge successful, Update the order
					$this->Order->set(array(
						'orders_id' => $this->Order->id,
						'orders_status' => 4,
						'billing_status' => 4,
						'cc_number' => $this->request->data['Customer']['cc_number'],
						'cc_expires' =>
							$this->request->data['Customer']['cc_expires_month'] .
							$this->request->data['Customer']['cc_expires_year'],
						'cc_owner' => $this->request->data['Customer']['cc_firstname'] . ' ' . $this->request->data['Customer']['cc_lastname'],
						'billing_name' => $address['Address']['entry_firstname'] . ' ' . $address['Address']['entry_lastname'],
						'billing_company' => isset($address['Address']['entry_company']) ? $address['Address']['entry_company'] : $address['Address']['entry_firstname'] . ' ' . $address['Address']['entry_lastname'],
						'billing_street_address' => $address['Address']['entry_street_address'],
						'billing_suburb' => $address['Address']['entry_suburb'],
						'billing_city' => $address['Address']['entry_city'],
						'billing_postcode' => $address['Address']['entry_postcode'],
						'billing_state' => $address['Zone']['zone_code'],
						'billing_country' => $address['Country']['countries_name'],
						'billing_address_format_id' => $address['Country']['address_format_id'],
					));
					$this->Order->save();
					$this->Flash->set('Your package has been paid for.');
					$this->Order->Customer->CustomerReminder->clearRecord($id, 'awaiting_payment');

					// Check if the customer has opted to save this card to their account
					if ($this->request->data['Customer']['save']) {
						if ($this->Order->Customer->save($this->request->data, true, $customerValidateFields)) {
							$this->Activity->record('edit', $this->Auth->user('customers_id'), 'payment_info');
							$this->Order->Customer->CustomerReminder->clearRecord($this->Auth->user('customers_id'), 'payment_info');
							// Save the new address if a new/custom one.
							if ($this->request->data['Customer']['customers_default_address_id'] == 'custom') {
								// Save the address and set it as the customer's default, update Auth
								if ($addressId = $this->Order->Customer->Address->saveAndMakeDefault($this->request->data)) {
									$this->Session->write('Auth.User.customers_default_address_id', $addressId);
								}
							}
							$this->Flash->set(
								'Your package has been paid for and your payment information has been saved for automatic billing when a package arrives.'
							);
						} else {
							$this->Flash->set(
								'Your package was successfully paid for but your payment information could not be saved for new packages.'
							);
						}
					}

					return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
				} else {
					$this->Flash->set('The credit card you provided could not be charged. Please double check all information and try again.');
				}
			} else {
				$this->Flash->set('There was a problem with your payment information.');
			}
		}
		$addresses = $this->Order->Customer->Address->find('list', array(
			'conditions' => array(
				'Address.customers_id' => $this->Auth->user('customers_id')
			)
		));

		$zones = $this->Order->Customer->Address->Zone->find('list');
		$selected = $this->Auth->user('customers_default_address_id');
		if (isset($this->request->data['Customer']['customers_default_address_id'])) {
			$selected = $this->request->data['Customer']['customers_default_address_id'];
		}
		$orderCharges = $this->Order->OrderTotal->findAllChargesForOrderId($id);
		$this->set(compact('order', 'addresses', 'zones', 'selected', 'orderCharges'));
	}

	/**
	 * employee_search
	 *
	 * @return mixed
	 */
	public function employee_search() {
		$this->manager_search();
		return $this->render('manager_search');
	}

	/**
	 * manager_search
	 *
	 * @return void
	 */
	public function manager_search() {
		$this->request->allowMethod('get');
		$search = $this->request->query('q');
		$showStatus = null;
		$conditions = $this->getConditions($search);
		if (!empty($conditions)) {
			$fromThePast = $this->request->query('from_the_past');
			if (!empty($fromThePast)) {
				$conditions[0]['AND'][] = [
					'Order.date_purchased >= ' => date_create($fromThePast)->format('Y-m-d H:i:s')
				];
			}
			$showStatus = $this->request->query('showStatus');
			if (!empty($showStatus)) {
				$conditions[0]['AND'][] = [
					'Order.orders_status' => $showStatus
				];
			}
			$this->Paginator->settings['conditions'] = $conditions;
			$this->Paginator->settings['contain'] = [
				'OrderStatus',
				'Customer' => ['customers_id', 'billing_id', 'customers_email_address'],
				'OrderTotal',
			];
			$results = $this->paginate();
			$customRequests = $this->Order->CustomPackageRequest->findAllForOrder($results);
		} else {
			$results = false;
			$customRequests = null;
		}
		if (empty($fromThePast)) {
			$fromThePast = Configure::read('Search.date.default');
		}
		$statusFilterOptions = $this->Order->OrderStatus->find('list');
		$userIsManager = $this->_userIsManager();
		$this->set(compact(
			'search',
			'results',
			'fromThePast',
			'statusFilterOptions',
			'showStatus',
			'userIsManager',
			'customRequests'
		));
	}

	/**
	 * getConditions
	 *
	 * @param array $query An array of terms to add to the search conditions
	 * @return array
	 */
	protected function getConditions($query) {
		$terms = explode(' ', $query);
		$fieldRegex = array(
			'Order.orders_id LIKE' => '/^[\d]{0,15}/i',
			'Order.usps_track_num LIKE' => '/[a-z\d]+/i',
			'Order.inbound_tracking LIKE' => '/[a-z\d]+/i',
		);
		$conditions = array();

		$i = 0;
		foreach ($terms as $term) {
			foreach ($fieldRegex as $field => $regex) {
				if (preg_match($regex, $term)) {
					$perms = $this->getTermSearchPermutations($field, $term);
					foreach ($perms as $perm) {
						$conditions[$i]['OR'][] = array($field => $perm);
					}
				}
			}
			$i++;
		}

		return $conditions;
	}

	/**
	 * Accepts a string and returns an array with keys of fields and
	 * values of strings to search.
	 *
	 * @param string $field A SQL fragment
	 * @param string $term The term to search for
	 * @return array
	 */
	protected function getTermSearchPermutations($field, $term) {
		$perms = array();
		$perms[] = '%' . $term . '%';
		return $perms;
	}

	/**
	 * manager_view
	 *
	 * @param mixed $id The orderid
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_view($id = null) {
		$this->request->allowMethod('get');
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		$options = array(
			'conditions' => array(
				'Order.' . $this->Order->primaryKey => $id
			),
			'contain' => Array(
				'Customer',
				'OrderStatus',
				'CustomPackageRequest',
			),
		);
		$order = $this->Order->find('first', $options);
		if (!empty($order['Order']['creator_id'])) {
			$this->loadModel('Admin');
			$creatorEmail = $this->Admin->field('email', ['id' => $order['Order']['creator_id']]);
			$this->set('creator', (!empty($creatorEmail) ? $creatorEmail : $order['Order']['creator_id']));
		}
		$currentStatusHistory = $this->Order->OrderStatusHistory->findCurrentStatusForOrderId($id);
		$statusHistories = $this->Order->OrderStatusHistory->findStatusesForOrderId($id, array('contain' => array('OrderStatus')));
		$ordersStatuses = $this->Order->OrderStatus->find('list');
		$orderCharges = $this->Order->OrderTotal->findAllChargesForOrderId($id);
		$invoiceCustomer = $this->Order->checkForInvoiceCustomer($order['Customer']);

		$order = $this->Order->findOrderForCharge($id);
		$action = 'Print';
		$level = isset($this->request->params['manager']) ? ['manager' => true] : ['employee' => true];
		if ($order['Order']['mail_class'] != 'FEDEX') {
			$endiciaXml = $this->initEndiciaXml();
			$xml = $endiciaXml->createXml($order);
			$xml = trim($xml['xml']);
			$mailClass = 'usps';
			$url = '#';
			$reprint = false;
		} else {
			$mailClass = 'fedex';
			$url = [
				'controller' => 'orders',
				'action' => 'print_fedex',
				'id' => $order['Order']['orders_id'],
				key($level) => current($level),
			];
			$reprint = $this->Order->OrderData->checkOrderData($id, 'fedex-zpl');
			if ($reprint) {
				$url['reprint'] = 'reprint';
				$action = 'Reprint';
			}
			$xml = null;
		}

		$this->set(compact(
			'order',
			'currentStatusHistory',
			'statusHistories',
			'ordersStatuses',
			'orderCharges',
			'invoiceCustomer',
			'xml',
			'mailClass',
			'url',
			'reprint',
			'action',
			'level'
		));
	}

	/**
	 * Allows manager to add a new order.
	 *
	 * @param mixed $customerId The customer to create an order for.
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_add($customerId = null) {
		$this->request->allowMethod(['get', 'post']);
		if (!$customer = $this->Order->Customer->findByCustomersId($customerId)) {
			throw new NotFoundException('The customer could not be found');
		}

		if (!$customersAddresses = $this->Order->Customer->Address->find('list', [
			'conditions' => ['Address.customers_id' => $customerId]
		])) {
			$this->Flash->set(__('This customer has insufficient address data for an order to be created.'));
			return $this->redirect(['controller' => 'admins', 'action' => 'index']);
		}

		if (isset($this->request->data['Order'])) {
			$this->request->data['Order'] = $this->Order->setWeight($this->request->data['Order']);
			$this->Order->checkOrderKeys($this->request->data['Order']);
		}

		$this->request->data['Order']['customers_id'] = $customerId;
		$this->request->data['Order']['customers_telephone'] = $customer['Customer']['customers_telephone'];
		$this->request->data['Order']['customers_email_address'] = $customer['Customer']['customers_email_address'];
		$this->_setOrderDefaults($customer);
		$this->_setCustomersDetails($customerId);
		$this->request->data['Order']['creator_id'] = $this->Session->read('Auth.User.id');

		if ($this->request->is('post')) {
			// Marshal the address data into the request data in 'Order'
			$this->request->data = array_merge(
				$this->request->data,
				$this->_setAddressesForOrder($this->request->data, $customerId)
			);

			// Insurance
			if (isset($this->request->data['Order']['insurance']) && !$this->request->data['Order']['insurance']) {
				unset($this->request->data['Order']['insurance_coverage']);
			}

			// Invoice customers
			if ($this->Order->checkForInvoiceCustomer($customer['Customer'])) {
				$this->request->data['Order']['billing_status'] = 5;
			}

			if ($this->Order->validShipping($this->request->data) && $this->Order->saveOrder($this->request->data)) {
				$msg = null;
				if (!$this->addPostage($this->Order->getLastInsertId())) {
					$msg = 'IMPORTANT: The order has been created but postage was NOT automatically added.';
				}
				$this->Order->OrderTotal->updateTotal($this->Order->getLastInsertId());
				$this->Order->CustomPackageRequest->updateOrderId(
					$this->request->data,
					$this->Order->getLastInsertId()
				);
				if ($msg) {
					$this->Flash->set(__($msg));
				}
				return $this->redirect(['action' => 'charge', 'id' => $this->Order->getLastInsertId()]);
			} else {
				if (!empty($this->Order->validationErrors['amazon_track_num'])) {
					$this->Order->validationErrors['inbound_tracking_number'] = $this->Order->validationErrors['amazon_track_num'];
				}
				if (!empty($this->Order->validationErrors['fedex_track_num'])) {
					$this->Order->validationErrors['inbound_tracking_number'] = $this->Order->validationErrors['fedex_track_num'];
				}
				if (!empty($this->Order->validationErrors['ups_track_num'])) {
					$this->Order->validationErrors['inbound_tracking_number'] = $this->Order->validationErrors['ups_track_num'];
				}
				if (!empty($this->Order->validationErrors['usps_track_num_in'])) {
					$this->Order->validationErrors['inbound_tracking_number'] = $this->Order->validationErrors['usps_track_num_in'];
				}
				if (!empty($this->Order->validationErrors['dhl_track_num'])) {
					$this->Order->validationErrors['inbound_tracking_number'] = $this->Order->validationErrors['dhl_track_num'];
				}
				if (!$this->Order->validShipping($this->request->data)) {
					$this->Order->validationErrors['delivery_address_id'] = ['A US address is required for FedEx shipments.'];
				}
				$this->Flash->set(__('The order could not be saved. Please, try again.'));
			}
		}

		$customersOptions = [];
		$customersOptions['conditions'] = ['Customer.customers_id' => $customerId];
		$this->set('customers', $this->Order->Customer->find('list', $customersOptions));
		$orderStatuses = $this->Order->OrderStatus->find('list');
		$requests = $this->Order->CustomPackageRequest->findOpen($customerId, 1);
		$this->set(compact(
			'customer',
			'orderStatuses',
			'customersAddresses',
			'requests'
		));
	}

	/**
	 * Employee wrapper around manager_add.
	 *
	 * @param mixed $customerId The customer id
	 * @return mixed
	 */
	public function employee_add($customerId = null) {
		$this->manager_add($customerId);
		return $this->render('manager_add');
	}

	/**
	 * Sets up the view depending on if a customer id was passed in the URL.
	 *
	 * @param mixed $customerId The customer id
	 * @return void
	 */
	protected function _setCustomersDetails($customerId) {
		$this->set('customerIsReadonly', false);
		if (!empty($customerId)) {
			$this->set('customerIsReadonly', true);
		}
	}

	/**
	 * _marshalAddressTo
	 *
	 * @param string $type An address type
	 * @param array $address An Address array
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function _marshalAddressTo($type, $address) {
		if (!in_array($type, array('customers', 'delivery', 'billing'))) {
			throw new InvalidArgumentException('Invalid marshalling type');
		}
		$address['Address']['__name'] = $address['Address']['entry_firstname'] . ' ' . $address['Address']['entry_lastname'];

		$map = array(
			'%s_name' => '__name',
			'%s_company' => 'entry_company',
			'%s_street_address' => 'entry_street_address',
			'%s_suburb' => 'entry_suburb',
			'%s_city' => 'entry_city',
			'%s_postcode' => 'entry_postcode',
		);

		$return = array();
		foreach ($map as $orderKey => $addressKey) {
			$return[sprintf($orderKey, $type)] = $address['Address'][$addressKey];
		}
		if ($return[sprintf('%s_company', $type)] == '') {
			$return[sprintf('%s_company', $type)] = substr($address['Address']['__name'], 0, 32);
		}
		$return[sprintf('%s_state', $type)] = $address['Zone']['zone_code'];
		$return[sprintf('%s_country', $type)] = $address['Country']['countries_name'];
		$return[sprintf('%s_address_format_id', $type)] = 2;

		return $return;
	}

	/**
	 * employee_update_status
	 *
	 * @param mixed $id The order id
	 * @return void
	 */
	public function employee_update_status($id = null) {
		$this->manager_update_status($id);
	}

	/**
	 * manager_update_status
	 *
	 * @param mixed $id The order id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_update_status($id = null) {
		$this->request->allowMethod(array('post', 'put'));
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}

		if (empty($this->request->data['Order']['orders_status'])) {
			$this->Flash->set(__('Missing required key: Order.orders_status. Please, try again.'));
			return $this->redirect($this->referer());
		}
		if ($this->Order->markAs($id, $this->request->data['Order']['orders_status'], $this->request->data['Order'])) {
			if (!empty($this->request->data['Order']['notify_customer'])) {
				$this->Order->sendStatusUpdateEmail($id);
			}
			$this->Flash->set(__('The status for order # %s has been updated.', $id));
			if ($this->_newStatusIsShipped()) {
				return $this->redirect(array(
					'controller' => 'admins',
					'action' => 'index',
					$this->request->prefix => true,
				));
			}
			return $this->redirect(array('action' => 'view', 'id' => $id));
		} else {
			$this->Flash->set(__('The order\'s status could not be saved. Please, try again.'));
			return $this->redirect($this->referer());
		}
	}

	/**
	 * Deletes an order, along with `orders_status_history` records (by cascading)
	 * and `orders_total` records by the deleteAll() method.
	 *
	 * @param mixed $id The order id
	 * @param mixed $customerId The customer id
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_delete($id = null, $customerId = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Order->delete()) {
			$options = array(
				'OrderTotal.orders_id' => $id,
			);
			$this->Order->OrderTotal->deleteAll($options, false);
			$this->Flash->set(__('The order has been deleted.'));
		} else {
			$this->Flash->set(__('The order could not be deleted. Please, try again.'));
		}
		if ($customerId) {
			return $this->redirect(array(
				'controller' => 'customers',
				'action' => 'view',
				'id' => $customerId,
			));
		}
		return $this->redirect(array('action' => 'search'));
	}

	/**
	 * manager_mark_as_shipped
	 *
	 * @param mixed $id The order id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_mark_as_shipped($id = null) {
		$this->request->allowMethod('post');
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException('Invalid order number.');
		}

		if ($this->Order->markAsShipped()) {
			$this->Order->sendStatusUpdateEmail($id);
			$this->Flash->set(sprintf('Order #%d has been marked as shipped.', $id));
		} else {
			$this->Flash->set(sprintf('Order #%d could not be marked as shipped. Please try again.', $id));
		}

		return $this->redirect(array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $id,
		));
	}

	/**
	 * Allows a manager to review, edit totals, and charge an existing order.
	 *
	 * @param mixed $id The order id.
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_charge($id = null) {
		$this->request->allowMethod(array('get', 'post', 'put'));
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException('Invalid order number.');
		}
		$order = $this->Order->findOrderForCharge($id);
		$allowCharge = $this->Order->checkIfOrderCanBeCharged($order);
		$this->request->data['Order']['orders_id'] = $id;
		$this->set('feeRates', Configure::read('Orders.feeRates'));

		if ($this->request->is(array('post', 'put')) && $allowCharge['allow']) {
			$order = $this->_prepareChargeData($order);
			$invoiceCustomer = $this->Order->checkForInvoiceCustomer($order['Customer'], true);

			if ($this->request->data['submit'] == 'postage') {
				if ($this->Order->usesFedex($order)) {
					$rateBackend = 'Fedex';
				} else {
					$rateBackend = Configure::check('ShippingApis.Rates.backend') ? Configure::read('ShippingApis.Rates.backend') : $this->_defaultRateBackend;
				}
				$rates = $this->{'get' . $rateBackend . 'Rates'}($order);
				$this->request->data['OrderTotal'] = $order['OrderTotal'];
				$this->set(compact('order', 'invoiceCustomer', 'allowCharge', 'rates'));
				return;
			}

			// Set `mail_class` to `fedex` if FedEx is going to be used for shipping
			if ($this->Order->usesFedex($order)) {
				$this->request->data['Order']['mail_class'] = 'fedex';
			}
			$saved = $this->Order->saveOrderForCharge($this->request->data);

			if ($saved) {
				$newOrderTotal = $this->Order->OrderTotal->findByOrdersId($id);
				$order['OrderTotal'] = $newOrderTotal['OrderTotal'];
				if ($this->request->data['submit'] == 'charge') {

					if ($charge = $this->_processCharge($invoiceCustomer, $order)) {
						$action = ($invoiceCustomer ? 'invoiced' : 'charged to card');
						if ($charge === 'not recorded') {
							$this->Flash->set('Order was successfully ' . $action . ', but there was a problem saving details back to the record.');
						}
					} else {
						$this->_markFailedPayment($id);
						$this->Flash->set('Order payment could not be processed. Customer has been notified of awaiting payment status. The error was: ' . $this->Payment->lastErrorMessage);
						$this->Order->OrderStatusHistory->recordChargeFailed($order);
					}

					$this->Order->sendStatusUpdateEmail($id);
				}

				return $this->redirect(array('action' => 'view', 'id' => $id));
			} else {
				$this->Flash->set('The amounts could not be saved before charging the card. The card was not charged');
			}
		} // end post
		if (empty($this->request->data['OrderShipping'])) {
			$this->request->data['OrderShipping'] = $order['OrderShipping'];
			$this->request->data['OrderShipping']['value'] = number_format($order['OrderShipping']['value'], 2);
		}
		if (empty($this->request->data['OrderStorage'])) {
			$this->request->data['OrderStorage'] = $order['OrderStorage'];
			$this->request->data['OrderStorage']['value'] = number_format($order['OrderStorage']['value'], 2);
		}
		if (empty($this->request->data['OrderInsurance'])) {
			$this->request->data['OrderInsurance'] = $order['OrderInsurance'];
			$this->request->data['OrderInsurance']['value'] = number_format($order['OrderInsurance']['value'], 2);
		}
		if (empty($this->request->data['OrderFee'])) {
			$order['OrderFee']['value'] = $order['OrderFee']['value'] ?: $this->Order->OrderFee->getFee($order['Order']['weight_oz']);
			$this->request->data['OrderFee'] = $order['OrderFee'];
			$this->request->data['OrderFee']['value'] = number_format($order['OrderFee']['value'], 2);
		}
		if (empty($this->request->data['OrderRepack'])) {
			$this->request->data['OrderRepack'] = $order['OrderRepack'];
			$this->request->data['OrderRepack']['value'] = number_format($order['OrderRepack']['value'], 2);
		}
		if (empty($this->request->data['OrderBattery'])) {
			$this->request->data['OrderBattery'] = $order['OrderBattery'];
			$this->request->data['OrderBattery']['value'] = number_format($order['OrderBattery']['value'], 2);
		}
		if (empty($this->request->data['OrderReturn'])) {
			$this->request->data['OrderReturn'] = $order['OrderReturn'];
			$this->request->data['OrderReturn']['value'] = number_format($order['OrderReturn']['value'], 2);
		}
		if (empty($this->request->data['OrderMisaddressed'])) {
			$this->request->data['OrderMisaddressed'] = $order['OrderMisaddressed'];
			$this->request->data['OrderMisaddressed']['value'] = number_format($order['OrderMisaddressed']['value'], 2);
		}
		if (empty($this->request->data['OrderShipToUS'])) {
			$this->request->data['OrderShipToUS'] = $order['OrderShipToUS'];
			$this->request->data['OrderShipToUS']['value'] = number_format($order['OrderShipToUS']['value'], 2);
		}
		if (empty($this->request->data['OrderSubtotal'])) {
			$this->request->data['OrderSubtotal'] = $order['OrderSubtotal'];
			$this->request->data['OrderSubtotal']['value'] = number_format($order['OrderSubtotal']['value'], 2);
		}

		if (!empty($this->request->data['OrderBattery']['value']) && $this->request->data['OrderBattery']['value'] > 0) {
			$this->request->data['checkbox']['OrderBattery'] = 1;
		}
		if (!empty($this->request->data['OrderReturn']['value']) && $this->request->data['OrderReturn']['value'] > 0) {
			$this->request->data['checkbox']['OrderReturn'] = 1;
		}
		if (!empty($this->request->data['OrderMisaddressed']['value']) && $this->request->data['OrderMisaddressed']['value'] > 0) {
			$this->request->data['checkbox']['OrderMisaddressed'] = 1;
		}
		if (!empty($this->request->data['OrderShipToUS']['value']) && $this->request->data['OrderShipToUS']['value'] > 0) {
			$this->request->data['checkbox']['OrderShipToUS'] = 1;
		}

		$invoiceCustomer = $this->Order->checkForInvoiceCustomer($order['Customer']);
		$this->request->data['OrderTotal'] = $order['OrderTotal'];
		$this->set(compact('order', 'invoiceCustomer', 'allowCharge'));
	}

	/**
	 * Employee wrapper around manager_charge.
	 *
	 * @param int $id The order id
	 * @return mixed
	 */
	public function employee_charge($id = null) {
		$this->manager_charge($id);
		return $this->render('manager_charge');
	}

	/**
	 * The associated OrderTotal records need to have their id set before
	 * saving the associated data. This ensures they are set.
	 *
	 * @param array $order The order array.
	 * @return void
	 */
	protected function enforceOrderTotalKeys($order) {
		$records = array(
			'OrderShipping',
			'OrderStorage',
			'OrderInsurance',
			'OrderFee',
			'OrderRepack',
			'OrderBattery',
			'OrderReturn',
			'OrderMisaddressed',
			'OrderShipToUS',
		);
		foreach ($records as $key) {
			$this->request->data[$key]['orders_total_id'] = $order[$key]['orders_total_id'];
		}
	}

	/**
	 * employee_view
	 *
	 * @param mixed $id The order id
	 * @return mixed
	 */
	public function employee_view($id = null) {
		return $this->manager_view($id);
	}

	/**
	 * api_view
	 *
	 * @param mixed $id The order id
	 * @return void
	 * @throws NotFoundException
	 */
	public function api_view($id = null) {
		$this->request->allowMethod('get');
		$this->Order->id = $id;

		if (!$this->Order->exists()) {
			throw new NotFoundException('Order #' . $id . ' not found');
		}

		$order = $this->Order->find('first', array(
			'conditions' => array('Order.orders_id' => $id),
		));

		$this->set('order', $order);
	}

	/**
	 * Changes the status of an order. Relevant data fields are:
	 *
	 * * orders_status
	 * * status_history_comments
	 * * usps_track_num
	 * * notify_customer
	 *
	 * On success, responds with status 204 No Content. May also respond with 404 and 400.
	 *
	 * @return void
	 * @throws NotFoundException
	 * @throws BadRequestException
	 */
	public function api_changestatus() {
		$this->request->addDetector(
			'patch',
			array('env' => 'REQUEST_METHOD', 'value' => 'PATCH')
		);
		$this->request->allowMethod('patch');
		$id = Hash::get($this->request->data, 'data.id');
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException('Order not found');
		}

		$data = Hash::get($this->request->data, 'data.attributes');

		if (!$this->_changeOrderStatus($id, $data)) {
			throw new BadRequestException('Unable to update order status.');
		}

		$this->response->statusCode(204);
	}

	/**
	 * Adds a new order for customer $id (customer id or billing id)
	 *
	 * @param mixed $id either `customers_id` or `billing_id`
	 * @return void
	 * @throws NotFoundException
	 * @throws BadRequestException
	 */
	public function api_add($id = null) {
		$this->request->allowMethod('post');

		$customer = $this->Order->Customer->findByBillingId($id);
		if (!$customer) {
			$customer = $this->Order->Customer->findByCustomersId($id);
		}

		if (!$customer) {
			throw new NotFoundException(__('Customer %s not found', $id));
		}

		$model = Inflector::classify(Hash::get($this->request->data, 'data.type'));
		if ($model !== 'Order') {
			throw new BadRequestException('Invalid model');
		}

		$requestData = Hash::get($this->request->data, 'data.attributes');
		$this->Order->checkOrderKeys($requestData);
		$data = array($model => $requestData);

		$data = array_merge($data, $this->_setDataForOrder($data, $customer));
		$data = array_merge($data, $this->_setAddressesForOrder($data, $customer['Customer']['customers_id'], true));

		if ($this->Order->saveOrder($data)) {
			$order = $this->Order->findByOrdersId($this->Order->id);
			$this->addPostage($this->Order->id);
			$this->Order->OrderTotal->updateTotal($this->Order->id);
			$this->Order->CustomPackageRequest->updateOrderId(
				$data,
				$this->Order->id
			);

			$this->response->statusCode(201);
			$this->set(compact('order'));
			return;
		} else {
			$errorMsg = Hash::extract($this->Order->invalidFields(), '{s}.0');
			$errorMsg = (isset($errorMsg[0]) ? $errorMsg[0] : "The $model could not be saved");
			throw new BadRequestException('Error: ' . $errorMsg);
		}
	}

	/**
	 * Charges an order.
	 *
	 * @param mixed $id The order id
	 * @return void
	 * @throws NotFoundException
	 * @throws BadRequestException
	 * @throws BaseSerializerException
	 */
	public function api_charge($id = null) {
		$this->request->addDetector(
			'patch',
			array('env' => 'REQUEST_METHOD', 'value' => 'PATCH')
		);
		$this->request->allowMethod('patch');

		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException('Invalid order number.');
		}

		$model = Inflector::classify(Hash::get($this->request->data, 'data.type'));
		if ($model != 'Order') {
			throw new BadRequestException('Invalid model');
		}

		$order = $this->Order->findOrderForCharge($id);
		$allowCharge = $this->Order->checkIfOrderCanBeCharged($order);
		if (!$allowCharge['allow']) {
			throw new BadRequestException($allowCharge['message']);
		}

		$requestAttributes = Hash::get($this->request->data, 'data.attributes');
		$requestRelationships = Hash::get($this->request->data, 'data.relationships');

		$relationships = array();
		if (is_array($requestRelationships)) {
			foreach ($requestRelationships as $k => $v) {
				$relationships[$k] = $v['data'];
			}
		}
		$this->request->data = $requestAttributes + $relationships;

		$this->request->data['Order']['orders_id'] = $id;
		$order = $this->_prepareChargeData($order);
		$invoiceCustomer = $this->Order->checkForInvoiceCustomer($order['Customer'], true);
		$saved = $this->Order->saveOrderForCharge($this->request->data);

		if ($saved) {
			$newOrderTotal = $this->Order->OrderTotal->findByOrdersId($id);
			$order['OrderTotal'] = $newOrderTotal['OrderTotal'];
			if ($this->request->data['submit'] == 'charge') {
				$options = array(
					'address' => $this->Order->addressForPayment(),
					'total' => sprintf('%2.f', $order['OrderTotal']['value']),
					'description' => 'Order #' . $this->Order->id
				);

				if ($invoiceCustomer) {
					if ($this->Order->recordInvoicePayment($order['Customer'])) {
						$order = $this->Order->findOrderForCharge($id);
						$this->Order->sendStatusUpdateEmail($id);
						return $this->_sendChargeResponse($order, $this->request->data['submit']);
					} else {
						$msg = 'Order was successfully invoiced, but there was a problem saving details.';
						$errorData = array_merge(['orders_id' => $id], $this->Order->validationErrors);
						$this->logBaseSerializerException($msg, 'Order', 'orders', $errorData);
						throw new BaseSerializerException('Error', $msg, '400');
					}
				} elseif ($this->Payment->charge($order['Customer'], $options)) {
					if ($this->Order->recordPayment($order['Customer'], $this->request->data)) {
						// charge successful
						$order = $this->Order->findOrderForCharge($id);
						$this->Order->sendStatusUpdateEmail($id);
						return $this->_sendChargeResponse($order, $this->request->data['submit']);
					} else {
						// charge successful - saving failed
						$msg = 'The charge was successful but could not be recorded.';
						$errorData = array_merge(['orders_id' => $id], $this->Order->validationErrors);
						$this->logBaseSerializerException($msg, 'Order', 'orders', $errorData);
						throw new BaseSerializerException('Error', $msg, '400');
					}
				}
				// charge failed
				$error = $this->Payment->lastErrorMessage;
				$this->Order->sendStatusUpdateEmail($id);
				$msg = 'Charge Error.';
				$errorData = array_merge(['orders_id' => $id], ['message' => $error]);
				$this->logBaseSerializerException($msg, 'Order', 'orders', $errorData);
				throw new BaseSerializerException($msg, $error, '400');
			} else {
				$order = $this->Order->findOrderForCharge($id);
				return $this->_sendChargeResponse($order, $this->request->data['submit']);
			}
		} else {
			// save failed
			$msg = 'The amounts could not be saved before charging the card. The card was not charged.';
			$errorData = array_merge(['orders_id' => $id], $this->Order->validationErrors);
			$this->logBaseSerializerException($msg, 'Order', 'orders', $errorData);
			throw new BaseSerializerException($msg, 'Error', '400');
		}
	}

	/**
	 * Sets required order and request data before saving before a charge/invoice
	 *
	 * @param array $order An Order data array
	 * @return array
	 */
	protected function _prepareChargeData($order) {
		if (empty($order['OrderFee']['orders_total_id'])) {
			$order['OrderFee'] = $this->Order->addDefaultOrderFee();
		}
		$this->request->data['Order']['last_modified'] = date_format(date_create(), 'Y-m-d H:i:s');
		$this->request->data['Order']['orders_status'] = $order['Order']['orders_status'];
		if ($order['Order']['orders_status'] == 1) {
			$this->request->data['Order']['orders_status'] = 2;
		}
		$this->enforceOrderTotalKeys($order);
		return $order;
	}

	/**
	 * Utility method for success responses from the API charge endpoint.
	 *
	 * @param array $order An Order data array
	 * @param string $type The type of charge
	 * @return mixed
	 */
	protected function _sendChargeResponse($order, $type) {
		$models = array(
			'OrderShipping',
			'OrderStorage',
			'OrderInsurance',
			'OrderFee',
			'OrderRepack',
			'OrderBattery',
			'OrderReturn',
			'OrderMisaddressed',
			'OrderShipToUS',
			'OrderSubtotal',
			'OrderTotal',
		);
		$relationships = array();
		foreach ($models as $model) {
			$relationships[$model] = array(
				'data' => array(
					'value' => $order[$model]['value'],
				),
			);
		}
		$response = array(
			'data' => array(
				'type' => 'orders',
				'id' => $order['Order']['orders_id'],
				'attributes' => array(
					'orders_status' => $order['Order']['orders_status'],
				),
				'relationships' => $relationships,
			),
		);
		if ($type == 'charge') {
			$response['data']['attributes']['payment_method'] = $order['Order']['payment_method'];
		}

		$this->response->statusCode(200);
		$this->response->body(json_encode($response));
		return $this->response->send();
	}

	/**
	 * Attempts to change the status of an order. If the status is changed to
	 * '3' (shipped), the `usps_track_num` field is required. If
	 * `notify_customer` is present or set to '1', the customer will be sent
	 * an email notification.
	 *
	 * @param int $id The order id
	 * @param array $data The Order data array
	 * @return bool | void
	 * @throws BadRequestException
	 */
	protected function _changeOrderStatus($id, $data) {
		if (empty($data['orders_status'])) {
			throw new BadRequestException('Missing required key: orders_status');
		}

		if ($data['orders_status'] == 3 && empty($data['usps_track_num'])) {
			throw new BadRequestException('Tracking number required for shipped orders.');
		}

		if ($this->Order->markAs($id, $data['orders_status'], $data)) {
			if (!empty($data['notify_customer'])) {
				$this->Order->sendStatusUpdateEmail($id);
			}
			return true;
		} else {
			throw new BadRequestException('Unable to update order status.');
		}
	}

	/**
	 * Set default address ids if ids aren't specifically passed in.
	 *
	 * @param array $data An Order data array
	 * @param string $customerId A customer id
	 * @return array $data The modified data
	 * @throws BadRequestException
	 */
	protected function _fallbackToDefaultAddresses($data, $customerId) {
		$addressTypes = [
			'customers' => 'default',
			'delivery' => 'shipping',
			'billing' => 'default',
		];

		$customer = [];
		foreach ($addressTypes as $onOrder => $onCustomer) {
			if (empty($data['Order'][$onOrder . '_address_id'])) {
				$customer = (!empty($customer)) ? $customer : $this->Order->Customer->read(null, $customerId);

				if (empty($customer['Customer'])) {
					throw new BadRequestException('Cannot retrieve addresses from invalid customer #' . $customerId . '.');
				}

				$data['Order'][$onOrder . '_address_id'] = $customer['Customer']['customers_' . $onCustomer . '_address_id'];
			}
		}

		return $data;
	}

	/**
	 * Sets address fields for an order before saving.
	 *
	 * @param array $data An Order data array
	 * @param string $customerId The customer id
	 * @param bool $fallbackToDefaults Determine if _fallbackToDefaultAddresses() is used
	 * @return array $data The modified data
	 * @throws BadRequestException
	 */
	protected function _setAddressesForOrder($data, $customerId, $fallbackToDefaults = false) {
		if ($fallbackToDefaults) {
			$data = $this->_fallbackToDefaultAddresses($data, $customerId);
		}

		// Marshal the address data into the request data in 'Order'
		$addressTypes = [
			'customers',
			'delivery',
			'billing',
		];

		foreach ($addressTypes as $type) {
			if (!empty($data['Order'][$type . '_address_id'])) {
				$address = $this->Order->Customer->Address->get($data['Order'][$type . '_address_id'], array(
					'contain' => array(
						'Country',
						'Zone',
					),
				));

				if (!empty($address) && $address['Address']['customers_id'] !== (string)$customerId) {
					throw new BadRequestException('Requested address # ' . $address['Address']['customers_id'] . ' does not belong to customer.');
				}

				$data['Order'] = Hash::merge($data['Order'], $this->_marshalAddressTo($type, $address));
			}
		}

		return $data;
	}

	/**
	 * Sets required fields for an order before saving.
	 *
	 * @param array $data An Order data array
	 * @param array $customer A Customer data array
	 * @return array $data The modified data
	 */
	protected function _setDataForOrder($data, $customer) {
		$data['Order']['customers_id'] = $customer['Customer']['customers_id'];
		$data['Order']['customers_telephone'] = $customer['Customer']['customers_telephone'];
		$data['Order']['customers_email_address'] = $customer['Customer']['customers_email_address'];

		if (empty($data['Order']['insurance_coverage'])) {
			$data['Order']['insurance_coverage'] = $customer['Customer']['insurance_amount'];
		}
		if (isset($data['Order']['insurance']) && $data['Order']['insurance'] == 'false') {
			$data['Order']['insurance_coverage'] = '';
		}

		if ($this->Order->checkForInvoiceCustomer($customer['Customer'])) {
			$data['Order']['billing_status'] = 5;
		}

		if (!isset($data['Order']['comments'])) {
			$data['Order']['comments'] = '';
		}

		return $data;
	}

	/**
	 * Checks the submitted address data and queries for it and/or related Zone
	 * record and then returns the data.
	 *
	 * @return mixed
	 */
	protected function _checkOrSetAddress() {
		if ($this->request->data['Customer']['customers_default_address_id'] != 'custom') {
			return $this->Order->Customer->Address->findForPayment(
				$this->request->data['Customer']['customers_default_address_id'],
				$this->Auth->user('customers_id')
			);
		}

		$this->request->data['Address']['customers_id'] = $this->Auth->user('customers_id');
		$this->Order->Customer->Address->set($this->request->data);
		if ($this->Order->Customer->Address->validates()) {
			$address = $this->Order->Customer->Address->attachZone($this->request->data);
			return $this->Order->Customer->Address->attachCountry($address);
		}

		return false;
	}

	/**
	 * Report for order totals
	 *
	 * @return void
	 */
	public function manager_report() {
		$this->request->allowMethod('get', 'post');

		$statusFilterOptions = $this->Order->OrderStatus->find('list');
		$validIntervals = $this->Order->_validIntervals;
		$validSortFields = $this->Order->_validSortFields;
		$this->set(compact(
			'statusFilterOptions',
			'validIntervals',
			'validSortFields'
		));

		$data = !empty($this->request->data) ? $this->request->data : $this->request->query;
		if (!empty($data)) {
			$this->request->data = $data;
			$interval = !empty($data['interval']) ? $data['interval'] : 'day';
			$results = $this->Order->findOrderTotalsReport($data);
			$this->set(compact(
				'results',
				'interval'
			));
		}
	}

	/**
	 * Populates the order status total counts widget on the reports menu page
	 *
	 * @return array `orders_status_name => count`
	 * @throws ForbiddenException
	 */
	public function manager_statustotals() {
		if (empty($this->request->params['requested'])) {
			throw new ForbiddenException();
		}
		return $this->Order->findTotalsPerStatus();
	}

	/**
	 * Prepares a request array of required fields and queries the USPS API
	 * to find all available rates based on the order package data.
	 *
	 * @param array $order An Order data array
	 * @return array $rates The available rates for the package
	 */
	protected function getUspsRates($order) {
		$Usps = $this->initUsps();
		return $Usps->getRates($order);
	}

	/**
	 * Initialize an instance of the Usps class.
	 *
	 * @param array $config Optional config args
	 * @return object $Usps
	 */
	protected function initUsps($config = []) {
		return new Usps($config);
	}

	/**
	 * Prepares a request array of required fields and queries the Fedex API
	 * to find the available rate based on the order package data.
	 *
	 * @param array $order An order data array
	 * @return array $rates The rate for the package
	 */
	protected function getFedexRates($order) {
		$Fedex = $this->initFedex();
		return $Fedex->getRate($order);
	}

	/**
	 * Initialize an instance of the Fedex class.
	 *
	 * @return object $Fedex
	 */
	protected function initFedex() {
		return new Fedex();
	}

	/**
	 * Initialize an instance of the EndiciaXml class.
	 *
	 * @return object $Usps
	 */
	protected function initEndiciaXml() {
		return new EndiciaXml();
	}

	/**
	 * Adds postage price depending on `mail_class`.
	 *
	 * @param string $id The order id
	 * @return void
	 */
	protected function addPostage($id) {
		$options = [
			'conditions' => [
				'Order.orders_id' => $id,
			],
			'contain' => [
				'OrderShipping',
			],
		];
		$order = $this->Order->find('first', $options);
		if ($this->Order->usesFedex($order)) {
			$rateBackend = 'Fedex';
		} else {
			$rateBackend = Configure::check('ShippingApis.Rates.backend') ? Configure::read('ShippingApis.Rates.backend') : $this->_defaultRateBackend;
		}

		$rates = $this->{'get' . $rateBackend . 'Rates'}($order);
		if (
			!$rates ||
			!is_array($rates) ||
			!Hash::check($rates, '{n}.@CLASSID') ||
			!Hash::check($rates, '{n}.Rate')
		) {
			return false;
		}

		foreach ($rates as $rate) {
			if ($rate['@CLASSID'] == '1' && $order['Order']['mail_class'] == 'PRIORITY') {
				$price = $rate['Rate'];
				break;
			}
			if ($rate['@CLASSID'] == '1058' && $order['Order']['mail_class'] == 'PARCEL') {
				$price = $rate['Rate'];
				break;
			}
			if ($rate['@CLASSID'] == 'FedEx') {
				$price = $rate['Rate'];
				break;
			}
		}
		$data = [
			'OrderShipping' => [
				'orders_total_id' => $order['OrderShipping']['orders_total_id'],
				'text' => '$' . $price,
				'value' => $price,
			],
		];

		return $this->Order->OrderShipping->save($data, ['callbacks' => false]);
	}

	/**
	 * Returns true if the passed in order status is the shipped status.
	 *
	 * @return bool
	 */
	protected function _newStatusIsShipped() {
		return $this->request->data['Order']['orders_status'] === '3';
	}

	/**
	 * Set default order request data based on customer preferences.
	 *
	 * @param array $customer The customer array.
	 * @return void;
	 */
	protected function _setOrderDefaults($customer) {
		$this->_setInsuranceCoverage($customer);
		$this->_setMailClass($customer);
	}

	/**
	 * Set the insurance coverage into the request data if it is not already
	 * set based on the customer's insurance_amount.
	 *
	 * @param array $customer The customer array.
	 * @return void;
	 */
	protected function _setInsuranceCoverage($customer) {
		if (!empty($this->request->data['Order']['insurance_coverage'])) {
			return;
		}
		$this->request->data['Order']['insurance_coverage'] = $customer['Customer']['insurance_amount'];
	}

	/**
	 * Set the order mail class into the request data if it is not already
	 * set based on the customer's default_mail_type.
	 *
	 * @param array $customer The customer array.
	 * @return void;
	 */
	protected function _setMailClass($customer) {
		if (!empty($this->request->data['Order']['mail_class'])) {
			return;
		}
		if (empty($customer['Customer']['default_postal_type'])) {
			return;
		}

		$this->request->data['Order']['mail_class'] =
			$this->Order->mailClassFromCustomer(
				$customer['Customer']['default_postal_type']
			);
	}

	/**
	 * Process the charge or invoice and record payment.
	 *
	 * @param bool $isInvoice Is this an invoice?
	 * @param array $order The order record.
	 * @return bool|string True on success, or string if partial success.
	 */
	protected function _processCharge($isInvoice, $order) {
		$options = array(
			'address' => $this->Order->addressForPayment(),
			'total' => sprintf('%2.f', $order['OrderTotal']['value']),
			'description' => 'Order #' . $this->Order->id
		);

		if ($isInvoice || $this->Payment->charge($order['Customer'], $options)) {
			$method = ($isInvoice ? 'recordInvoicePayment' : 'recordPayment');
			$recorded = $this->Order->{$method}($order['Customer']);
			if (!$recorded) {
				$this->log(
					'OrdersController::_processCharge: ' . json_encode($this->Order->validationErrors),
					'orders'
				);
			}
			return $recorded ? true : 'not recorded';
		}

		return false;
	}

	/**
	 * Mark payment as failed and that customer is notified.
	 *
	 * @param int $id The order id.
	 * @return bool|array The result of the Order save
	 */
	protected function _markFailedPayment($id) {
		$this->request->data['Order']['notify_customer'] = 1;
		if (Configure::read('ZebraLabel.auto') === true) {
			$this->printZebraLabel($id);
		}
		return $this->Order->markAs($id, 2, $this->request->data['Order']);
	}

	/**
	 * Prints or previews a ZPL label
	 *
	 * @param int $id The order id
	 * @param bool $asImage True to return a png (useful for preview)
	 * @return mixed The label in png or raw format, or bool if network printing
	 */
	protected function printZebraLabel($id, $asImage = false) {
		$order = $this->Order->findOrderForCharge($id);
		$data = $this->prepareLabelData($order);
		$ZebraLabel = $this->initZebraLabel([
			'client' => Configure::read('ZebraLabel.client'),
			'method' => Configure::read('ZebraLabel.method'),
		]);
		$label = $ZebraLabel->printLabel($data, $asImage);
		return $label;
	}

	/**
	 * Initialize an instance of the ZebraLabel class.
	 *
	 * @param array $config The config
	 * @return object $ZebraLabel
	 */
	protected function initZebraLabel($config = []) {
		return new ZebraLabel($config);
	}

	/**
	 * Prepares and formats label data into three sections (header, body, footer)
	 * with configurable font sizes per section.
	 *
	 * @param array $order An order array with both `Order` and `Customer` keys
	 * @return array $data The label data in sections
	 */
	protected function prepareLabelData($order) {
		$address = PHP_EOL;
		if (!empty($order['Order']['delivery_company'])) {
			$address .= $order['Order']['delivery_company'] . PHP_EOL;
		}
		$address .= $order['Order']['delivery_street_address'] . PHP_EOL;
		if (!empty($order['Order']['delivery_suburb'])) {
			$address .= $order['Order']['delivery_suburb'] . PHP_EOL;
		}
		$address .= $order['Order']['delivery_city'] . ', ';
		$address .= $order['Order']['delivery_state'] . ' ';
		$address .= $order['Order']['delivery_postcode'] . PHP_EOL;
		$address .= $order['Order']['delivery_country'] . PHP_EOL . PHP_EOL;
		$address .= $order['Order']['customers_email_address'];
		if (!empty($order['Order']['customers_telephone'])) {
			$address .= PHP_EOL . 'Phone: ' . $order['Order']['customers_telephone'];
		}
		$address .= PHP_EOL;

		$data = [
			'header' => [
				'size' => 34,
				'content' => $order['Order']['delivery_name'] . ' - ' . $order['Customer']['billing_id'],
			],
			'body' => [
				'size' => 30,
				'content' => $address,
			],
			'footer' => [
				'size' => 18,
				'content' => 'Charge failed on ' . date('m/d/Y') . ' for order #' . $order['Order']['orders_id'],
			],
		];
		return $data;
	}

	/**
	 * AJAX only method that calls printZebraLabel() to trigger label printing.
	 *
	 * @return mixed Raw data or the send result on success, null on failure.
	 */
	public function manager_print_label() {
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			return $this->printZebraLabel($this->params['id']);
		}
	}

	/**
	 * Employee level wrapper for manager_print_label()
	 *
	 * @return mixed The result of manager_print_label()
	 */
	public function employee_print_label() {
		return $this->manager_print_label();
	}

	/**
	 * AJAX only method that calls printFedexLabel() to trigger label printing.
	 *
	 * @return mixed bool result of writing the label file or raw zpl data
	 */
	public function manager_print_fedex() {
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			$reprint = isset($this->params['reprint']) ? true : false;
			return $this->printFedexLabel($this->params['id'], $reprint);
		}
	}

	/**
	 * Employee level wrapper for manager_print_fedex()
	 *
	 * @return mixed The result of manager_print_label()
	 */
	public function employee_print_fedex() {
		return $this->manager_print_fedex();
	}

	/**
	 * Checks for a an order by ID and reprint status and either fetches the
	 * ZPL data if reprinting (and data exists) or requests a ZPL label from
	 * the FedEx API.
	 *
	 * @param int $orderId An order id
	 * @param bool $reprint True if the label should be reprinted
	 * @return mixed bool result of writing the label file or raw zpl data
	 */
	protected function printFedexLabel($orderId, $reprint = false) {
		if (!$order = $this->Order->findOrderForCharge($orderId)) {
			return false;
		}

		if ($reprint) {
			$label = $this->Order->OrderData->fetchOrderData($orderId, 'fedex-zpl');
			if ($label) {
				return $label;
			}
		}

		$Fedex = $this->initFedex();
		$label = $Fedex->printLabel($order);
		if ($label && Configure::read('ShippingApis.Fedex.label.type') == 'ZPLII') {
			$this->Order->OrderData->saveOrderData($orderId, 'fedex-zpl', $label);
			return $label;
		}
		return (bool)$label;
	}

	/**
	 * Removes all `fedex-zpl` data types for the supplied order ID.
	 *
	 * @param mixed $id The order id
	 * @param string $level The admin level (manager or employee)
	 * @return redirection back to order detail
	 */
	public function manager_delete_label($id = null, $level = 'manager') {
		$id = !is_null($id) ? $id : $this->params['id'];
		if (!$id) {
			$this->Flash->set('The FedEx label could not be found and was not removed.');
		} else {
			if ($this->Order->OrderData->clearOrderData($id, 'fedex-zpl')) {
				$this->Flash->set('The FedEx label has been removed.');
			} else {
				$this->Flash->set('The FedEx label could not be removed.');
			}
		}

		return $this->redirect([
			'action' => 'view',
			$level => true,
			'id' => $id,
		]);
	}

	/**
	 * Employee level wrapper for manager_delete_label()
	 *
	 * @return mixed The result of manager_delete_label()
	 */
	public function employee_delete_label() {
		return $this->manager_delete_label(null, 'employee');
	}
}
