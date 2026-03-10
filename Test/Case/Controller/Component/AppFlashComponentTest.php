<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('AppFlashComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');

/**
 * A fake controller to test against
 *
 */
class TestController extends Controller {
}

/**
 * AppFlashComponent Test Case
 *
 */
class AppFlashComponentTest extends CakeTestCase {

	protected static $_sessionBackup;

	/**
	 * Stores the component under test.
	 *
	 * @var Component
	 */
	public $Component = null;

	/**
	 * Stores the fake controller attached to the component being tested.
	 *
	 * @var Controller
	 */
	public $Controller = null;

	/**
	 * Fixtures to load.
	 *
	 * @var array
	 */
	public $fixtures = array(
	);

	/**
	 * test case startup
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		self::$_sessionBackup = Configure::read('Session');
		Configure::write('Session', array(
			'defaults' => 'php',
			'timeout' => 100,
			'cookie' => 'test'
		));
	}

	/**
	 * cleanup after test case.
	 *
	 * @return void
	 */
	public static function teardownAfterClass() {
		Configure::write('Session', self::$_sessionBackup);
	}

	/**
	 * setUp the needed stuff for our tests
	 *
	 */
	public function setUp() {
		parent::setUp();

		$_SESSION = null;

		// Setup our component and fake test controller
		$this->Component = new AppFlashComponent(new ComponentCollection());
		$this->Session = new SessionComponent(new ComponentCollection());
		$this->Controller = new TestController(new CakeRequest(), new CakeResponse());
		$this->Component->startup($this->Controller);
		$this->Session->startup($this->Controller);
	}

	/**
	 * Destroy any artifacts created for the test.
	 *
	 */
	public function tearDown() {
		CakeSession::destroy();

		unset($this->Component);
		unset($this->Controller);

		parent::tearDown();
	}

	/**
	 * Provide sets of [flash message, element name, view vars, session key,
	 * expected string, phpunit assertion message] to testSetFlash().
	 *
	 * @return void
	 */
	public function provideSetFlashArgs() {
		return array(
			array(
				'Hello world.',
				null,
				null,
				'Message.flash',
				array(
					'message' => 'Hello world.',
					'element' => 'Flash/flash_bootstrap',
					'params' => array(
						'class' => 'alert-default',
					),
					'key' => 'flash',
				),
				'Providing only a message should activate bootstrap default values.',
			),

			array(
				'Hello world.',
				array(
					'element' => 'override_element',
					'key' => 'keymaster',
					'class' => 'danger',
				),
				'danger',
				'Message.keymaster',
				array(
					'message' => 'Hello world.',
					'element' => 'Flash/override_element',
					'params' => array(
						'class' => 'alert-danger',
					),
					'key' => 'keymaster',
				),
				'Overriding values explicitly should retain those options.',
			),
			array(
				'Hello world.',
				array(
					'element' => 'override_element',
					'key' => 'keymaster',
					'class' => 'danger',
				),
				'alert-danger',
				'Message.keymaster',
				array(
					'message' => 'Hello world.',
					'element' => 'Flash/override_element',
					'params' => array(
						'class' => 'alert-danger',
					),
					'key' => 'keymaster',
				),
				'Overriding values explicitly should retain those options.',
			),
		);
	}

	/**
	 * Make sure the Flash->set() override produces expected results for a
	 * variety of inputs.
	 *
	 * NOTE: this test is disabled as it will only run with phpunit's --stderr
	 * flag on. To run this test, remove `noRun` from it's name.
	 *
	 * @dataProvider provideSetFlashArgs
	 * @param	string	$message	The flash message to display.
	 * @param	array	$options	Options to pass
	 * @param	string	$type		Shortcut to set flash type
	 * @param	string	$expectedKey	The expected session key where the data was saved.
	 * @param	array	$expectedValue	The expected session array value.
	 * @param	string	$msg		The PHPUnit assertion message to print if the test fails.
	 * @return	void
	 */
	public function noRuntestSetSession($message, $options, $type, $expectedKey, $expectedValue, $msg = '') {
		if (!CakeSession::start()) {
			$this->markTestSkipped('Session not available. Can not test Flash->set(). Use PHPUnit --stderr command line option.');
		}
		$this->Component->set($message, $options, $type);

		$component = new AppFlashComponent(new ComponentCollection());
		$component->set($message, $options, $type);

		$this->assertEquals(
			$expectedValue,
			$this->Session->read($expectedKey),
			$msg
		);
	}

	/**
	 * Confirm the Flash->set() override produces expected results for a
	 * variety of inputs.
	 *
	 * @dataProvider provideSet
	 * @return void
	 */
	public function testSet($message, $options, $type, $expected, $msg = '') {
		$Component = $this->getMockbuilder('AppFlashComponent')
			->disableOriginalConstructor()
			->setMethods(['parentSet'])
			->getMock();

		$Component->expects($this->once())
			->method('parentSet')
			->with(
				$this->identicalTo($message),
				$this->identicalTo($expected)
			);

		$result = $Component->set($message, $options, $type);
	}

	public function provideSet() {
		return [
			[
				'message',
				[],
				null,
				[
					'element' => 'flash_bootstrap',
					'params' => [
						'class' => 'alert-default',
					],
					'key' => 'flash',
				],
				'should use the default values',
			],
			[
				'message',
				['element' => 'foo_bar'],
				null,
				[
					'element' => 'foo_bar',
					'params' => [
						'class' => 'alert-default',
					],
					'key' => 'flash',
				],
				'should use the supplied value for `element`',
			],
			[
				'message',
				['params' => ['foo' => 'bar']],
				null,
				[
					'params' => [
						'class' => 'alert-default',
						'foo' => 'bar',
					],
					'element' => 'flash_bootstrap',
					'key' => 'flash',
				],
				'should add the supplied value to `params`',
			],
			[
				'message',
				[],
				'foobar',
				[
					'element' => 'flash_bootstrap',
					'params' => [
						'class' => 'alert-foobar',
					],
					'key' => 'flash',
				],
				'should set the `class` params key to the value of `type` prepended with `alert-`'
			],
			[
				'message',
				['key' => 'fooBar'],
				'alert-foobar',
				[
					'key' => 'fooBar',
					'element' => 'flash_bootstrap',
					'params' => [
						'class' => 'alert-foobar',
					],
				],
				'should set the `key` params key to the value of `key` option'
			],
		];
	}
}
