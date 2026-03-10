<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class: ReportHelper
 *
 * @see AppHelper
 */
class ReportHelper extends AppHelper {

	/**
	 * helpers
	 *
	 * @var array
	 */
	public $helpers = array(
		'Time',
		'Number',
	);

	/**
	 * formatPrice
	 *
	 * @param int $price The price to format
	 * @return string The formatted price
	 */
	public function formatPrice($price) {
		return $this->Number->currency($price);
	}
}
