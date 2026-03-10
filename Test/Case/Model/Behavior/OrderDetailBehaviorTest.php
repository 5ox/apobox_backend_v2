<?php
App::uses('OrderShipping', 'Model');
App::uses('OrderTotal', 'Model');
App::uses('OrderDetailBehavior', 'Model/Behavior');

class TestOrderDetailBehavior extends OrderDetailBehavior {
	public function updateOrderTotal(Model $Model) {
		return parent::updateOrderTotal($Model);
	}
}

/**
 * OrderDetailBehavior Test Case
 *
 */
class OrderDetailBehaviorTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.order',
		'app.order_total',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->OrderShipping = $this->getMockForModel('OrderShipping', null);
		$this->OrderSubtotal = $this->getMockForModel('OrderSubtotal', null);
		$this->OrderTotal = $this->getMockForModel('OrderTotal', null);
		$this->OrderShipping->Behaviors->load('TestOrderDetail', [
			'title' => 'Postage :',
			'class' => 'ot_shipping',
			'sort_order' => 1,
		]);
		$this->OrderSubtotal->Behaviors->load('TestOrderDetail', [
			'title' => 'Subtotal :',
			'class' => 'ot_subtotal',
			'sort_order' => 5,
		]);
		$this->OrderTotal->Behaviors->load('TestOrderDetail', [
			'title' => 'Total :',
			'class' => 'ot_total',
			'sort_order' => 6,
		]);
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->OrderDetail);

		parent::tearDown();
	}

	/**
	 * Test that `orderTotalExists()` will return false when it does not exist
	 * and that the `orderTotalExists` property is false on Order.
	 */
	public function testUpdateOrderTotalWhenNotExists() {
		$orderId = 1;
		$this->OrderTotal->delete(5);
		$data = $this->OrderShipping->find('first', ['conditions' => ['orders_id' => $orderId]]);
		$this->OrderShipping->set($data);

		$this->assertFalse($this->OrderShipping->updateOrderTotal());
	}

	/**
	 * Test that `updateOrderTotal()` will return false if current model is
	 * OrderTotal or OrderSubtotal and `orderTotalExists` is set true on model.
	 */
	public function testUpdateOrderTotalWhenOrderTotalOrOrderSubtotal() {
		$this->OrderShipping->Order->orderTotalExists = true;
		$this->assertFalse($this->OrderTotal->updateOrderTotal());
		$this->assertFalse($this->OrderSubtotal->updateOrderTotal());
	}

	/**
	 * Test that `updateOrderTotal()` will throw an error if `orderTotalExists`
	 * is set to true on the model (for some reason), but the proper data is
	 * not set on the model.
	 *
	 * @expectedException BadMethodCallException
	 */
	public function testUpdateOrderTotalWithBadData() {
		$this->OrderShipping->Order->orderTotalExists = true;
		$this->OrderShipping->updateOrderTotal();
	}

	/**
	 * Test that order total gets updated successfully when an Order* value
	 * is changed.
	 */
	public function testUpdateOrderTotalSuccess() {
		$orderId = 1;
		$newShippingValue = 10;

		$shippingBefore = $this->OrderShipping->find('first', ['conditions' => ['orders_id' => $orderId]]);
		$totalBefore = $this->OrderTotal->find('first', ['conditions' => ['orders_id' => $orderId]]);

		$shipping = $shippingBefore;
		$shipping['OrderShipping']['value'] = $newShippingValue;
		$shippingAfter = $this->OrderShipping->save($shipping);
		$totalAfter = $this->OrderTotal->find('first', ['conditions' => ['orders_id' => $orderId]]);
		$shippingDifference = ($shippingBefore['OrderShipping']['value'] - $newShippingValue);

		$this->assertSame(
			(float)($totalBefore['OrderTotal']['value'] - $shippingDifference),
			(float)$totalAfter['OrderTotal']['value']
		);
	}

	/**
	 * Test that `orderTotalExists()` will return true when it has previously
	 * been proven to exist, in which case, `orderTotalExists` is true on Order.
	 */
	public function testOrderTotalExistsCached() {
		$this->OrderShipping->Order->orderTotalExists = true;
		$this->assertTrue($this->OrderShipping->orderTotalExists());
	}

	/**
	 * Test that `orderTotalExists()` will throw an error if the result isn't
	 * cached and the proper data is not set on the model.
	 *
	 * @expectedException BadMethodCallException
	 */
	public function testOrderTotalExistsBadData() {
		$this->OrderShipping->orderTotalExists();
	}

	/**
	 * Test that `orderTotalExists()` will return true when it exists and that
	 * the `orderTotalExists` property is set on Order.
	 */
	public function testOrderTotalExistsTrue() {
		$orderId = 1;
		$data = $this->OrderShipping->find('first', ['conditions' => ['orders_id' => $orderId]]);
		$this->OrderShipping->set($data);

		$this->assertTrue($this->OrderShipping->orderTotalExists());
		$this->assertTrue($this->OrderShipping->Order->orderTotalExists);
	}

	/**
	 * Test that `orderTotalExists()` will return false when it does not exist
	 * and that the `orderTotalExists` property is false on Order.
	 */
	public function testOrderTotalExistsFalse() {
		$orderId = 1;
		$this->OrderTotal->delete(5);
		$data = $this->OrderShipping->find('first', ['conditions' => ['orders_id' => $orderId]]);
		$this->OrderShipping->set($data);

		$this->assertFalse($this->OrderShipping->orderTotalExists());
		$this->assertFalse($this->OrderShipping->Order->orderTotalExists);
	}

	/**
	 * Confirm the correct exception is thrown if key `title` is missing.
	 *
	 * @return void
	 */
	public function testSetupMissingTitle() {
		$this->setExpectedException('InvalidArgumentException', 'Missing key "title" for OrderDetailBehavior');
		$this->OrderTotal->Behaviors->unload('TestOrderDetail');
		$this->OrderTotal->Behaviors->load('TestOrderDetail');
	}

	/**
	 * Confirm the correct exception is thrown if key `class` is missing.
	 *
	 * @return void
	 */
	public function testSetupMissingClass() {
		$this->setExpectedException('InvalidArgumentException', 'Missing key "class" for OrderDetailBehavior');
		$this->OrderTotal->Behaviors->unload('TestOrderDetail');
		$this->OrderTotal->Behaviors->load('TestOrderDetail', [
			'title' => 'Total :',
		]);
	}

	/**
	 * Confirm the correct exception is thrown if key `sort_order` is missing.
	 *
	 * @return void
	 */
	public function testSetupMissingSortOrder() {
		$this->setExpectedException('InvalidArgumentException', 'Missing key "sort_order" for OrderDetailBehavior');
		$this->OrderTotal->Behaviors->unload('TestOrderDetail');
		$this->OrderTotal->Behaviors->load('TestOrderDetail', [
			'title' => 'Total :',
			'class' => 'ot_total',
		]);
	}

	/**
	 * Confirm if the fieldlist array has a value that is an array and contains
	 * the string `value` the whilelist is set as expected.
	 *
	 * @return void
	 */
	public function testBeforeSaveFieldIsArray() {
		$Behavior = $this->getMockBuilder('OrderDetailBehavior')
			->setMethods(null)
			->getMock();
		$Model = $this->getMockForModel('OrderTotal');
		$options = [
			'fieldList' => [
				'foo' => 'bar',
				'baz' => [
					'inner' => 'value',
				],
			],
		];

		$this->assertEmpty($Model->whitelist);

		$result = $Behavior->beforeSave($Model, $options);

		$this->assertTrue($result);
		$this->assertSame(['text'], $Model->whitelist);
	}
}
