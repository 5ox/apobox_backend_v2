<?php
App::uses('SearchIndexShell', 'Console/Command');
App::uses('ShellDispatcher', 'Console');
App::uses('ShellTestCase', 'Test');

/**
 * Class SearchIndexShellTest
 */
class SearchIndexShellTest extends ShellTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [];

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Shell = $this->getMockBuilder('SearchIndexShell')
			->setMethods(['_out'])
			->getMock();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Dispatch, $this->Shell);
	}

	/**
	 * Confirm the shell calls the expected methods with the expected data and
	 * produces the expected output messsage.
	 *
	 * @return void
	 */
	public function testCustomer() {
		$id = 12345;
		$existsId = 98765;
		$data = [
			[
				'Customer' => [
					'customers_id' => $id,
					'customers_firstname' => 'Foo',
					'customers_lastname' => 'Bar',
					'customers_email_address' => 'foo@bar.org',
				],
				'AuthorizedName' => [
					[
						'authorized_names_id' => 'anything',
						'authorized_firstname' => 'Auth',
						'authorized_lastname' => 'Name',
					],
				],
			],
		];
		$existsData = [
			'SearchIndex' => ['id' => $existsId],
		];
		$saveData = [
			'SearchIndex' => [
				'association_key' => $id,
				'model' => 'Customer',
				'data' => 'Foo. Bar. foo@bar.org. Auth. Name',
				'id' => $existsId,
			]
		];

		$Customer = $this->getMockForModel('Customer', ['find']);
		$SearchIndex = $this->getMockForModel('SearchIndex', [
			'findByAssociationKey',
			'save',
			'clear',
		]);

		$Customer->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('all'),
				$this->isType('array')
			)
			->will($this->returnValue($data));

		$SearchIndex->expects($this->once())
			->method('findByAssociationKey')
			->with(
				$this->identicalTo($id),
				$this->identicalTo('id')
			)
			->will($this->returnValue($existsData));
		$SearchIndex->expects($this->once())
			->method('save')
			->with($this->identicalTo($saveData));
		$SearchIndex->expects($this->once())
			->method('clear')
			->with();

		$this->Shell->expects($this->once())
			->method('_out')
			->with(
				$this->identicalTo('Customer search index rebuilt.'),
				$this->identicalTo('info')
			);

		$this->Shell->customer();
	}
}
