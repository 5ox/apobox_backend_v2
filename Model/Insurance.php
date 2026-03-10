<?php
/**
 * Insurance
 */

App::uses('AppModel', 'Model');

/**
 * Insurance Model
 *
 */
class Insurance extends AppModel {

	/**
	 * Use table
	 *
	 * @var mixed False or table name
	 */
	public $useTable = 'insurance';

	/**
	 * Primary key field
	 *
	 * @var	string
	 */
	public $primaryKey = 'insurance_id';

	/**
	 * Takes a given coverage amount and returns the fee to insure that package.
	 * If the supplied $coverage amount can be parsed as a valid number, a
	 * matching Insurance record is attempted to be found. If a match is found,
	 * the `insurance_fee` field is returned, otherwise boolean false.
	 *
	 * @param mixed $coverage The coverage amount to find a match for
	 * @return mixed A matching insurance fee or bool false
	 */
	public function getFeeForCoverageAmount($coverage) {
		if (!$coverage) {
			return 0.00;
		}

		$num = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
		$coverage = $num->parse($coverage);
		if ($coverage) {
			$record = $this->find('first', array(
				'conditions' => array(
					$this->alias . '.amount_from <=' => $coverage,
					$this->alias . '.amount_to >=' => $coverage,
				),
			));

			if (!empty($record)) {
				return $record[$this->alias]['insurance_fee'];
			}
		}

		return false;
	}

	/**
	 * Checks that no gaps exist between one records `amount_to` and the next's `amount_from`
	 * returns true when table appears OK. False otherwise.
	 *
	 * @return bool
	 */
	public function checkIntegrity() {
		$results = $this->query(
			'SELECT Insurance.insurance_id, (
				SELECT (insurance2.`amount_from` - Insurance.`amount_to`)
				FROM insurance as insurance2
				WHERE insurance2.amount_from > Insurance.amount_to
				ORDER BY amount_to ASC
				LIMIT 1
			) AS diff
			FROM insurance as Insurance
			HAVING diff > 0.01
			ORDER BY amount_to ASC;',
			false
		);

		return empty($results);
	}

}
