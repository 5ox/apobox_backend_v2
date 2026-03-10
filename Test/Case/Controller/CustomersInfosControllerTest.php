<?php
App::uses('CustomersInfosController', 'Controller');

/**
 * CustomersInfosController Test Case
 *
 */
class CustomersInfosControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.customer',
		'app.customers_info',
		'app.admin',
	);

	/**
	 * Confirm that the manger report with accessed by GET returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerReportGet() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers_infos',
			'action' => 'report',
			'manager' => true,
		));

		$this->testAction($url, array(
			'method' => 'get',
		));

		$this->assertArrayNotHasKey('interval', $this->vars);
		$this->assertArrayNotHasKey('results', $this->vars);
		$this->assertArrayHasKey('validIntervals', $this->vars);
		$this->assertArrayHasKey('validSortFields', $this->vars);
		$this->assertArrayHasKey('isManager', $this->vars);
	}

	/**
	 * Confirm that the manger report with POSTed data returns the expected
	 * view vars.
	 *
	 * @return void
	 */
	public function testManagerReportPost() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'customers_infos',
			'action' => 'report',
			'manager' => true,
		));

		$data = array(
			'interval' => 'year',
			'from_date' => '2015-01-01 00:00:00',
			'to_date' => '2015-12-31 00:00:00'
		);

		$this->testAction($url, array(
			'method' => 'post',
			'data' => $data,
		));

		$this->assertArrayHasKey('interval', $this->vars);
		$this->assertArrayHasKey('results', $this->vars);
		$this->assertArrayHasKey(0, $this->vars['results']);
		$this->assertEquals(3, $this->vars['results'][0]['total']);
		$this->assertArrayHasKey('validIntervals', $this->vars);
		$this->assertArrayHasKey('validSortFields', $this->vars);
		$this->assertArrayHasKey('isManager', $this->vars);
	}
}
