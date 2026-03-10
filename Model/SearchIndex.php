<?php
App::uses('AppModel', 'Model');

/**
 * Class: SearchIndex
 *
 * Imported from https://github.com/connrs/Searchable-Behaviour-for-CakePHP
 * and updated.
 *
 * @see AppModel
 */
class SearchIndex extends AppModel {

	/**
	 * name
	 *
	 * @var string
	 */
	public $name = 'SearchIndex';

	/**
	 * useTable
	 *
	 * @var string
	 */
	public $useTable = 'search_indices';

	/**
	 * models
	 *
	 * @var array
	 */
	private $models = [];

	/**
	 * recursive
	 *
	 * @var int
	 */
	public $recursive = 1;

	/**
	 * virtualFields
	 *
	 * @var array
	 */
	public $virtualFields = [
		'relevance' => null
	];

	/**
	 * bindTo
	 *
	 * @param mixed $model The model
	 * @return void
	 */
	private function bindTo($model) {
		$this->bindModel(
			[
				'belongsTo' => [
					$model => [
						'className' => $model,
						'conditions' => 'SearchIndex.model = \'' . $model . '\'',
						'foreignKey' => 'association_key'
					]
				]
			], false
		);
	}

	/**
	 * searchModels
	 *
	 * @param array $models The models
	 * @return void
	 */
	public function searchModels($models = []) {
		if (is_string($models)) {
			$models = [$models];
		}
		$this->models = $models;
		foreach ($models as $model) {
			$this->bindTo($model);
		}
	}

	/**
	 * beforeFind
	 *
	 * @param mixed $queryData The queryData
	 * @return void
	 */
	public function beforeFind($queryData) {
		$modelsCondition = false;
		if (!empty($this->models)) {
			$modelsCondition = [];
			foreach ($this->models as $model) {
				$Model = ClassRegistry::init($model);
				$modelsCondition[] = $model . '.' . $Model->primaryKey . ' IS NOT NULL';
			}
		}

		if (isset($queryData['conditions'])) {
			if ($modelsCondition) {
				if (is_string($queryData['conditions'])) {
					$queryData['conditions'] .= ' AND (' . join(' OR ', $modelsCondition) . ')';
				} else {
					$queryData['conditions'][] = ['OR' => $modelsCondition];
				}
			}
		} else {
			if ($modelsCondition) {
				$queryData['conditions'][] = ['OR' => $modelsCondition];
			}
		}

		// Add relevance field
		if (isset($queryData['conditions']) && is_string($queryData['conditions'])) {
			//Remove any other from the conditions just to calculate relevance
			$conditions = explode(' AND ', $queryData['conditions']);
			$this->virtualFields['relevance'] = $conditions[0];
		} else {
			// Do nothing, set relevance to 1
			$this->virtualFields['relevance'] = 1;
		}

		return $queryData;
	}

	/**
	 * afterFind
	 *
	 * @param mixed $results The results
	 * @param mixed $primary The primary
	 * @return void
	 */
	public function afterFind($results, $primary = false) {
		if ($primary) {
			foreach ($results as $x => $result) {
				if (Hash::get($result, 'SearchIndex.model')) {
					$Model = ClassRegistry::init($result['SearchIndex']['model']);
					$results[$x]['SearchIndex']['displayField'] = $Model->displayField;
				}
			}
		}
		return $results;
	}

	/**
	 * fuzzyize
	 *
	 * @param mixed $query The query
	 * @return void
	 */
	public function fuzzyize($query) {
		$query = preg_replace('/\s+/', '\s*', $query);
		return $query;
	}
}
