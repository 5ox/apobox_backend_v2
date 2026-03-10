<?php
App::uses('ReportsController', 'Controller');

/**
 * ReportsController Test Case
 *
 */
class ReportsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.address',
		'app.customer',
		'app.customers_info',
		'app.order',
		'app.order_total',
		'app.order_status',
	);


	/**
	 * Confirm that the manger report when accessed returns the expected view vars.
	 *
	 * @return void
	 */
	public function testManagerIndex() {
		$userId = 1;
		$url = Router::url(array(
			'controller' => 'reports',
			'action' => 'index',
			'manager' => true,
		));

		$this->testAction($url, array(
			'method' => 'get',
		));

		$this->assertArrayHasKey('salesChartData', $this->vars);
		$this->assertArrayHasKey('signupChartData', $this->vars);
		$this->assertArrayHasKey('demoChartData', $this->vars);
		$this->assertTrue(!empty($this->vars['demoChartData']));
	}
}
