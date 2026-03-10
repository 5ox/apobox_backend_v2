<?php
App::uses('ApoboxPasswordHasher', 'Controller/Component/Auth');

class ApoboxPasswordHasherTest extends CakeTestCase {

	public function setUp() {
		$this->hasher = New ApoboxPasswordHasher();
	}

	public function tearDown() {
		unset($this->hasher);
	}

	public function testCheck() {
		$this->assertTrue($this->hasher->check('12345678', 'ecbb7eea36ee843a19ff31954af2c4ec:e4'));
	}

	public function testCheckCanFail() {
		$this->assertFalse($this->hasher->check('password', 'ecbb7eea36ee843a19ff31954af2c4ec:e4'));
	}

	public function testHash() {
		$password = 'password';
		$hashed = $this->hasher->hash($password);

		$this->assertContains(':', $hashed);
		list($hash, $salt) = explode(':', $hashed);
		$this->assertEqual(strlen($hash), 32);
		$this->assertEqual(strlen($salt), 2);
	}

	public function testHashAndCheck() {
		$password = 'password';
		$hashed = $this->hasher->hash($password);

		$this->assertTrue($this->hasher->check($password, $hashed));
	}

	public function testHashDoesNotduplicate() {
		$password = 'password';
		$salt1 = $salt2 = null;
		$count = 0;
		while ($salt1 === $salt2 && $count < 2) {
			$hashed1 = $this->hasher->hash($password);
			$hashed2 = $this->hasher->hash($password);

			list(, $salt1) = explode(':', $hashed1);
			list(, $salt2) = explode(':', $hashed2);

			if ($salt1 === $salt2) { $count++; }
		}

		$this->assertNotEqual($count, 2, 'Salt was the same twice in a row, go buy a lotto ticket.');
		$this->assertNotEqual($hashed1, $hashed2);
	}

	public function testHashRarelyDuplicates() {
		$password = 'password';
		$salt1 = $salt2 = null;
		$count = 0;
		for ($i=0; $i < 51200; $i++) {
			$hashed1 = $this->hasher->hash($password);
			$hashed2 = $this->hasher->hash($password);

			list(, $salt1) = explode(':', $hashed1);
			list(, $salt2) = explode(':', $hashed2);
			if ($salt1 === $salt2) { $count++; }
		}
		$average = $count/200;

		$this->assertLessThan(1.2, $average, 'Expecting collision average less than 1.2, but got ' . $average);
	}
}
