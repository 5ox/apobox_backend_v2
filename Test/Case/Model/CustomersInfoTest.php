<?php
App::uses('CustomersInfo', 'Model');

/**
 * CustomersInfo Test Case
 *
 */
class CustomersInfoTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.customers_info',
		'app.customer',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->CustomersInfo = ClassRegistry::init('CustomersInfo');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->CustomersInfo);

		parent::tearDown();
	}

	/**
	 * Confirm that findCustomerTotalsReport returns expected data with various
	 * query combinations.
	 *
	 * @dataProvider provideFindCustomerTotalsReport
	 * @return void
	 */
	public function testFindCustomerTotalsReport($data, $expected) {
		$result = $this->CustomersInfo->findCustomerTotalsReport($data);
		$this->assertEquals($expected['count'], count($result));
		if ($result) {
			$this->assertArrayHasKey(0, $result);
			$this->assertArrayHasKey('total', $result[0]);
			$this->assertEquals($expected['total'], $result[0]['total']);
		}
	}

	/**
	 * provideFindCustomerTotalsReport
	 *
	 * @return array
	 */
	public function provideFindCustomerTotalsReport() {
		return array(
			array(
				'data' => array(
					'interval' => 'year',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00'
				),
				'result' => array(
					'count' => 1,
					'total' => 3,
				),
			),
			array(
				'data' => array(
					'interval' => 'month',
					'from_date' => '2015-07-01 00:00:00',
					'to_date' => '2015-07-31 00:00:00'
				),
				'result' => array(
					'count' => 0,
				),
			),
			array(
				'data' => array(
					'interval' => 'week',
					'from_date' => '2015-12-03 00:00:00',
					'to_date' => '2015-12-09 00:00:00'
				),
				'result' => array(
					'count' => 2,
					'total' => 1,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => '2014-06-26 00:00:00',
					'to_date' => '2014-06-26 23:59:59'
				),
				'result' => array(
					'count' => 1,
					'total' => 3,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => '2015-01-01 00:00:00',
					'to_date' => '2015-12-31 00:00:00',
				),
				'result' => array(
					'count' => 3,
					'total' => 1,
				),
			),
			array(
				'data' => array(
					'interval' => 'day',
					'from_date' => array(
						'day' => '21',
						'month' => '5',
						'year' => '2014'
					),
					'to_date' => array(
						'day' => '21',
						'month' => '10',
						'year' => '2016'
					),
				),
				'result' => array(
					'count' => 4,
					'total' => 3,
				),
			),
		);
	}

	/**
	 * Confirm that updateAccountCreated() can successfully created new customers_info
	 * records with the expected id and date fields.
	 *
	 * @return void
	 */
	public function testUpdateAccountCreated() {
		$missingId = 6;
		$before = $this->CustomersInfo->find('all');
		$beforeIds = Hash::extract($before, '{n}.CustomersInfo.customers_info_id');
		$this->assertFalse(array_search($missingId, $beforeIds));

		$this->Customers = ClassRegistry::init('Customer');
		$customers = $this->Customers->findMissingCustomersInfo();
		$this->assertNotEmpty($customers);
		$this->assertSame('2015-10-22 10:04:30', $customers[6]);

		$result = $this->CustomersInfo->updateAccountCreated($customers);
		$this->assertEmpty($result);

		$after = $this->CustomersInfo->find('all');
		$afterIds = Hash::extract($after, '{n}.CustomersInfo.customers_info_id');
		$this->assertSame(count($beforeIds) + 1, count($afterIds));
		$keyToTest = array_search($missingId, $afterIds);
		$this->assertTrue((bool)$keyToTest);
		$this->assertRegExp(
			'/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
			$after[$keyToTest]['CustomersInfo']['customers_info_date_account_created']
		);
		$this->assertSame(
			$customers[$missingId],
			$after[$keyToTest]['CustomersInfo']['customers_info_date_account_created']
		);
	}

	/**
	 * Confirm an array of failed to save ids is returned if saving fails.
	 *
	 * @return void
	 */
	public function testUpdateAccountCreatedSaveFails() {
		$Model = $this->getMockForModel('CustomersInfo', ['save', 'clear']);
		$data = [
			7 => '2016-08-02 13:07:07',
			9 => '2016-09-02 13:07:07',
		];

		$Model->expects($this->exactly(count($data)))
			->method('save')
			->will($this->returnValue(false));
		$Model->expects($this->exactly(count($data)))
			->method('clear');

		$result = $Model->updateAccountCreated($data);

		$this->assertSame(7, $result[0]);
		$this->assertSame(9, $result[1]);
	}
}
