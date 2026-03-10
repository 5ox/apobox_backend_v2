<?php
/*
 * Custom test suite to execute all APP (non-composer managed) plugin tests.
 */
class AllPluginsTest extends PHPUnit_Framework_TestSuite {
	public static function suite() {
		$suite = new CakeTestSuite('All App Plugin Tests');
		$suite->addTestDirectoryRecursive(APP . 'Plugin' . DS . 'Usps');
		$suite->addTestDirectoryRecursive(APP . 'Plugin' . DS . 'Fedex');
		return $suite;
	}
}
