<?php
/**
 * Addresses
 */

App::uses('AppController', 'Controller');

/**
 * Addresses Controller
 *
 * @property	Address	$Address
 * @property	PaginatorComponent	$Paginator
 * @property	SessionComponent	$Session
 */
class AddressesController extends AppController {

	/**
	 * Models
	 *
	 * @var	array
	 */
	public $uses = array(
		'Address',
		'ShippingAddress',
	);

	/**
	 * Components
	 *
	 * @var	array
	 */
	public $components = array(
		'Paginator',
	);

	/**
	 * Helpers
	 *
	 * @var	array
	 */
	//public $helpers = array();

	/**
	 * A list of address models that can be specified in the json add method.
	 */
	public $validAddressModels = ['Address', 'ShippingAddress'];

	/**
	 * add method
	 *
	 * @return	void
	 */
	public function add() {
		if ($this->request->accepts('application/vnd.api+json')) {
			return $this->_jsonAdd();
		}

		if ($this->request->is('post')) {
			// ensure users can only add addresses for themselves
			$this->request->data['Address']['customers_id'] = $this->Auth->user('customers_id');

			$this->Address->create();

			if ($this->Address->save($this->request->data)) {
				$this->Flash->set(__('The address book has been saved.'));

				// Check if the user wants to assign it to one of their addresses.
				if ($this->_shouldUpdateCustomerDefaultAddressWithLastCreated()) {
					$mapKey = $this->request->data['Address']['make_this_my'];

					if (!$this->_setLastCreatedAddressAsCustomersAddress()) {
						$this->Flash->set(
							__('We were unable to set the new address as your ' . $mapKey . ' address')
						);
						return $this->redirect(array(
							'controller' => 'customers',
							'action' => 'edit_partial',
							'default_addresses'
						));
					}

					$this->Flash->set(
						__('The address has been saved and set as your ' . $mapKey . ' address')
					);
				}

				return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
			} else {
				$this->Flash->set(__('The address could not be saved. Please, try again.'));
			}
		}

		$zones = $this->Address->Zone->find('list');

		$this->set(compact('zones'));
	}

	/**
	 * Process JSON formatted address add requests. This is intended for use
	 * by the widget and currently defaults the address to be a valid APO type
	 * address. This validation is done by the ShippingAddress model.
	 *
	 * @param string $model The model to use for validation and saving
	 * @return The complete response to the client including headers and message body
	 * @throws BaseSerializerException If save fails
	 */
	protected function _jsonAdd($model = 'ShippingAddress') {
		$this->request->allowMethod('post');
		$this->initJsonResponse();
		$data = $this->checkAndSetDataFromJson($model, $this->validAddressModels);
		$dataOriginal = $data;

		$data[$model]['customers_id'] = $this->Auth->user('customers_id');
		$data[$model] = $this->checkCountry($data[$model]);
		$this->{$model}->create();
		$data = $this->{$model}->save($data);
		if (!$data) {
			$msg = implode(' ', Hash::extract($this->{$model}->validationErrors, '{s}.{n}'));
			$this->logBaseSerializerException($msg, $model, 'customers', $dataOriginal);
			throw new BaseSerializerException($msg);
		}

		$this->{$model}->setDefaultsForCustomer($this->Auth->user('customers_id'));

		$response = array(
			'data' => array(
				'type' => Inflector::tableize($model),
				'id' => $data[$model]['address_book_id'],
				'attributes' => $data[$model],
			),
		);

		$options = array(
			'contain' => array(
				'DefaultAddress',
				'DefaultAddress.Zone',
			),
			'conditions' => array(
				'Customer.customers_id' => $this->Auth->user('customers_id'),
			)
		);
		$customer = $this->Address->Customer->find('first', $options);
		if ($customer) {
			$this->_sendWelcomeEmail($customer);
		}

		$this->response->statusCode(201);
		$this->response->body(json_encode($response));
		return $this->response->send();
	}

	/**
	 * Set the default country to 223 (USA) if it is not already set on input.
	 *
	 * @param array $data The input data.
	 * @return array
	 */
	protected function checkCountry($data) {
		if (empty($data['entry_country_id'])) {
			$data['entry_country_id'] = 223;
		}
		return $data;
	}

	/**
	 * _shouldUpdateCustomerDefaultAddressWithLastCreated
	 *
	 * @return bool
	 */
	protected function _shouldUpdateCustomerDefaultAddressWithLastCreated() {
		if (empty($this->request->data['Address']['make_this_my'])) {
			return false;
		}

		return true;
	}

	/**
	 * _setLastCreatedAddressAsCustomersAddress
	 *
	 * @param mixed $customerId The customerId
	 * @return bool
	 */
	protected function _setLastCreatedAddressAsCustomersAddress($customerId = null) {
		if ($customerId != null) {
			$this->Address->Customer->id = $customerId;
		} else {
			$this->Address->Customer->id = $this->Auth->user('customers_id');
		}

		if (empty($this->request->data['Address']['make_this_my'])) {
			// Assume billing if not set
			$this->request->data['Address']['make_this_my'] = 'billing';
		}

		$mapKey = $this->request->data['Address']['make_this_my'];
		$fieldMap = array(
			'billing' => 'customers_default_address_id',
			'shipping' => 'customers_shipping_address_id',
			'emergency' => 'customers_emergency_address_id',
		);

		if (empty($fieldMap[$mapKey])) {
			return false;
		}

		$addressId = $this->Address->getInsertId();
		if (!$this->Address->Customer->saveField($fieldMap[$mapKey], $addressId, true)) {
			return false;
		}

		return true;
	}

