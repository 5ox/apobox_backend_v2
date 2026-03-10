<?php
App::uses('ModelBehavior', 'Model');

class CreditCardBehavior extends ModelBehavior {

	/**
	 * Per model settings array
	 */
	public $settings = array();

	/**
	 * setup
	 *
	 * @param Model $Model The Model
	 * @param array $settings Optional settings
	 * @return bool True to continue the save
	 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = array(
				'numberField' => 'cc_number',
				'cvvField' => 'cc_cvv',
			);
		}
		$this->settings[$Model->alias] = array_merge(
			$this->settings[$Model->alias], (array)$settings
		);
	}

	/**
	 * beforeSave
	 *
	 * @param Model $Model The Model
	 * @param array $options Optional options
	 * @return void
	 * @return bool True to continue the save
	 */
	public function beforeSave(Model $Model, $options = array()) {
		parent::beforeSave($Model, $options);
		if (!empty($Model->data[$Model->alias][$this->settings[$Model->alias]['numberField']])) {
			// Always mask number before saving
			$Model->data[$Model->alias][$this->settings[$Model->alias]['numberField'] . '_raw'] =
				$Model->data[$Model->alias][$this->settings[$Model->alias]['numberField']];
			$Model->data[$Model->alias][$this->settings[$Model->alias]['numberField']] =
				$this->maskCardNumber($Model, $Model->data[$Model->alias][$this->settings[$Model->alias]['numberField']]);
		}
		// Don't save CVV
		if (!empty($Model->data[$Model->alias][$this->settings[$Model->alias]['cvvField']])) {
			$Model->data[$Model->alias][$this->settings[$Model->alias]['cvvField'] . '_raw'] =
				$Model->data[$Model->alias][$this->settings[$Model->alias]['cvvField']];
			unset($Model->data[$Model->alias][$this->settings[$Model->alias]['cvvField']]);
		}

		return true;
	}

	/**
	 * afterFind
	 *
	 * @param Model $Model The Model
	 * @param array $results The modified data
	 * @param bool $primary Whether this model is being queried directly
	 * @return array Results array
	 */
	public function afterFind(Model $Model, $results, $primary = false) {
		parent::afterFind($Model, $results, $primary);
		foreach ($results as $key => $val) {
			if (isset($val[$Model->alias][$this->settings[$Model->alias]['cvvField']])) {
				$results[$key][$Model->alias][$this->settings[$Model->alias]['cvvField']] = $Model->maskCVV(
					$val[$Model->alias][$this->settings[$Model->alias]['cvvField']]
				);
			}
		}
		return $results;
	}

	/**
	 * Masks a credit card number showing only the last 4 digits.
	 *
	 * @param Model $Model The Model
	 * @param string $cardNumber The raw card number
	 * @return string Masked credit card number strings
	 */
	public function maskCardNumber(Model $Model, $cardNumber) {
		if (!empty($cardNumber)) {
			$cardNumber = 'XXXXXXXXXXXX' . substr($cardNumber, -4);
		}

		return $cardNumber;
	}

	/**
	 * Return a genreic string for CVV values
	 *
	 * @param string $cvv The raw cvv
	 * @return string Masked ccv
	 */
	public function maskCVV($cvv) {
		return '***';
	}
}
