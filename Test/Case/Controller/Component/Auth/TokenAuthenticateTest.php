<?php
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('TokenAuthenticate', 'Controller/Component/Auth');

/**
 * Class: TestTokenAuthenticate
 */
class TestTokenAuthenticate extends TokenAuthenticate {
	public function findUserForToken($token) {
		return parent::findUserForToken($token);
	}
}

/**
 * Class: TokenAuthenticateTest
 */
class TokenAuthenticateTest extends CakeTestCase {

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(null)
			->setConstructorArgs([new ComponentCollection(), [
				'userModel' => 'Admin'
			]])
			->getMock();

		$this->Request = $this->getMockBuilder('CakeRequest')
			->setMethods(['header'])
			->getMock();
		$this->Response = $this->getMockBuilder('CakeResponse')
			->setMethods(null)
			->getMock();
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Token);
		unset($this->Request);
		unset($this->Response);
	}

	/**
	 * Confirm if an authorization header does not have a token value the method
	 * returns an empty string.
	 *
	 * @return void
	 */
	public function testGetTokenEmpty() {
		$token = null;
		$this->Request->staticExpects($this->once())
			->method('header')
			->with($this->identicalTo('Authorization'))
			->will($this->returnValue('Bearer ' . $token));

		$result = $this->Token->getToken($this->Request);

		$this->assertEmpty($result);
	}

	/**
	 * Confirm a token can be extracted from an authorization header and
	 * returned.
	 *
	 * @return void
	 */
	public function testGetToken() {
		$token = 'token';
		$this->Request->staticExpects($this->once())
			->method('header')
			->with($this->identicalTo('Authorization'))
			->will($this->returnValue('Bearer ' . $token));

		$result = $this->Token->getToken($this->Request);

		$this->assertSame($token, $result);
	}

	/**
	 * Confirm an instance of `AppModel` is created and configured for the model
	 * specified in $settings used in the constructor.
	 *
	 * @return void
	 */
	public function testGetModel() {
		$result = $this->Token->getModel();

		$this->assertInstanceOf('AppModel', $result);
		$this->assertSame('Admin', $result->alias);
	}

	/**
	 * Confirm the model find call is supplied with the correct and expected
	 * options when the `userFields` setting is used.
	 *
	 * @return void
	 */
	public function testFindUserForTokenWithUserFields() {
		$token = 'token';
		$options = [
			'conditions' => [
				'User.token' => $token,
			],
			'recursive' => 0,
			'contain' => [],
			'fields' => ['foo', 'bar'],
		];

		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['getModel'])
			->setConstructorArgs([new ComponentCollection(), [
				'userFields' => ['foo', 'bar']
			]])
			->getMock();
		$Model = $this->getMockForModel('Admin', ['find']);

		$this->Token->expects($this->once())
			->method('getModel')
			->will($this->returnValue($Model));
		$Model->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('first'),
				$this->identicalTo($options)
			)
			->will($this->returnValue('user'));

		$result = $this->Token->findUserForToken($token);

		$this->assertSame('user', $result);
	}

	/**
	 * Confirm the model find call is supplied with the correct and expected
	 * options with default settings.
	 *
	 * @return void
	 */
	public function testFindUserForTokenWithDefaults() {
		$token = 'token';
		$options = [
			'conditions' => [
				'User.token' => $token,
			],
			'recursive' => 0,
			'contain' => [],
		];

		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['getModel'])
			->setConstructorArgs([new ComponentCollection(), []])
			->getMock();
		$Model = $this->getMockForModel('Admin', ['find']);

		$this->Token->expects($this->once())
			->method('getModel')
			->will($this->returnValue($Model));
		$Model->expects($this->once())
			->method('find')
			->with(
				$this->identicalTo('first'),
				$this->identicalTo($options)
			)
			->will($this->returnValue('user'));

		$result = $this->Token->findUserForToken($token);

		$this->assertSame('user', $result);
	}

	/**
	 * Confirm the expected exception is thrown if `findUserForToken` does not
	 * return a valid user.
	 *
	 * @return void
	 */
	public function testGetUserMissingToken() {
		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['findUserForToken', 'getToken'])
			->setConstructorArgs([new ComponentCollection(), []])
			->getMock();

		$this->Token->expects($this->exactly(2))
			->method('getToken')
			->with($this->identicalTo($this->Request))
			->will($this->returnValue('token'));
		$this->Token->expects($this->once())
			->method('findUserForToken')
			->with($this->identicalTo($this->Token->getToken($this->Request)))
			->will($this->returnValue([]));

		$this->setExpectedException(
			'UnauthorizedException',
			'Missing, invalid or expired token present in request. Include an Authorization header.'
		);

		$this->Token->getUser($this->Request);
	}

	/**
	 * Confirm the expected data is returned when a user is found.
	 *
	 * @return void
	 */
	public function testGetUserWithUser() {
		$data = ['foo' => 'bar'];
		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['findUserForToken', 'getToken'])
			->setConstructorArgs([new ComponentCollection(), []])
			->getMock();

		$this->Token->expects($this->exactly(2))
			->method('getToken')
			->with($this->identicalTo($this->Request))
			->will($this->returnValue('token'));
		$this->Token->expects($this->once())
			->method('findUserForToken')
			->with($this->identicalTo($this->Token->getToken($this->Request)))
			->will($this->returnValue(['User' => $data]));

		$result = $this->Token->getUser($this->Request);

		$this->assertSame($data, $result);
	}

	/**
	 * Confirm the `logout` method always returns bool true.
	 *
	 * @return void
	 */
	public function testLogout() {
		$this->assertTrue($this->Token->logout('foo'));
	}

	/**
	 * Confirm the method returns false and catches the thrown exception when
	 * `getUser` fails.
	 *
	 * @return void
	 */
	public function testAuthenticateFailure() {
		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['getUser'])
			->setConstructorArgs([new ComponentCollection(), []])
			->getMock();

		$this->Token->expects($this->once())
			->method('getUser')
			->with($this->identicalTo($this->Request))
			->will($this->throwException(new Exception));

		$result = $this->Token->authenticate($this->Request, $this->Response);

		$this->assertFalse($result);
	}

	/**
	 * Confirm when authentication is successful the method returns the result
	 * of `getUser`.
	 *
	 * @return void
	 */
	public function testAuthenticateSuccess() {
		$this->Token = $this->getMockBuilder('TestTokenAuthenticate')
			->setMethods(['getUser'])
			->setConstructorArgs([new ComponentCollection(), []])
			->getMock();

		$this->Token->expects($this->once())
			->method('getUser')
			->with($this->identicalTo($this->Request))
			->will($this->returnValue('canary'));

		$result = $this->Token->authenticate($this->Request, $this->Response);

		$this->assertSame('canary', $result);
	}
}
