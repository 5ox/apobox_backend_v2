<?php
App::uses('AbstractPasswordHasher', 'Controller/Component/Auth');

/**
 * Class: ApoboxPasswordHasher
 *
 * @see AbstractPasswordHasher
 */
class ApoboxPasswordHasher extends AbstractPasswordHasher {

	/**
	 * hash
	 *
	 * @param string $password The password to hash
	 * @return string The hashed password
	 */
	public function hash($password) {
		$salt = bin2hex(openssl_random_pseudo_bytes(1));
		return Security::hash($salt . $password, 'md5') . ':' . $salt;
	}

	/**
	 * check
	 *
	 * @param string $password The cleartext password
	 * @param string $hashedPassword The hashed password
	 * @return bool True if a match, false if not
	 */
	public function check($password, $hashedPassword) {
		list($salted, $salt) = explode(':', $hashedPassword);
		return (Security::hash(($salt . $password), 'md5') === $salted);
	}
}
