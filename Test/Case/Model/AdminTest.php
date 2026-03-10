<?php
App::uses('Admin', 'Model');

/**
 * Admin Test Case
 *
 */
class AdminTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.admin',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->Admin = ClassRegistry::init('Admin');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Admin);

		parent::tearDown();
	}

	/**
	 * Confirm that beforeSave() hashes a password
	 *
	 * @return	void
	 */
	public function testBeforeSave() {
		$id = 1;
		$admin = $this->Admin->findByid($id);

		$this->assertEquals('password', $admin['Admin']['password']);

		$this->Admin->set($admin);
		$result = $this->Admin->beforeSave();

		$this->assertTrue($result);
		$this->assertNotEquals('password', $this->Admin->data['Admin']['password']);
		$this->assertRegexp('/^\$2a.*/', $this->Admin->data['Admin']['password']);
	}

	/**
	 * Confirm that beforeValidate() adds a validation rule for `token` if
	 * `role` is equal to `api`.
	 *
	 * @return	void
	 */
	public function testBeforeValidateRoleApi() {
		$data = array(
			'Admin' => array(
				'role' => 'api',
				'token' => '',
			),
		);
		$beforeRuleCount = $this->Admin->validator()->count();
		$this->Admin->set($data);
		$result = $this->Admin->beforeValidate();

		$afterRuleCount = $this->Admin->validator()->count();
		$this->assertNotEquals($beforeRuleCount, $afterRuleCount);
	}

	/**
	 * Confirm that beforeValidate() doesn't add a validation rule for `token` if
	 * `role` is NOT equal to `api`.
	 *
	 * @return	void
	 */
	public function testBeforeValidateRoleManager() {
		$data = array(
			'Admin' => array(
				'role' => 'manager',
				'token' => '',
			),
		);
		$beforeRuleCount = $this->Admin->validator()->count();
		$this->Admin->set($data);
		$result = $this->Admin->beforeValidate();

		$afterRuleCount = $this->Admin->validator()->count();
		$this->assertEquals($beforeRuleCount, $afterRuleCount);
	}

	/**
	 * Confirm that beforeValidate() removes the `password`  and 'confirm_new_password`
	 * validation rules if `id` is set and `password` is empty.
	 *
	 * @return	void
	 */
	public function testBeforeValidateOnEdit() {
		$data = array(
			'Admin' => array(
				'role' => 'manager',
				'id' => 1,
				'password' => '',
				'confirm_new_password' => ''
			),
		);
		$beforeRuleCount = $this->Admin->validator()->count();
		$this->Admin->set($data);
		$this->assertArrayHasKey('password', $this->Admin->data['Admin']);
		$this->assertArrayHasKey('confirm_new_password', $this->Admin->data['Admin']);
		$result = $this->Admin->beforeValidate();

		$afterRuleCount = $this->Admin->validator()->count();
		$this->assertNotEquals($beforeRuleCount, $afterRuleCount);
		$this->assertEquals($beforeRuleCount - 2, $afterRuleCount);
		$this->assertArrayNotHasKey('password', $this->Admin->data['Admin']);
		$this->assertArrayNotHasKey('confirm_new_password', $this->Admin->data['Admin']);
	}

	/**
	 * Confirm that beforeValidate() doesn't remove the `password`  and 'confirm_new_password`
	 * validation rules if `id` is set and `password` is not empty.
	 *
	 * @return	void
	 */
	public function testBeforeValidateOnEditHasPasswords() {
		$data = array(
			'Admin' => array(
				'role' => 'manager',
				'id' => 1,
				'password' => 'testtest',
				'confirm_new_password' => 'testtest'
			),
		);
		$beforeRuleCount = $this->Admin->validator()->count();
		$this->Admin->set($data);
		$this->assertArrayHasKey('password', $this->Admin->data['Admin']);
		$this->assertArrayHasKey('confirm_new_password', $this->Admin->data['Admin']);
		$result = $this->Admin->beforeValidate();

		$afterRuleCount = $this->Admin->validator()->count();
		$this->assertEquals($beforeRuleCount, $afterRuleCount);
		$this->assertArrayHasKey('password', $this->Admin->data['Admin']);
		$this->assertArrayHasKey('confirm_new_password', $this->Admin->data['Admin']);
	}

	/**
	 * Confirm the method returns the correct model based on the structure and
	 * content of the query.
	 *
	 * @dataProvider provideDetermineModelToSearch
	 * @return	void
	 */
	public function testDetermineModelToSearch($query, $expected) {
		$result = $this->Admin->determineModelToSearch($query);
		$this->assertEquals($expected, $result);
	}

	public function provideDetermineModelToSearch() {
		return array(
			array('Bill Test', 'customer'),
			array('Test', 'customer'),
			array('X', 'customer'),
			array('X:12345', 'customer'),
			array('1234abcd', 'customer'),
			array('12345abcdefghijkl', 'customer'),
			array('S:', 'tracking'),
			array('s:12345', 'tracking'),
			array('S:12345', 'tracking'),
			array('S:abcdefg', 'tracking'),
			array('1A2B3C4D5E6F', 'order'),
			array('1a2b3c4d5e6f', 'order'),
			array('12345', 'order'),
		);
	}

	/**
	 * Confirm validateConfirmNewPassword returns false if the passwords don't
	 * match.
	 *
	 * @return	void
	 */
	public function testValidateConfirmNewPasswordsNotMatch() {
		$data = array(
			'Admin' => array(
				'password' => 'testtest',
			),
		);
		$this->Admin->set($data);
		$customer = array(
			'confirm_new_password' => 'foobar'
		);
		$this->assertFalse($this->Admin->validateConfirmNewPassword($customer));
	}

	/**
	 * Confirm validateConfirmNewPassword returns true if the passwords match.
	 *
	 * @return	void
	 */
	public function testValidateConfirmNewPasswordsMatch() {
		$data = array(
			'Admin' => array(
				'password' => 'testtest',
			),
		);
		$this->Admin->set($data);
		$customer = array(
			'confirm_new_password' => 'testtest'
		);
		$this->assertTrue($this->Admin->validateConfirmNewPassword($customer));
	}

}
