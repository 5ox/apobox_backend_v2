<?php
App::uses('AuthorizedName', 'Model');

/**
 * AuthorizedName Test Case
 *
 */
class AuthorizedNameTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.authorized_name',
		'app.customer',
		'app.search_index',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->AuthorizedName = ClassRegistry::init('AuthorizedName');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->AuthorizedName);

		parent::tearDown();
	}

	/**
	 * Tests all validation rules
	 *
	 * @dataProvider validationProvider
	 */
	public function testValidationNew($field, $value, $pass) {
		$data = array(
			'AuthorizedName' => array(
				$field => $value,
			),
		);

		$this->AuthorizedName->create();
		$this->AuthorizedName->set($data);
		$result = $this->AuthorizedName->validates($data);

		$test = ($pass) ? 'assertArrayNotHasKey' : 'assertArrayHasKey';
		$this->{$test}($field, $this->AuthorizedName->validationErrors);
	}

	public function testCreateValidData() {
		$customerId = 1;

		$SearchIndex = $this->getMockForModel('SearchIndex', [
			'findByAssociationKey',
			'saveField',
		]);
		$SearchIndex->expects($this->once())
			->method('findByAssociationKey')
			->will($this->returnValue(['SearchIndex' => ['id' => 1, 'data' => 'foo']]));
		$SearchIndex->expects($this->once())
			->method('saveField')
			->will($this->returnValue(true));

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => 'George',
				'authorized_lastname' => 'Washington',
				'customers_id' => $customerId
			)
		);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->AuthorizedName->create();

		$result = $this->AuthorizedName->save($data);

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->assertEquals($countBefore+1, $countAfter, 'Record should have been created.');
	}

	public function testCreateInvalidData() {
		$customerId = 1;

		$data = array(
			'AuthorizedName' => array(
				'authorized_firstname' => 'George',
				'authorized_lastname' => 'Washington',
				'customers_id' => ''
			)
		);

		$countBefore = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->AuthorizedName->create();

		$result = $this->AuthorizedName->save($data);

		$countAfter = $this->AuthorizedName->find('count', array(
			'conditions' => array(
				'customers_id' => $customerId
			)
		));

		$this->assertEquals($countBefore, $countAfter, 'Record should not have been created.');
	}

	public static function validationProvider() {
		$tests = array(
			array('authorized_firstname', 'John', true),
			array('authorized_firstname', '%^$&', true),
			array('authorized_firstname', 'text with spaces', true),
			array('authorized_firstname', 'a long string with more than 20 characters', false),
			array('authorized_firstname', '', false),

			array('authorized_lastname', 'Smith', true),
			array('authorized_lastname', '%^$&', true),
			array('authorized_lastname', 'text with spaces', true),
			array('authorized_lastname', 'a long string with more than 20 characters', false),
			array('authorized_lastname', '', false),

			array('customers_id', '', false),
			array('customers_id', 'abc', false),
			array('customers_id', '51', true)

		);

		return $tests;
	}

	/**
	 * Confirm that when a new authorized name is added, the firstname and
	 * lastname are added to SearchIndex.data.
	 *
	 * @return void
	 */
	public function testAfterSaveCreatesSearchIndex() {
		$id = 5;
		$SearchIndex = ClassRegistry::init('SearchIndex');
		$before = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'XU934. Invoice. Unique. test.user99@example.com',
			$before['SearchIndex']['data']
		);

		$data = [
			'AuthorizedName' => [
				'authorized_firstname' => 'Bob',
				'authorized_lastname' => 'Dobbs',
				'customers_id' => $id,
			]
		];
		$this->AuthorizedName->save($data);

		$after = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'XU934. Invoice. Unique. test.user99@example.com. Bob. Dobbs',
			$after['SearchIndex']['data'],
			'Should add firstname and lastname to SearchIndex.data'
		);
	}

	/**
	 * Confirm that when an authorized name is updated/edited, the updated changes
	 * are made in SearchIndex.data as well.
	 *
	 * @return void
	 */
	public function testAfterSaveUpdatesSearchIndex() {
		$id = 2;
		$SearchIndex = ClassRegistry::init('SearchIndex');
		$before = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'IB1234. Incomplete. Billing. someone.else@example.com. George. Washington. Lorem. SetDefaults',
			$before['SearchIndex']['data']
		);

		$data = [
			'AuthorizedName' => [
				'authorized_names_id' => 4,
				'authorized_firstname' => 'Bob',
				'authorized_lastname' => 'Dobbs',
				'customers_id' => $id,
			]
		];
		$this->AuthorizedName->save($data);

		$after = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'IB1234. Incomplete. Billing. someon.else@example.com. George. Washington. Bob. Dobbs',
			$after['SearchIndex']['data'],
			'Should change firstname and lastname in SearchIndex.data'
		);
	}

	/**
	 * Confirm that when an authorized name is deleted, the data record in
	 * SearchIndex is updated with the change.
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$id = 2;
		$SearchIndex = ClassRegistry::init('SearchIndex');
		$before = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'IB1234. Incomplete. Billing. someone.else@example.com. George. Washington. Lorem. SetDefaults',
			$before['SearchIndex']['data']
		);

		$data = [
			'AuthorizedName' => [
				'customers_id' => $id,
			]
		];
		$this->AuthorizedName->data = $data;
		$this->AuthorizedName->delete(4);

		$after = $SearchIndex->findByAssociationKey($id);
		$this->assertSame(
			'IB1234. Incomplete. Billing. someon.else@example.com. George. Washington',
			$after['SearchIndex']['data'],
			'Should remove firstname and lastname in SearchIndex.data'
		);
	}
}
