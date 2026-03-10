<?php
App::uses('ModelBehavior', 'Model');

/**
 * Class: SearchableBehavior
 *
 * Imported from https://github.com/connrs/Searchable-Behaviour-for-CakePHP
 * and updated.
 *
 * @see ModelBehavior
 */
class SearchableBehavior extends ModelBehavior {

	/**
	 * defaultSettings
	 *
	 * @var array
	 */
	public $defaultSettings = [
		'foreignKey' => false,
		'_index' => false,
		'rebuildOnUpdate' => true,
		'fields' => '*',
	];

	/**
	 * settings
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * SearchIndex
	 *
	 * @var mixed
	 */
	public $SearchIndex;

	/**
	 * model
	 *
	 * @var mixed
	 */
	public $model;

	/**
	 * setup
	 *
	 * @param Model $Model The Model
	 * @param array $config Optional config
	 * @return void
	 */
	public function setup(Model $Model, $config = []) {
		$this->settings[$Model->name] = array_merge($this->defaultSettings, $config);
	}

	/**
	 * processData
	 *
	 * @param Model $Model The Model
	 * @return mixed The results of the model's indexData() method
	 */
	public function processData(Model $Model) {
		return $Model->indexData();
	}

	/**
	 * beforeSave
	 *
	 * @param Model $Model The Model
	 * @param array $options Optional options
	 * @return bool true
	 */
	public function beforeSave(Model $Model, $options = []) {
		if ($Model->id) {
			$this->settings[$Model->alias]['foreignKey'] = $Model->id;
		} else {
			$this->settings[$Model->alias]['foreignKey'] = 0;
		}
		if ($this->settings[$Model->alias]['foreignKey'] == 0 || $this->settings[$Model->alias]['rebuildOnUpdate']) {
			$this->settings[$Model->alias]['_index'] = $this->processData($Model);
		}
		return true;
	}

	/**
	 * afterSave
	 *
	 * @param Model $Model The Model
	 * @param mixed $created The created status of the record
	 * @param array $options Optional options
	 * @return bool true
	 */
	public function afterSave(Model $Model, $created, $options = []) {
		if ($this->settings[$Model->alias]['_index'] !== false) {
			if (!$this->SearchIndex) {
				$this->SearchIndex = ClassRegistry::init('SearchIndex', true);
			}
			if ($this->settings[$Model->alias]['foreignKey'] == 0) {
				$this->settings[$Model->alias]['foreignKey'] = $Model->getLastInsertID();
				$this->SearchIndex->create();
				$this->SearchIndex->save(
					[
						'SearchIndex' => [
							'model' => $Model->alias,
							'association_key' => $this->settings[$Model->alias]['foreignKey'],
							'data' => $this->settings[$Model->alias]['_index']
						]
					]
				);
			} else {
				$searchEntry = $this->SearchIndex->find('first', [
					'conditions' => [
						'model' => $Model->alias,
						'association_key' => $this->settings[$Model->alias]['foreignKey']
					]
				]);
				$this->SearchIndex->save(
					[
						'SearchIndex' => [
							'id' => empty($searchEntry) ? 0 : $searchEntry['SearchIndex']['id'],
							'model' => $Model->alias,
							'association_key' => $this->settings[$Model->alias]['foreignKey'],
							'data' => $this->settings[$Model->alias]['_index']
						]
					]
				);
			}
			$this->settings[$Model->alias]['_index'] = false;
			$this->settings[$Model->alias]['foreignKey'] = false;
		}
		return true;
	}

	/**
	 * afterDelete
	 *
	 * @param Model $Model The Model
	 * @return void
	 */
	public function afterDelete(Model $Model) {
		if (!$this->SearchIndex) {
			$this->SearchIndex = ClassRegistry::init('SearchIndex', true);
		}
		$conditions = ['model' => $Model->alias, 'association_key' => $Model->id];
		$this->SearchIndex->deleteAll($conditions);
	}
}