	/**
	 * edit
	 *
	 * @param mixed $id The address id
	 * @return void
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 */
	public function edit($id = null) {
		$this->Address->id = $id;

		if (! ($this->request->is('get') || $this->request->is('put')) ) {
			throw new MethodNotAllowedException('Method must be one of GET or PUT');
		}

		if (!$this->Address->exists()) {
			throw new NotFoundException('The address was not found.');
		}

		$address = $this->Address->read();
		$this->Address->Customer->id = $customerId = $this->Auth->user('customers_id');

		if ($address['Address']['customers_id'] != $customerId) {
			throw new ForbiddenException('You are not allowed to edit this address');
		}

		if ($this->request->is('put')) {
			$this->request->data['Address']['address_book_id'] = $this->Address->id;
			$this->request->data['Address']['customers_id'] = $customerId;

			$saved = $this->Address->save($this->request->data);
			if (!$saved) {
				$this->Flash->set('The address could not be updated.');
			} else {
				$this->Flash->set('Your address was successfully updated!');
				return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
			}

		} else {
			$this->request->data['Address'] = $address['Address'];
		}

		$addressName = $address['Address']['full'];
		$addressId = $address['Address']['address_book_id'];
		$zones = $this->Address->Zone->find('list');
		$this->set(compact('zones', 'addressName', 'addressId'));
	}

	/**
	 * delete
	 *
	 * @param mixed $id The address id
	 * @return void
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 */
	public function delete($id = null) {
		$this->Address->id = $id;

		if (!$this->request->is('delete')) {
			throw new MethodNotAllowedException('Method must be DELETE');
		}

		if (!$this->Address->exists()) {
			throw new NotFoundException('The address was not found.');
		}

		$address = $this->Address->read();
		$this->Address->Customer->id = $this->Auth->user('customers_id');

		if ($address['Address']['customers_id'] != $this->Address->Customer->id) {
			throw new ForbiddenException('You are not allowed to edit this address');
		}

		if ($this->Address->Customer->addressIsInUse($id)) {
			$this->Flash->set(
				'You are currently using that address as one of your default addresses.
				Please, select another address to use in it\'s place and then try again.'
			);
			return $this->redirect(array(
				'controller' => 'customers',
				'action' => 'edit_partial',
				'partial' => 'addresses'
			));
		}

		if ($this->Address->delete($id, false)) {
			$this->Flash->set('The address was successfully deleted!');
			return $this->redirect(array('controller' => 'customers', 'action' => 'account'));
		}

		$this->Flash->set('The address could not be deleted.');
	}

	/**
	 * manager_add
	 *
	 * @param mixed $customerId The customer id
	 * @return void
	 * @throws MethodNotAllowedException
	 * @throws NotFoundException
	 */
	public function manager_add($customerId = null) {
		if (!($this->request->is('get') || $this->request->is('post'))) {
			throw new MethodNotAllowedException('Method must be GET or POST');
		}

		$this->Address->Customer->id = $customerId;

		if (!$this->Address->Customer->exists()) {
			throw new NotFoundException('The requested customer was not found.');
		}

		$customer = $this->Address->Customer->read();

		if ($this->request->is('post')) {
			$this->request->data['Address']['customers_id'] = $customerId;

			$this->Address->create();

			if ($this->Address->save($this->request->data)) {
				$this->Flash->set('The address has been saved.');

				if ($this->_shouldUpdateCustomerDefaultAddressWithLastCreated()) {
					$addressType = $this->request->data['Address']['make_this_my'];

					if (!$this->_setLastCreatedAddressAsCustomersAddress($customerId)) {
						$this->Flash->set(
							__('We were unable to set the new address as the customers ' . $addressType . ' address')
						);
						return $this->redirect(array(
							'controller' => 'customers',
							'action' => 'edit_default_addresses',
							'id' => $customerId,
						));
					}

					$this->Flash->set(
						'The address has been saved and set as the customers ' . $addressType . ' address'
					);
				}

				return $this->redirect(array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $customerId,
				));
			} else {
				$this->Flash->set('The address could not be saved. Please, try again.');
			}
		}

		$countries = $this->Address->Country->findWithZones();
		$zones = $this->Address->Zone->findZonesWithCountries();
		$this->set(compact('countries', 'zones', 'customer'));
	}

	/**
	 * Employee wrapper around manager_add.
	 *
	 * @param mixed $customerId The customer id
	 * @return mixed
	 */
	public function employee_add($customerId = null) {
		$this->manager_add($customerId);
		if ($this->request->is('get')) {
			return $this->render('manager_add');
		}
	}

	/**
	 * Sends an email to new customers.
	 *
	 * @param array $customer A Customer array
	 * @return bool
	 * @throws NotFoundException
	 */
	protected function _sendWelcomeEmail($customer) {
		$task = $this->taskFactory();

		$recipient = array(
			$customer['Customer']['customers_email_address'] =>
				$customer['Customer']['customers_firstname'] . ' ' . $customer['Customer']['customers_lastname']
		);
		$vars = array(
			'almostFinishedUrl' => Router::url([
				'controller' => 'customers',
				'action' => 'almost_finished',
			], true),
			'firstName' => $customer['Customer']['customers_firstname'],
			'lastName' => $customer['Customer']['customers_lastname'],
			'billingId' => $customer['Customer']['billing_id'],
			'address' => $customer['DefaultAddress'],
		);
		return $task->createJob('AppEmail',
			[
				'method' => 'sendWelcome',
				'recipient' => $recipient,
				'vars' => $vars,
			],
			null,
			'Address::sendWelcome',
			$customer['Customer']['billing_id']
		);
	}
}
