<?php
App::uses('Customer', 'Model');

class CreditCardBehaviorTest extends CakeTestCase {

	/**
	 *
	 */
	public $fixtures = array('app.customer');

	public function setUp() {
		parent::setUp();
		$this->Customer = $this->getMockForModel('Customer', array('authorizeCreditCard'));
		// Ensure CreditCardBehavior is attached to Customer model
		$this->Customer->Behaviors->load('CreditCard');
		$this->Customer->Behaviors->unload('Searchable'); // Unload so we don't need to mock more
	}

	public function tearDown() {
		unset($this->Customer);
		parent::tearDown();
	}

	/**
	 * Test that credit card number gets obfuscated on save. This requires
	 * the encrypted field to also be allowed during save.
	 */
	public function testBeforeSaveCCNumberIsObfuscated() {
		$this->Customer->expects($this->once())
			->method('authorizeCreditCard')
			->will($this->returnValue(true));
		$expected = 'XXXXXXXXXXXX4242';
		$result = $this->Customer->save(
			['Customer' => [
				'customers_id' => 1,
				'cc_number' => '4242424242424242'
			]],
			true,
			['cc_number', 'cc_number_encrypted']
		);

		$this->assertEquals($expected, $result['Customer']['cc_number']);
	}

	/**
	 * Test that CVV field does not get stored.
	 */
	public function testBeforeSaveCCCvvIsNotSaved() {
		$this->Customer->expects($this->never())
			->method('authorizeCreditCard');
		$result = $this->Customer->save(
			['Customer' => [
				'customers_id' => 1,
				'customers_firstname' => 'test',
				'customers_lastname' => 'user',
				'cc_cvv' => '424'
			]],
			true,
			['customers_firstname', 'customers_lastname', 'cc_cvv']
		);

		$this->assertArrayNotHasKey('cc_cvv', $result['Customer']);
	}

	public function testAfterFindCvvIsMasked() {
		$modelId = 1;
		$expected = '***';
		$result = $this->Customer->find('first', array(
			'Customer.customers_id' => $modelId,
		));

		$this->assertEquals($expected, $result['Customer']['cc_cvv']);
	}

	public function testAfterFindCvvIsMaskedWhenEmpty() {
		$modelId = 2;
		$expected = '***';
		$result = $this->Customer->find('first', array(
			'Customer.customers_id' => $modelId,
		));

		$this->assertEquals($expected, $result['Customer']['cc_cvv']);
	}

}
