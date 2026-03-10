<?php
/**
 * Customers
 */

App::uses('ActivityComponent', 'Controller/Component');
App::uses('AppController', 'Controller');
App::uses('ApoboxPasswordHasher', 'Controller/Component/Auth');
App::uses('AppEmail', 'Network/Email');
App::uses('Insurance', 'Model');

/**
 * Customers Controller
 *
 * @property	Customer	$Customer
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class CustomersController extends AppController {

	public $uses = [
		'Customer',
		'SearchIndex',
	];

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
	 * Pagination
	 *
	 * @var	string
	 */
	public $paginate = array(
		'Order' => array(
			'order' => array('Order.date_purchased' => 'DESC'),
			'contain' => array(
				'Customer' => array('billing_id'),
				'OrderStatus',
			),
		),
	);

	/**
	 * Helpers
	 *
	 * @var	array
	 */
	public $helpers = array(
		'Tracking',
	);

	/**
	 * beforeFilter
	 *
	 * @return void
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array(
			'add',
			'login',
			'forgot_password',
			'reset_password',
		));
	}

	/**
	 * login method
	 *
	 * @throws	NotFoundException
	 * @return	void
	 */
	public function login() {
		if ($this->request->accepts('application/vnd.api+json')) {
			return $this->_jsonLogin();
		}

		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$this->Flash->set('You have been logged in!');
				$this->Activity->record('login', $this->Auth->user('customers_id'));
				return $this->redirect($this->Auth->redirectUrl());
			}
			$this->Flash->set('Your email address or password was incorrect.');
		}
	}

	/**
	 * API login method
	 *
	 * The api_login method is a hybrid method for customer authentication. It
	 * is called as an API method, but accepts customer authentication data
	 * and checks that the customer can successfully log into the system.
	 *
	 * @return mixed
	 * @throws BaseSerializerException
	 */
	protected function _jsonLogin() {
		$this->request->allowMethod('post');
		$this->initJsonResponse();
		$this->request->data = $this->checkAndSetDataFromJson('Customer');

		$this->Auth->authenticate = $this->formAuthConfig;
		if (!$this->Auth->login()) {
			$msg = 'Your email address or password was incorrect.';
			$this->logBaseSerializerException($msg, 'Customer', 'login-error', $this->request->data);
			throw new BaseSerializerException('Error', $msg, '400');
		}

		$this->Activity->record('login', $this->Auth->user('customers_id'));

		$customer = $this->dataForApi($this->Auth->user());
		$response = array(
			'data' => array(
				'type' => 'customers',
				'id' => $customer['customers_id'],
				'attributes' => $customer,
			),
		);

		$this->response->statusCode(200);
		$this->response->body(json_encode($response));
		return $this->response->send();
	}

	/**
	 * logout method
	 *
	 * @return	void
	 */
	public function logout() {
		if ($this->Auth->logout()) {
			$this->Flash->set('You have been logged out.');
			return $this->redirect($this->Auth->loginAction);
		} else {
			$this->Flash->set('You were unable to be logged out. Please try again.');
		}
	}

	/**
	 * forgot_password method
	 *
	 * @return	void
	 */
	public function forgot_password() {
		if ($this->request->is('post')) {
			$options = [
				'contain' => [],
				'conditions' => [
					'Customer.customers_email_address' => $this->request->data['Customer']['email'],
					'Customer.is_active' => 1,
				],
			];
			$customer = $this->Customer->find('first', $options);

			if (empty($customer)) {
				$this->Flash->set('A customer with the email address you entered could not be found.');
				return;
			}
			$customerId = $customer['Customer']['customers_id'];

			$saved = $this->Customer->PasswordRequest->save(array(
				'PasswordRequest' => array('customer_id' => $customerId)
			));
			if (!$saved) {
				$this->Flash->set('We were unable to create a password reset request for you. Please try again.');
				return;
			}

			$requestId = $this->Customer->PasswordRequest->getInsertId();
			$affiliateLinks = $this->getAffiliates();
			$emailVars = array(
				'customerName' =>
					$customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname'],
				'url' =>
					Router::url(
						array('controller' => 'customers', 'action' => 'reset_password', 'uuid' => $requestId),
						array('full' => true)
				),
				'requestId' => $requestId,
				'affiliateLinks' => $affiliateLinks,
			);

			$task = $this->taskFactory();
			$sent = $task->createJob('AppEmail',
				[
					'method' => 'sendForgotPassword',
					'recipient' => $customer['Customer']['customers_email_address'],
					'vars' => $emailVars
				],
				null,
				'Customer::sendForgotPassword',
				$customer['Customer']['billing_id']
			);
			if (!$sent) {
				$this->Customer->PasswordRequest->delete($requestId);
				$this->Flash->set('There was a problem sending your password reset email.');
				return;
			}
			$this->Flash->set('An email with instructions on how to reset your password has been sent.');

			return $this->redirect(array('controller' => 'customers', 'action' => 'login'));
		}
	}

	/**
	 * reset_password
	 *
	 * @param mixed $uuid The uuid of the PasswordRequest
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function reset_password($uuid = null) {
		$this->Customer->PasswordRequest->deleteExpired();
		$this->Customer->PasswordRequest->id = $uuid;

		if (!$this->Customer->PasswordRequest->exists()) {
			throw new NotFoundException('The password request could not be found or is no longer valid.');
		}

		if ($this->request->is('post')) {
			$password = $this->request->data['Customer']['new_password'];
			if ($password == $this->request->data['Customer']['password_confirm']) {
				$this->Customer->id = $this->Customer->PasswordRequest->read('customer_id');
				if ($this->Customer->saveField('customers_password', $password, true)) {
					$this->Customer->PasswordRequest->delete($uuid);
					$this->Auth->login($this->Customer->read()['Customer']);
					$this->Flash->set('Your password has been changed and you have been logged in.');
					return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
				}
			} else {
				$this->Flash->set('Password and password confirm did not match. Please try again.');
			}

			unset($this->request->data['Customer']);
		}
	}

	/**
	 * Method to request a shipping address from customers that fail to complete
	 * the second step of the profile wizard.
	 *
	 * @throws	NotFoundException
	 * @return	void
	 */
	public function account_incomplete() {
		$customerId = $this->Auth->user($this->Customer->primaryKey);
		if (!$this->Customer->isPartialSignup($customerId)) {
			return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
		}

		$zones = $this->Customer->Address->Zone->find('list');

		$this->set(compact('zones'));

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->Customer->id = $this->request->data['Address']['customers_id'] = $this->Auth->user('customers_id');

			if ($this->Customer->Address->save($this->request->data)) {
				$newAddressId = $this->Customer->Address->getInsertID();
				$customerData = [
					'customers_default_address_id' => $newAddressId,
					'customers_shipping_address_id' => $newAddressId,
				];
			}

			$saveFields = [
				'customers_default_address_id',
				'customers_shipping_address_id',
			];
			if (!empty($newAddressId)) {
				if ($this->Customer->save($customerData, true, $saveFields)) {
					$this->Flash->set('Your account is now complete!');
					return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
				} else {
					$this->Customer->Address->delete($newAddressId);
				}
			}

			$this->Flash->set('There were errors with your input, please try again.');
		}

		if (empty($this->request->data['Address'])) {
			$this->request->data['Address'] = array(
				'entry_firstname' => $this->Auth->user('customers_firstname'),
				'entry_lastname' => $this->Auth->user('customers_lastname'),
			);
		}
	}

	/**
	 * almost_finished method
	 *
	 * @throws	NotFoundException
	 * @return	void
	 */
	public function almost_finished() {
		//Check if the user has a complete account
		$customer = $this->Customer->find('FirstIncompleteBilling', array(
			'conditions' => array(
				'Customer.customers_id' => $this->Auth->user('customers_id'),
			),
		));
		if (empty($customer)) {
			$this->Flash->set('Your account is complete.');
			return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
		}

		$addresses = $this->Customer->Address->find('list', array(
			'conditions' => array(
				'Address.customers_id' => $customer['Customer']['customers_id']
			)
		));

		// Limit zone selection to USA only (country_id 223)
		$zones = $this->Customer->Address->Zone->find('list', [
			'conditions' => [
				'zone_country_id' => 223,
			],
		]);

		$this->set('customer', $this->Auth->user());
		$this->set(compact('addresses', 'zones'));

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->Customer->set($this->request->data);
			$this->Customer->id = $this->request->data['Address']['customers_id'] = $this->Auth->user('customers_id');

			if ($this->request->data['Customer']['customers_default_address_id'] === 'new') {
				if ($this->Customer->Address->save($this->request->data)) {
					$newAddressId = $this->Customer->Address->getInsertID();
					$this->request->data['Customer']['customers_default_address_id'] = $newAddressId;
				}
			} else {
				unset($this->request->data['Address']);
			}

			$saveFields = [
				'customers_default_address_id',
				'cc_firstname',
				'cc_lastname',
				'cc_number',
				'cc_number_encrypted',
				'cc_expires_month',
				'cc_expires_year',
				'cc_cvv',
			];
			if ($this->Customer->save($this->request->data, true, $saveFields)) {
				$this->Flash->set('Your account is now complete!');
				$sourceId = $this->request->data['CustomersInfo']['source_id'];
				unset($this->request->data['CustomersInfo']);
				$this->Activity->record('source', $this->Auth->user('customers_id'), $sourceId);
				return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
			} else {
				if (!empty($newAddressId)) {
					$this->Customer->Address->delete($newAddressId);
				}
			}

			$this->Flash->set('There were errors with your input, please try again.');
		}

		if (empty($this->request->data['Customer'])) {
			$customer['Customer']['cc_cvv'] = '';
			$this->request->data['Customer'] = $customer['Customer'];
		}
		if (empty($this->request->data['Address'])) {
			// Fill out only the customer's name for them
			$this->request->data['Address'] = array(
				'entry_firstname' => $this->Auth->user('customers_firstname'),
				'entry_lastname' => $this->Auth->user('customers_lastname'),
			);
		}
	}

	/**
	 * account
	 * This is the page the user sees after login.
	 *
	 * @return void
	 * @throws NotFoundException
	 */
	public function account() {
		$this->Customer->id = $customerId = $this->Auth->user($this->Customer->primaryKey);
		if (!$this->Customer->exists($customerId)) {
			throw new NotFoundException(__('Invalid customer'));
		}
		if ($this->Customer->isPartialSignup($customerId)) {
			return $this->redirect(array('controller' => 'customers', 'action' => 'account_incomplete'));
		}

		$options = array(
			'contain' => array(
				'Address',
				'DefaultAddress',
				'ShippingAddress',
				'EmergencyAddress',
				'DefaultAddress.Zone',
				'ShippingAddress.Zone',
				'EmergencyAddress.Zone',
				'AuthorizedName'
			),
			'conditions' => array(
				'Customer.' . $this->Customer->primaryKey => $customerId
			)
		);
		$customer = $this->Customer->find('first', $options);
		$insuranceFee = ClassRegistry::init('Insurance')->getFeeForCoverageAmount($customer['Customer']['insurance_amount']);
		$conditions = array('Order.customers_id' => $this->Auth->user('customers_id'));
		$orders = $this->Customer->Order->find('all', array(
			'contain' => array(
				'CustomPackageRequest',
				'OrderStatus',
				'OrderStatusHistory',
				'OrderTotal',
			),
			'conditions' => array($conditions),
			'order' => array('Order.date_purchased' => 'DESC'),
			'limit' => 5,
		));
		$requests = $this->Customer->Order->CustomPackageRequest->findOpen($this->Auth->user('customers_id'));

		$showViewAllLink = false;
		if ($this->Customer->Order->find('count', array('conditions' => $conditions)) > 5) {
			$showViewAllLink = true;
		}

		$awaitingPayments = $this->Customer->Order->find('awaitingPayments', array(
			'contain' => array(
				'OrderTotal',
			),
			'conditions' => array(
				'Order.customers_id' => $this->Auth->user('customers_id')
			)
		));

		$this->set(compact('customer', 'insuranceFee', 'orders', 'requests', 'showViewAllLink', 'awaitingPayments'));
	}

	/**
	 * edit_partial
	 *
	 * @param string $partial The partial to edit
	 * @return mixed
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 */
	public function edit_partial($partial = null) {
		if (! ($this->request->is('get') || $this->request->is('put')) ) {
			throw new MethodNotAllowedException('Method must be one of GET or PUT');
		}

		$fieldList = array(
			'my_info' => array(
				'customers_id' => array(),
				'customers_email_address' => array('label' => array('text' => 'Email')),
				'backup_email_address' => array('label' => array('text' => 'Backup Email')),
				'customers_telephone' => array('label' => array('text' => 'Phone')),
				'customers_fax' => array('label' => array('text' => 'Cell Phone')),
			),
			'addresses' => array(
				'customers_id' => array(),
				'customers_default_address_id' => array('label' => array('text' => 'Billing Address')),
				'customers_shipping_address_id' => array('label' => array('text' => 'Shipping Address')),
				'customers_emergency_address_id' => array('label' => array('text' => 'Backup Shipping Address'))
			),
			'payment_info' => array(
				'customers_id' => array(),
				'cc_firstname' => array('label' => array('text' => 'First Name on Credit Card')),
				'cc_lastname' => array('label' => array('text' => 'Last Name on Credit Card')),
				'cc_number' => array(
					'label' => array('text' => 'Card Number'),
					'maxlength' => '20',
				),
				'cc_expires_month' => array(
					'label' => array(
						'text' => 'Expiration Month',
						'class' => 'col-sm-3 control-label',
					)),
				'cc_expires_year' => array(
					'label' => array(
						'text' => 'Expiration Year',
					)),
				'cc_cvv' => array(
					'label' => array('text' => 'CVV Code'),
					'type' => 'text',
					'maxlength' => '4',
				)
			),
			'shipping' => array(
				'customers_id' => array(),
				'insurance_amount' => array('label' => array('text' => 'Insurance Amount')),
				'default_postal_type' => array(
					'label' => array('text' => 'Default Postal Type'),
					'type' => 'select',
					'options' => Configure::read('PostalClasses'),
				),
			),
		);

		if (empty($fieldList[$partial])) {
			throw new NotFoundException('The "' . $partial . '" group could not be found');
		}
		$inputs = $fieldList[$partial];
		$fields = array_keys($inputs);

		$customer = $this->Customer->find('first', array(
			'fields' => $fields,
			'conditions' => array(
				'Customer.customers_id' => $this->Auth->user('customers_id'),
			)
		));

		if ($this->request->is('put')) {
			$save = true;
			if ($partial == 'shipping') {
				$fields[] = 'insurance_fee';
				$this->Insurance = ClassRegistry::init('Insurance');
				$fee = $this->Insurance->getFeeForCoverageAmount($this->request->data['Customer']['insurance_amount']);
				if ($fee === false) {
					$save = false;
					$this->Customer->validationErrors['insurance_amount'] =
						array('You have entered and invalid insurance coverage amount.');
				}

				$this->request->data['Customer']['insurance_fee'] = $fee;
			}
			if ($partial == 'payment_info') {
				// Without appending to field list, save will fail
				$fields[] = 'cc_number_encrypted';
				$fields[] = 'card_token';
				// Setting Customer->id ensures that the model knows for which customer to grab the billing address.
				$this->Customer->id = $this->request->data['Customer']['customers_id'] = $this->Auth->user('customers_id');
				if (empty($this->Customer->validationErrors['cc_firstname'])) {
					$customer['Customer']['cc_firstname'] = $this->request->data['Customer']['cc_firstname'];
				}
				if (empty($this->Customer->validationErrors['cc_lastname'])) {
					$customer['Customer']['cc_lastname'] = $this->request->data['Customer']['cc_lastname'];
				}
			}

			if ($save && $this->Customer->save($this->request->data, true, $fields)) {
				$this->Flash->set('The information has been updated.');
				$this->Activity->record('edit', $this->Auth->user('customers_id'), $partial);
				$this->Customer->CustomerReminder->clearRecord($this->Auth->user('customers_id'), $partial);
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'account',
					'#' => str_replace('_', '-', $partial)
				));
			}

			$this->Flash->set('The information could not be updated. Please try again.');
		}

		switch($partial) {
			case 'addresses':
				$addresses = $this->Customer->Address->find('list', array(
					'conditions' => array(
						'Address.customers_id' => $this->Auth->user('customers_id'),
					)
				));

				$this->set('customersDefaultAddresses', $addresses);
				$this->set('customersShippingAddresses', $addresses);
				$this->set('customersEmergencyAddresses', $addresses);
				break;

			case 'payment_info':
				// Do not show card data
				unset(
					$customer['Customer']['cc_number'],
					$customer['Customer']['cc_expires_month'],
					$customer['Customer']['cc_expires_year'],
					$customer['Customer']['cc_cvv']
				);

				// Set dropdown for months and years
				$inputs['cc_expires_month']['type'] = 'select';
				$inputs['cc_expires_year']['type'] = 'select';

				$inputs['cc_expires_month']['options'] = Configure::read('Form.months');
				$inputs['cc_expires_year']['options'] = Configure::read('Form.years');

				break;
		}

		$this->request->data = $customer;
		$this->set(compact('customer', 'inputs', 'partial'));
	}

	/**
	 * change_password method
	 *
	 * @return mixed
	 */
	public function change_password() {
		if ($this->request->is('post')) {
			$this->Customer->id = $user['customers_id'] = $this->Auth->user('customers_id');
			$user['customers_email_address'] = $this->Auth->user('customers_email_address');
			$currentPassword = $this->request->data['Customer']['current_password'];
			$currentPasswordHash = $this->Customer->field('customers_password');
			$newPassword = $this->request->data['Customer']['new_password'];
			$newPasswordConfirm = $this->request->data['Customer']['confirm_new_password'];
			$passwordHasher = new ApoboxPasswordHasher;
			$save = true;

			if (!$passwordHasher->check($currentPassword, $currentPasswordHash)) {
				$this->Flash->set('Your current password was incorrect and was not updated.');
				$save = false;
			}

			if ($save && $newPassword != $newPasswordConfirm) {
				$this->Flash->set('You new password did not match the confirmation.');
				$save = false;
			}

			unset(
				$this->request->data['Customer']['current_password'],
				$this->request->data['Customer']['new_password'],
				$this->request->data['Customer']['confirm_new_password']
			);

			if ($save) {
				$user['customers_password'] = $newPassword;
				if ($this->Customer->save($user, true, ['customers_password'])) {
					$this->Flash->set('You password has been successfully changed.');
					$this->Auth->login($user);
					return $this->redirect(array('action' => 'account'));
				} else {
					$this->Flash->set('You password could not be changed.');
					if (!empty($this->Customer->validationErrors['customers_password'])) {
						$this->Customer->validationErrors['new_password'] =
							$this->Customer->validationErrors['customers_password'];
					}
				}
			}
		}
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
	 * @return mixed
	 */
	public function manager_search() {
		$this->request->allowMethod('get');
		$search = $this->request->query('q');
		$this->SearchIndex->searchModels('Customer');

		if ($search) {
			$search = $this->autoWrapFullnameOrEmail($search);
			$this->Paginator->settings = [
				'conditions' => [
					'MATCH(SearchIndex.data) AGAINST(? IN BOOLEAN MODE)' => $search,
				],
			];
		}
		$results = $this->Paginator->paginate('SearchIndex');

		if (count($results) == 1) {
			return $this->redirect(array(
				'action' => 'view',
				'id' => $results[0]['Customer']['customers_id'],
			));
		}
		$userIsManager = $this->_userIsManager();
		$this->set(compact(
			'search',
			'results',
			'userIsManager'
		));
	}

	/**
	 * If a search is multiple words, it is most likely a full name. If so,
	 * wrap in quotes to search exactly that name.
	 *
	 * If a search matches the pattern of an email address (this@that.com),
	 * wrap in quotes to search exactly the email address.
	 *
	 * @param string $search the search query string.
	 * @return string
	 */
	protected function autoWrapFullnameOrEmail($search) {
		if (preg_match('/^[A-Za-z0-9_\']+ [A-Za-z0-9_\']+( [A-Za-z0-9_\']+)*$/', $search) || preg_match('/^[\w.%+-]+@[\w.-]+\.[A-Z]{2,}$/i', $search)) {
			return '"' . $search . '"';
		}
		return $search;
	}

	/**
	 * getConditions
	 *
	 * @param array $query An array of terms to add to the search conditions
	 * @return array
	 * @deprecated Replaced with SearchIndex and the Searchable behavior
	 * @codeCoverageIgnore This method is deprecated and not used
	 */
	protected function getConditions($query) {
		$terms = explode(' ', $query);
		$nameRegex = '/^[a-z_]((?!\d)(?!\.).)+$/i';
		$fieldRegex = [
			'Customer.billing_id LIKE' => '/^[a-z]{0,2}[\d]{0,4}$/i',
			'Customer.customers_firstname LIKE' => $nameRegex,
			'Customer.customers_lastname LIKE' => $nameRegex,
			'Customer.customers_email_address LIKE' => '/[a-z0-9!#$%&\'*+-\/=?^_`{|}~\.@]+/i',
			'AuthorizedName.authorized_firstname LIKE' => $nameRegex,
			'AuthorizedName.authorized_lastname LIKE' => $nameRegex,
		];
		$conditions = [];

		$i = 0;
		foreach ($terms as $term) {
			foreach ($fieldRegex as $field => $regex) {
				if (preg_match($regex, $term)) {
					$perms = $this->getTermSearchPermutations($field, $term);
					foreach ($perms as $perm) {
						if (strpos($perm, '%') === false) {
							$field = str_replace(' LIKE', '', $field);
						}
						$conditions[$i]['OR'][] = [$field => $perm];
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
	 * @deprecated Replaced with SearchIndex and the Searchable behavior
	 * @codeCoverageIgnore This method is deprecated and not used
	 */
	protected function getTermSearchPermutations($field, $term) {
		$perms = array();
		switch ($field) {
			case 'Customer.billing_id LIKE':
				if (preg_match('/^[a-z][a-z]\d{4}$/i', $term)) {
					$perms[] = $term;
				}
				if (preg_match('/^[a-z]{0,1}[\d]{4}$/i', $term)) {
					$perms[] = '%' . $term;
				}
				if (preg_match('/^[a-z]{0,1}[\d]{0,3}$/i', $term)) {
					$perms[] = '%' . $term . '%';
				}
				if (preg_match('/^[a-z]{2}\d{0,3}$/i', $term)) {
					$perms[] = $term . '%';
				}
				break;
			case 'Customer.customers_email_address LIKE':
				$perms[] = '%' . $term . '%';
				break;
			case 'Customer.customers_firstname LIKE':
			case 'Customer.customers_lastname LIKE':
			case 'AuthorizedName.authorized_firstname LIKE':
			case 'AuthorizedName.authorized_lastname LIKE':
				if (!preg_match('/^[a-z]{2}$/i', $term)) {
					$perms[] = '%' . $term . '%';
				}
				break;
		}
		return $perms;
	}

	/**
	 * employee_recent
	 *
	 * @param mixed $id A customer id
	 * @return void
	 */
	public function employee_recent($id = null) {
		$this->manager_recent($id);
	}

	/**
	 * manager_recent
	 *
	 * @param mixed $id A customer id
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_recent($id = null) {
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}
		$customer = $this->Customer->findByCustomersId($id);
		$customerName = $customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname'];

		$this->paginate['Order']['fields'] = array(
			'orders_id',
			'delivery_address',
			'comments',
			'last_modified',
			'date_purchased',
			'orders_date_finished',
			'inbound_tracking',
			'usps_track_num',
			'dimensions',
			'weight_oz',
			'mail_class',
		);
		$this->paginate['Order']['conditions'] = array('Order.customers_id' => $id);
		$this->paginate['Order']['contain'] = array(
			'Customer' => array('customers_id', 'billing_id'),
			'OrderStatus' => array('orders_status_id', 'orders_status_name'),
		);
		$this->Paginator->settings = $this->paginate;
		$orders = $this->Paginator->paginate('Order');

		$this->set(compact('orders', 'customerName'));
	}

	/**
	 * manager_edit_payment_info
	 *
	 * @param mixed $id A customer id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_edit_payment_info($id = null) {
		$this->request->allowMethod(array('get', 'put'));
		$this->request->data['Customer']['customers_id'] = $this->Customer->id = $id;
		if (!$this->Customer->exists()) {
			throw new NotFoundException;
		}

		$fields = array(
			'cc_firstname',
			'cc_lastname',
			'cc_number',
			'cc_number_encrypted',
			'card_token',
			'cc_expires_month',
			'cc_expires_year',
			'cc_cvv',
		);

		if ($this->request->is('put')) {
			if ($this->Customer->save($this->request->data, true, $fields)) {
				$this->Flash->set('The customer\'s credit card has been updated');
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $id,
				));
			}
			$this->Flash->set('The customer\'s credit card could not be updated.');
		}

		$customer = $this->Customer->findByCustomersId($id);

		unset(
			$customer['Customer']['cc_number'],
			$customer['Customer']['cc_expires_month'],
			$customer['Customer']['cc_expires_year'],
			$customer['Customer']['cc_cvv']
		);

		$this->request->data = $customer;
		$this->set(compact('customer'));
	}

	/**
	 * manager_edit_contact_info
	 *
	 * @param mixed $id A customer id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_edit_contact_info($id = null) {
		$this->request->allowMethod(array('get', 'put'));

		$this->request->data['Customer']['customers_id'] = $this->Customer->id = $id;
		if (!$this->Customer->exists()) {
			throw new NotFoundException;
		}

		if ($this->request->is('put')) {
			$fields = array(
				'customers_firstname',
				'customers_lastname',
				'customers_email_address',
				'customers_telephone',
				'customers_fax',
				'backup_email_address',
				'invoicing_authorized',
				'billing_type',
			);
			if ($this->Customer->save($this->request->data, true, $fields)) {
				$this->Flash->set('The customer\'s information has been updated.');
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $id,
				));
			}

			$this->Flash->set('The customer\'s information could not be updated.');
		}

		$customer = $this->Customer->read();
		$this->request->data['Customer'] = Hash::merge($customer['Customer'], $this->request->data['Customer']);

		$this->set(compact('customer'));
	}

	/**
	 * manager_edit_default_addresses
	 *
	 * @param mixed $id A customer id
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function manager_edit_default_addresses($id = null) {
		$this->request->allowMethod(array('get', 'put'));

		$this->request->data['Customer']['customers_id'] = $this->Customer->id = $id;
		if (!$this->Customer->exists()) {
			throw new NotFoundException;
		}

		if ($this->request->is('put')) {
			$fields = array(
				'customers_default_address_id',
				'customers_shipping_address_id',
				'customers_emergency_address_id',
			);
			if ($this->Customer->save($this->request->data, true, $fields)) {
				$this->Flash->set('The customer\'s default addresses have been updated.');
				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $id,
				));
			}

			$this->Flash->set('The customer\'s default addresses were unable to be updated.');
		}

		$customer = $this->Customer->read();
		$this->request->data['Customer'] = Hash::merge($customer['Customer'], $this->request->data['Customer']);

		$addresses = $this->Customer->Address->find('list', array(
			'conditions' => array(
				'Address.customers_id' => $id,
			)
		));

		$this->set('customersDefaultAddresses', $addresses);
		$this->set('customersShippingAddresses', $addresses);
		$this->set('customersEmergencyAddresses', $addresses);
		$this->set(compact('customer'));
	}

	/**
	 * Employee wrapper for manager_edit_default_addresses
	 *
	 * @param mixed $id A customer id
	 * @return mixed
	 */
	public function employee_edit_default_addresses($id = null) {
		$this->manager_edit_default_addresses($id);
		return $this->render('manager_edit_default_addresses');
	}

	/**
	 * api_view
	 *
	 * @param mixed $id A customer id
	 * @return void
	 * @throws NotFoundException
	 */
	public function api_view($id = null) {
		$this->request->allowMethod('get');
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}

		$customer = $this->Customer->find('first', array(
			'contain' => array(
				'DefaultAddress' => array('Zone'),
			),
			'conditions' => array('Customer.customers_id' => $id),
		));

		$this->set('customer', $customer);
	}

	/**
	 * Takes top level key "message" and uses it as the body of and email to
	 * customer where customers_id = $id.
	 * Send status code 204 on success. Also send 400 and 404.
	 *
	 * @param mixed $id A customer id
	 * @return void
	 * @throws NotFoundException
	 * @throws BadRequestException
	 */
	public function api_notify($id = null) {
		$this->request->allowMethod('post');
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}
		if (empty($this->request->data['message']['body'])) {
			throw new BadRequestException('Missing "body" key.');
		}

		$body = $this->request->data['message']['body'];
		$subject = Configure::read('Email.Subjects.customer');
		if (!empty($this->request->data['message']['subject'])) {
			$subject = $this->request->data['message']['subject'];
		}
		if (!$this->_sendDefaultEmail($id, $subject, $body)) {
			throw new BadRequestException('There was a problem sending the message');
		}

		$this->response->statusCode(204);
	}

	/**
	 * employee_view
	 *
	 * @param mixed $id A customer id
	 * @return mixed
	 */
	public function employee_view($id = null) {
		$this->manager_view($id);
		return $this->render('manager_view');
	}

	/**
	 * manager_view
	 *
	 * @param mixed $id A customer id
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_view($id = null) {
		$this->request->allowMethod('get');
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}

		$customer = $this->Customer->find('first', array(
			'contain' => array(
				'Address',
				'DefaultAddress' => array('Zone'),
				'ShippingAddress' => array('Zone'),
				'EmergencyAddress' => array('Zone'),
				'AuthorizedName'
			),
			'conditions' => array('Customer.customers_id' => $id)
		));
		$customer['Customer']['name'] = $customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname'];

		$this->Paginator->settings = [
			'Order' => [
				'paramType' => 'querystring',
				'contain' => ['OrderStatus', 'Customer', 'OrderTotal'],
				'conditions' => [
					'Order.customers_id' => $id
				],
				'limit' => 20,
			],
			'limit' => 5
		];
		$orders = $this->Paginator->paginate('Order');

		if (!$customer['Customer']['is_active']) {
			$Activity = ClassRegistry::init('CustomersInfo');
			$record = $Activity->findByCustomersInfoId($id, 'customers_info_date_account_closed');
			$this->set('closed', date('m/d/Y', strtotime($record['CustomersInfo']['customers_info_date_account_closed'])));
		}

		$userIsManager = $this->_userIsManager();
		$partialSignup = $this->Customer->isPartialSignup($id);
		$customRequests = $this->Customer->Order->CustomPackageRequest->findAllForOrder($orders);
		$this->set(compact(
			'customer',
			'orders',
			'userIsManager',
			'partialSignup',
			'customRequests'
		));
	}

	/**
	 * Displays a json list of addresses for a given customer.
	 *
	 * @param int $id A customer id.
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_addresses($id = null) {
		$this->RequestHandler->renderAs($this, 'json');
		$this->request->allowMethod('get');
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}

		$addresses = $this->Customer->Address->find('list', array('conditions' => array('Address.customers_id' => $id)));
		$this->set(compact('addresses'));
		$this->set('_serialize', array('addresses'));
	}

	/**
	 * Displays a json list of APO type shipping addresses for a given customer.
	 *
	 * @param int $id A customer id.
	 * @return void
	 * @throws NotFoundException
	 */
	public function manager_shipping_addresses($id = null) {
		$this->RequestHandler->renderAs($this, 'json');
		$this->request->allowMethod('get');
		$this->Customer->id = $id;

		if (!$this->Customer->exists()) {
			throw new NotFoundException('Customer not found');
		}

		$shippingAddresses = $this->Customer->ShippingAddress->find('list', array('conditions' => array('ShippingAddress.customers_id' => $id)));
		$this->set(compact('shippingAddresses'));
		$this->set('_serialize', array('shippingAddresses'));
	}

	/**
	 * Add customer records.
	 *
	 * Allows new customers to create account. This currently only accepts
	 * JSON formatted requests. It is intended for use by the widget.
	 *
	 * @return mixed
	 * @throws BaseSerializerException
	 */
	public function add() {
		$this->request->allowMethod('post');
		$this->initJsonResponse();
		$data = $this->checkAndSetDataFromJson('Customer');

		$dataOriginal = $data;
		if (!$this->Customer->save($data)) {
			$msg = implode(' ', Hash::extract($this->Customer->validationErrors, '{s}.{n}'));
			$this->logBaseSerializerException($msg, 'Customer', 'customers', $data);
			throw new BaseSerializerException($msg);
		}

		$this->request->data = $dataOriginal;

		$id = $this->Customer->id;
		$this->request->data['Customer'] = array_merge(
			$this->request->data['Customer'],
			array('customers_id' => $id)
		);
		$data = $this->Customer->read(null, $id);
		$this->Auth->login($data['Customer']);
		$this->Activity->record('register', $this->Auth->user('customers_id'));

		$response = array(
			'data' => array(
				'type' => 'customers',
				'id' => $data['Customer']['customers_id'],
				'attributes' => $this->dataForApi($data['Customer']),
			),
		);

		$this->response->statusCode(201);
		$this->response->body(json_encode($response));
		return $this->response->send();
	}

	/**
	 * Sends an otherwise blank email to customer where customers_id = $id with $message
	 * Auto addresses the body of the email to the customer.
	 *
	 * @param mixed $id A customer id
	 * @param string $subject The subject
	 * @param mixed $body The body
	 * @return bool
	 * @throws NotFoundException
	 */
	protected function _sendDefaultEmail($id, $subject, $body) {
		$customer = $this->Customer->find('first', array(
			'conditions' => array('customers_id' => $id),
		));
		$task = $this->taskFactory();

		$recipient = array(
			$customer['Customer']['customers_email_address'] =>
				$customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname']
		);
		$vars = array(
			'name' => current($recipient),
			'body' => $body,
		);

		return $task->createJob('AppEmail',
			[
				'method' => 'sendBlank',
				'recipient' => $recipient,
				'vars' => $vars,
				'subject' => $subject
			],
			null,
			'Customer::sendBlank',
			$customer['Customer']['billing_id']
		);
	}

	/**
	 * Filter customer data to only fields allowed in the API.
	 *
	 * @param array $data The customer data.
	 * @return array
	 */
	protected function dataForApi($data) {
		$apiFields = [
			'customers_id',
			'customers_firstname',
			'customers_lastname',
			'customers_email_address',
			'billing_id',
		];
		foreach ($data as $key => $value) {
			if (!in_array($key, $apiFields)) {
				unset($data[$key]);
			}
		}

		return $data;
	}

	/**
	 * Looks for a querystring matching a Customer::billing_id and redirects
	 * to adding an order for the found customer. If the billing_id is not found,
	 * redirects back to the admin dashboarda.
	 *
	 * @return mixed
	 */
	public function manager_quick_order() {
		$this->request->allowMethod('get');
		$search = $this->request->query('q');
		$customer = $this->Customer->findForQuickOrder($search);
		if ($customer) {
			return $this->redirect(array(
				'controller' => 'orders',
				'action' => 'add',
				$customer['Customer']['customers_id'],
			));
		}
		$this->Flash->set('An active customer with Billing ID: "' . $search . '" was not found.');
		return $this->redirect(array(
			'controller' => 'admins',
			'action' => 'index',
			'manager' => true,
		));
	}

	/**
	 * Employee wrapper for manager_quick_order.
	 *
	 * @return mixed
	 */
	public function employee_quick_order() {
		return $this->manager_quick_order();
	}

	/**
	 * Checks to make sure the passed `hash` equals the sha1 hash of the logged
	 * in user's id. If matching and the account is successfully closed the user
	 * is logged out.
	 *
	 * @return mixed
	 */
	public function close_account() {
		$this->autoRender = false;
		if ($this->checkHash() && $this->Customer->closeAccount($this->Auth->user('customers_id'))) {
			$this->Activity->record('close', $this->Auth->user('customers_id'));
			$this->Auth->logout();
			$this->Flash->set('Your APO Box address has been deactivated and your credit card information has been removed from our system. We will no longer forward packages on your behalf.');
			return $this->redirect('/');
		} else {
			$this->Flash->set('There was a problem closing your account.');
			return $this->redirect(array('action' => 'account', '#' => 'my-info'));
		}
	}

	/**
	 * Allows a manager to close a customer's account.
	 *
	 * @return mixed
	 */
	public function manager_close_account() {
		$id = Hash::get($this->request->params, 'customerId');
		if ($this->Customer->closeAccount($id)) {
			$this->Activity->record('close', $id);
			$this->Flash->set("The customer's APO Box account has been closed.");
		} else {
			$this->Flash->set("There was a problem closing this customer's account.");
		}
		return $this->redirect(array(
			'action' => 'view',
			'id' => $id,
		));
	}

	/**
	 * Validates the request and sends an email to the user with the 'close account'
	 * link.
	 *
	 * @return string
	 */
	public function confirm_close() {
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			$hash = $this->checkHash();
			$id = Hash::get($this->request->params, 'customerId');
			if ($hash && $customer = $this->Customer->findByCustomersId($id)) {
				$emailVars = array(
					'customerName' =>
					$customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname'],
					'url' => Router::url(
						array(
							'controller' => 'customers',
							'action' => 'close_account',
							'hash' => $hash
						),
						array(
							'full' => true
						)
					)
				);
				$task = $this->taskFactory();
				$added = $task->createJob('AppEmail',
					[
						'method' => 'sendConfirmClose',
						'recipient' => $customer['Customer']['customers_email_address'],
						'vars' => $emailVars,
					],
					null,
					'Customer::sendConfirmClose',
					$customer['Customer']['billing_id']
				);

				if ($added) {
					return 'success';
				}
			}
			return 'failure';
		}
	}

	/**
	 * Customer demographics report.
	 *
	 * @return void
	 */
	public function manager_demographics_report() {
		$this->request->allowMethod('get', 'post');

		$defaults = [
			'field' => 'ShippingAddress.entry_postcode',
			'limit' => 5,
			'from_date' => DateTime::createFromFormat('Y-m-d', '2006-11-29')->format('Y-m-d'),
			'to_date' => (new DateTime())->format('Y-m-d'),
		];
		$reportFields = $this->Customer->reportFields;

		$options = !empty($this->request->data) ? $this->request->data : $this->request->query;
		$options = array_merge($defaults, $options);
		$this->request->data = $options;

		$data = $this->Customer->findCustomerTotalsReport($options);

		$this->set(compact('options', 'data', 'reportFields'));
	}

	/**
	 * Checks if a request contains a hash consisting of today's date and the
	 * logged in user's id.
	 *
	 * @return mixed false or the request hash
	 */
	protected function checkHash() {
		$hash = Hash::get($this->request->params, 'hash');
		if (is_null($hash) || $hash != sha1(date('Y-m-d') . $this->Auth->user('customers_id'))) {
			return false;
		}
		return $hash;
	}
}
