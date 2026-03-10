<?php
App::uses('Component', 'Controller');

/**
 * The ActivityComponent handles recording of activities. It currently only
 * tracks user activities and interfaces with the CustomersInfo model to store
 * the data. Possible tracking option are listed in the `$activities` property
 * and recording them is done with `record($activity, $id, $subActivity)`.
 */
class ActivityComponent extends Component {

	/**
	 * A list of activities that can be recorded.
	 */
	protected $activities = [
		'register',
		'login',
		'edit',
		'close',
		'source',
	];

	/**
	 * The model alias to interface with.
	 *
	 * @var string
	 */
	protected $modelAlias = 'CustomersInfo';

	/**
	 * Stores an instance of the model.
	 *
	 * @var mixed
	 */
	protected $Model;

	/**
	 * Stores the clients ip address.
	 *
	 * @var mixed
	 */
	protected $clientIp;

	/**
	 * The controller using this component.
	 *
	 * @var mixed
	 */
	public $Controller;

	/**
	 * __construct
	 *
	 * @param ComponentCollection $collection A component collection
	 * @param array $settings Optional settings
	 * @return void
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		App::uses($this->modelAlias, 'Model');
		$this->Model = ClassRegistry::init($this->modelAlias);
	}

	/**
	 * initialize
	 *
	 * @param Controller $controller A controller object
	 * @return void
	 */
	public function initialize(Controller $controller) {
		$this->clientIp = $controller->request->clientIp();
	}

	/**
	 * record
	 *
	 * @param string $activity The activity
	 * @param mixed $id A CustomersInfo primary id
	 * @param mixed $subActivity Optional sub activity to record
	 * @return bool
	 */
	public function record($activity, $id, $subActivity = null) {
		if (!in_array($activity, $this->activities)) {
			$this->log('ActivityComponent::record: Activity ' . $activity . ' cannot be recorded', 'customers');
			return false;
		}

		$data = ['customers_info_id' => $id];
		return $this->{'record' . ucfirst($activity)}($data, $subActivity);
	}

	/**
	 * recordRegister
	 *
	 * @param array $data The data to save
	 * @param mixed $subActivity Optional sub activity to record
	 * @return bool
	 */
	protected function recordRegister($data, $subActivity = null) {
		$data = array_merge($data, [
			'customers_info_date_account_created' => date('Y-m-d H:i:s'),
			'IP_signup' => $this->clientIp,
		]);
		return $this->save($data);
	}

	/**
	 * recordLogin
	 *
	 * @param array $data The data to save
	 * @param mixed $subActivity Optional sub activity to record
	 * @return bool
	 */
	protected function recordLogin($data, $subActivity = null) {
		$data = array_merge($data, [
			'customers_info_date_of_last_logon' => date('Y-m-d H:i:s'),
			'customers_info_number_of_logons' => 1 + $this->lastLoginCount($data['customers_info_id']),
			'IP_lastlogon' => $this->clientIp,
		]);
		return $this->save($data);
	}

	/**
	 * recordEdit
	 *
	 * @param array $data The data to save
	 * @param string $subActivity The sub activity to record
	 * @return bool
	 */
	protected function recordEdit($data, $subActivity) {
		$data = array_merge($data, [
			'customers_info_date_account_last_modified' => date('Y-m-d H:i:s'),
		]);
		if ($subActivity === 'payment_info') {
			$data['IP_cc_update'] = $this->clientIp;
		}
		if ($subActivity === 'addresses') {
			$data['IP_addressbook_update'] = $this->clientIp;
		}

		return $this->save($data);
	}

	/**
	 * recordClose
	 *
	 * @param mixed $data The data
	 * @param mixed $subActivity Optional sub activity to record
	 * @return bool
	 */
	protected function recordClose($data, $subActivity = null) {
		$data = array_merge($data, [
			'customers_info_date_account_closed' => date('Y-m-d H:i:s'),
		]);
		return $this->save($data);
	}

	/**
	 * recordSource
	 *
	 * @param mixed $data The data
	 * @param mixed $sourceId The source Id
	 * @return bool
	 */
	protected function recordSource($data, $sourceId) {
		$data = array_merge($data, [
			'customers_info_source_id' => $sourceId,
		]);

		return $this->save($data);
	}

	/**
	 * save
	 *
	 * @param array $data The data
	 * @return bool
	 */
	protected function save($data) {
		return $this->Model->save($data);
	}

	/**
	 * lastLoginCount
	 *
	 * @param mixed $id The id
	 * @return bool
	 */
	protected function lastLoginCount($id) {
		$record = $this->Model->find('first', [
			'fields' => ['customers_info_number_of_logons'],
			'conditions' => ['customers_info_id' => $id],
		]);

		if (empty($record)) {
			return 0;
		}

		return $record[$this->modelAlias]['customers_info_number_of_logons'];
	}
}

