<?php
App::uses('Controller', 'Controller');
App::uses('View', 'Core');
App::uses('AppFormHelper', 'View/Helper');

class AppFormHelperTest extends CakeTestCase {
	public $AppFormHelper = null;

	public function setUp() {
		parent::setUp();
		$Controller = new Controller();
		$this->View = $View = new View($Controller);
		$this->AppFormHelper = new AppFormHelper($View);
	}

	public function tearDown() {
		unset($this->AppFormHelper);
		parent::tearDown();
	}

	/**
	 * Confirm the helper creates the correct HTML
	 *
	 * @return	void
	 */
	public function testCreateNoOptions() {
		$expected = '<form action="/" class="form-horizontal" id="fooForm" method="post" accept-charset="utf-8"><div style="display:none;"><input type="hidden" name="_method" value="POST"/></div>';
		$result = $this->AppFormHelper->create('foo');
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm the helper creates the correct HTML with options
	 *
	 * @return	void
	 */
	public function testCreateWithOptions() {
		$expected = '<form action="/" class="form-horizontal" foo="bar" id="fooForm" method="post" accept-charset="utf-8"><div style="display:none;"><input type="hidden" name="_method" value="POST"/></div>';
		$result = $this->AppFormHelper->create('foo', array('foo' => 'bar'));
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm the helper creates the correct HTML
	 *
	 * @return	void
	 */
	public function testInputNoOptions() {
		$expected = '<div class="input text"><label for="foo">Foo</label><input name="data[foo]" type="text" id="foo"/></div>';
		$result = $this->AppFormHelper->input('foo');
		$this->assertEquals($expected, $result);
	}

	/**
	 * Confirm the helper creates the correct HTML when type => checkbox
	 *
	 * @return	void
	 */
	public function testInputWithOptions() {
		$expected = '<div class="input checkbox"><input type="hidden" name="data[foo]" id="foo_" value="0"/><input type="checkbox" name="data[foo]" value="1" id="foo"/><label for="foo">Foo</label></div>';

		$result = $this->AppFormHelper->input('foo', array('type' => 'checkbox'));
		$this->assertEquals($expected, $result);
	}

}
