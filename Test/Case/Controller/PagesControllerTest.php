<?php
App::uses('PagesController', 'Controller');

/**
 * TestPages - Class to overwrite protected properties and methods
 * with public ones.
 */
class TestPagesController extends PagesController {
	public function initHttpSocket() {
		return parent::initHttpSocket();
	}
	public function initDomDocument() {
		return parent::initDomDocument();
	}
	public function _nameIsPrefixed($ctpName) {
		return parent::_nameIsPrefixed($ctpName);
	}
	public function _display($path, $prefix = '') {
		return parent::_display($path, $prefix);
	}
}

/**
 * PagesController Test Case
 *
 */
class PagesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array();

	/**
	 * setUp method
	 *
	 * @ref https://github.com/cakephp/cakephp/blob/12cf82ba19117e8bd6c33f6a757d7a9638cd529a/lib/Cake/Test/Case/View/ViewTest.php#L255,L285
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$path = APP . "Test/Samples/View/"; // Will be suffixed by `/Pages` automatically.
		App::build(array(
			'View' => array($path)
		), App::RESET);
	}

	/**
	 * tearDown method
	 *
	 * @ref https://github.com/cakephp/cakephp/blob/12cf82ba19117e8bd6c33f6a757d7a9638cd529a/lib/Cake/Test/Case/View/ViewTest.php#L287,L301
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Not specifying a page name for display() should redirect to the site root.
	 *
	 * @return void
	 */
	public function testDisplayNoPage() {
		$result = $this->testAction('/pages');
		$this->assertEquals(
			Configure::read('App.fullBaseUrl'),
			$this->headers['Location'],
			'Not requesting a page explicitly should redirect to the app\'s homepage.'
		);
	}

	/**
	 * A valid public page name should render the corresponding file.
	 *
	 * @return void
	 */
	public function testDisplayPublicPage() {
		$page = 'public_page';
		$result = $this->testAction('/pages/' . 'nested/' . $page);
		$this->assertStringMatchesFormat(
			"%A{$page}%A",
			$result,
			'Resulting page should contain its own file name.'
		);
	}

	/**
	 * Confirm that when a prefixed page is requested $prefix is auto-prepended
	 * to the final component of the path.
	 *
	 * @return void
	 */
	public function testDisplayPrefixedPage() {
		$path = ['bar'];
		$prefix = 'foo';
		$Pages = $this->generate('TestPages', [
			'methods' => [
				'render',
			],
		]);

		$Pages->expects($this->once())
			->method('render')
			->with('foo_bar'); // $prefix_$path

		$result = $Pages->_display($path, $prefix);

		$this->assertSame('Bar', $Pages->viewVars['title_for_layout']);
		$this->assertSame('bar', $Pages->viewVars['page']);
	}

	/**
	 * The public `::display()` action should **not** expose `admin_`
	 * prefixed files.
	 *
	 * @return void
	 */
	public function testDisplayNoAdminPrefixedPages() {
		$this->setExpectedException('NotFoundException', 'Invalid page');
		$result = $this->testAction('/pages/manager_home');
	}

	/**
	 * testTos
	 *
	 * @return void
	 */
	public function testTos() {
		Cache::delete('tos_content');
		$Pages = $this->generate('Pages', array(
			'methods' => array(
				'initHttpSocket',
				'initDomDocument',
			),
		));
		$this->HttpSocket = $this->getMock('HttpSocket',
			array('get', 'body', 'isOk')
		);
		$this->DomDocument = $this->getMock('DomDocument',
			array('loadHTML', 'getElementsByTagName', 'saveHtml')
		);

		$Pages->expects($this->once())
			->method('initHttpSocket')
			->will($this->returnValue($this->HttpSocket));
		$Pages->expects($this->once())
			->method('initDomDocument')
			->will($this->returnValue($this->DomDocument));

		$this->HttpSocket->expects($this->once())
			->method('isOk')
			->will($this->returnValue(true));
		$this->HttpSocket->expects($this->once())
			->method('get')
			->with('http://www.apobox.com')
			->will($this->returnValue($this->HttpSocket));
		$this->HttpSocket->expects($this->once())
			->method('body')
			->will($this->returnValue('html'));

		$this->DomDocument->expects($this->once())
			->method('loadHTML')
			->will($this->returnValue($this->DomDocument));
		$this->DomDocument->expects($this->once())
			->method('getElementsByTagName')
			->with('pre')
			->will($this->returnValue(['canary']));
		$this->DomDocument->expects($this->once())
			->method('saveHtml')
			->with('canary')
			->will($this->returnValue('html'));

		$result = $this->testAction('/tos', array());
		$this->assertArrayHasKey('content', $this->vars);
		Cache::delete('tos_content');
	}

	/**
	 * Confirm an instance of class HttpSocket is created by initHttpSocket()
	 *
	 * @return void
	 */
	public function testInitHttpSocket() {
		$PagesController = new TestPagesController();
		$this->assertInstanceOf('HttpSocket', $PagesController->initHttpSocket());
	}

	/**
	 * Confirm an instance of class DomDocument is created by initDomDocument()
	 *
	 * @return void
	 */
	public function testInitDomDocument() {
		$PagesController = new TestPagesController();
		$this->assertInstanceOf('DomDocument', $PagesController->initDomDocument());
	}

	/**
	 * Confirm if there are no active routing prefixes, the file is not
	 * considered to be prefixed.
	 *
	 * @return void
	 */
	public function testNameIsPrefixed() {
		Configure::write('Routing.prefixes', null);
		$PagesController = new TestPagesController();
		$result = $PagesController->_nameIsPrefixed(null);
		$this->assertFalse($result);
	}

	/**
	 * Not specifying a page name for display() should redirect to the site root.
	 *
	 * @return void
	 */
	public function testManagerDisplayNoPage() {
		$result = $this->testAction('/manager/pages');
		$this->assertEquals(
			Configure::read('App.fullBaseUrl'),
			$this->headers['Location'],
			'Not requesting a page explicitly should redirect to the app\'s homepage.'
		);
	}
}
