<?php
App::uses('ModelBehavior', 'Model');

/**
 * OrderDetailBehavior
 *
 * Does all the heavy lifting for the orders_status table requirements. When
 * one of the orders_status records is created or updated for an order, this
 * behavior will do the right thing. This ensures the order total, etc are
 * handled correctly.
 */
class OrderDetailBehavior extends ModelBehavior {

	/**
	 * Per model settings array
	 */
	public $settings = array();

	/**
	 * setup
	 *
	 * @param Model $Model The Model
	 * @param array $settings Optional settings
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($settings['title'])) {
			throw new InvalidArgumentException('Missing key "title" for OrderDetailBehavior');
		}
		if (!isset($settings['class'])) {
			throw new InvalidArgumentException('Missing key "class" for OrderDetailBehavior');
		}
		if (!isset($settings['sort_order'])) {
			throw new InvalidArgumentException('Missing key "sort_order" for OrderDetailBehavior');
		}

		$this->settings[$Model->alias]['title'] = $settings['title'];
		$this->settings[$Model->alias]['class'] = $settings['class'];
		$this->settings[$Model->alias]['sort_order'] = $settings['sort_order'];
	}

	/**
	 * beforeFind
	 *
	 * @param Model $Model The Model
	 * @param mixed $query The query
	 * @return array
	 */
	public function beforeFind(Model $Model, $query) {
		$query['conditions'] = Hash::merge(
			(array)$query['conditions'],
			array($Model->alias . '.class' => $this->settings[$Model->alias]['class'])
		);
		return $query;
	}

	/**
	 * beforeValidate
	 *
	 * @param Model $Model The Model
	 * @param array $options Optional options
	 * @return bool True
	 */
	public function beforeValidate(Model $Model, $options = array()) {
		if (!empty($Model->data[$Model->alias]['title'])) {
			$this->settings[$Model->alias]['title'] = $Model->data[$Model->alias]['title'];
		}
		$Model->data[$Model->alias] = array_merge(
			$Model->data[$Model->alias],
			array(
				'title' => $this->settings[$Model->alias]['title'],
				'class' => $this->settings[$Model->alias]['class'],
				'sort_order' => $this->settings[$Model->alias]['sort_order'],
			));

		return true;
	}

	/**
	 * Apply order detail logic before saving the record.
	 *
	 * Format and save text field: The old oscommerce site uses the text field.
	 * So although this is bad DB practice, we must format and save it for
	 * backwards compatibility with that system.
	 *
	 * @param Model $Model The model.
	 * @param Array $options Optional save options.
	 * @return bool Return true to continue the save operation.
	 */
	public function beforeSave(Model $Model, $options = array()) {
		$whitelist = false;
		if (isset($options['fieldList'])) {
			foreach ($options['fieldList'] as $key => $field) {
				if ($field == 'value') {
					$whitelist = true;
				} elseif (is_array($field)) {
					if (in_array('value', $field)) {
						$whitelist = true;
					}
				}
			}
		}
		if ($whitelist) {
			$Model->whitelist[] = 'text';
		}

		$value = !empty($Model->data[$Model->alias]['value']) ? $Model->data[$Model->alias]['value'] : 0;
		$text = '$' . number_format($value, 2);
		$Model->data[$Model->alias]['text'] = $text;
		if ($Model->alias === 'OrderTotal') {
			$Model->data[$Model->alias]['text'] = '<b>' . $text . '</b>';
		}

		return true;
	}

	/**
	 * afterSave
	 *
	 * @param Model $Model The Model
	 * @param bool $created True if this save created a new record
	 * @param array $options Optional options passed from Model::save()
	 * @return void
	 */
	public function afterSave(Model $Model, $created, $options = array()) {
		$this->updateOrderTotal($Model);
	}

	/**
	 * Handles updating the order total record if it already exists. Otherwise
	 * it just returns. Order totals are generated last when a new order is
	 * created, so this should always do the right thing.
	 *
	 * @param Model $Model The Model
	 * @return bool True if successful update or false if missing fields or update failed
	 * @throws BadMethodCallException
	 */
	protected function updateOrderTotal(Model $Model) {
		if (in_array($Model->alias, array('OrderTotal', 'OrderSubtotal'))) {
			return false;
		}

		if (!$this->orderTotalExists($Model)) {
			return false;
		}

		if (empty($Model->data[$Model->alias]['orders_id'])) {
			throw new BadMethodCallException('Must set model data before updating total');
		}

		$orderId = $Model->data[$Model->alias]['orders_id'];
		$OrderTotal = ClassRegistry::init('OrderTotal');
		return (bool)$OrderTotal->updateTotal($orderId);
	}

	/**
	 * Checks that an order total record already exists so that it can be
	 * updated.
	 *
	 * @param Model $Model The Model
	 * @return bool
	 * @throws BadMethodCallException
	 */
	public function orderTotalExists(Model $Model) {
		if ($Model->Order->orderTotalExists) {
			return true;
		}

		if (empty($Model->data[$Model->alias]['orders_id'])) {
			throw new BadMethodCallException('Must set model data before checking that total exists');
		}

		$OrderTotal = ClassRegistry::init('OrderTotal');
		$Model->Order->orderTotalExists = (bool)$OrderTotal->find('first', array('conditions' => [
			'class' => 'ot_total',
			'orders_id' => $Model->data[$Model->alias]['orders_id'],
		]));
		return $Model->Order->orderTotalExists;
	}
}
