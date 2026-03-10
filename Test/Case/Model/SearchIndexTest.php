<?php
App::uses('SearchIndex', 'Model');

/**
 * SearchIndex Test Case
 *
 */
class SearchIndexTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
		'app.search_index',
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->SearchIndex = ClassRegistry::init('SearchIndex');
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->SearchIndex);

		parent::tearDown();
	}

	/**
	 * Confirm that `searchModels()` which in turn calls `bindTo()` can add model
	 * associations on the fly when the supplied model is an array.
	 *
	 * @return	void
	 */
	public function testSearchModelsAsArray() {
		$model = 'Customer';

		$before = $this->SearchIndex->belongsTo;
		$this->assertEmpty($before);

		$result = $this->SearchIndex->searchModels([$model]);

		$after = $this->SearchIndex->belongsTo;
		$this->assertArrayHasKey($model, $after);
		$this->assertSame($model, $after[$model]['className']);
	}

	/**
	 * Confirm that `searchModels()` which in turn calls `bindTo()` can add model
	 * associations on the fly when the supplied model is a string.
	 *
	 * @return	void
	 */
	public function testSearchModelsAsString() {
		$model = 'Customer';

		$before = $this->SearchIndex->belongsTo;
		$this->assertEmpty($before);

		$result = $this->SearchIndex->searchModels($model);

		$after = $this->SearchIndex->belongsTo;
		$this->assertArrayHasKey($model, $after);
		$this->assertSame($model, $after[$model]['className']);
	}

	/**
	 * Confirm that fuzzyize can modify the supplied query.
	 *
	 * @return	void
	 * @dataProvider provideFuzzyize
	 */
	public function testFuzzyize($query, $expected) {
		$result = $this->SearchIndex->fuzzyize($query);
		$this->assertSame($expected, $result);
	}

	public function provideFuzzyize() {
		return [
			['foo', 'foo'],
			['foo bar', 'foo\s*bar'],
			['foo bar baz', 'foo\s*bar\s*baz'],
			['foobar baz', 'foobar\s*baz'],
		];
	}

	/**
	 * Confirm that if another model has not been bound to SearchIndex, the
	 * provided conditions are returned.
	 *
	 * @return void
	 */
	public function testBeforeFindUnbound() {
		$queryData = ['conditions' => ['foo' => 'bar']];
		$result = $this->SearchIndex->beforeFind($queryData);
		$this->assertSame($queryData, $result);
	}

	/**
	 * Confirm that after another model has been bound to SearchIndex and query
	 * conditions are supplied, the resulting query will be modified as
	 * expected.
	 *
	 * @return void
	 */
	public function testBeforeFindBound() {
		$model = 'Customer';
		$expected = [
			'conditions' => [
				'foo' => 'bar',
				 ['OR' => ['Customer.customers_id IS NOT NULL', ]],
			],
		];
		$this->SearchIndex->searchModels([$model]);
		$queryData = ['conditions' => ['foo' => 'bar']];
		$result = $this->SearchIndex->beforeFind($queryData);
		$this->assertSame($expected, $result);
	}

	/**
	 * Confirm that after another model has been bound to SearchIndex and query
	 * conditions are supplied as a string, the resulting query will be modified
	 * as expected.
	 *
	 * @return void
	 */
	public function testBeforeFindBoundWithStringConditions() {
		$model = 'Customer';
		$expected = [
			'conditions' => 'foobar AND (Customer.customers_id IS NOT NULL)',
		];
		$this->SearchIndex->searchModels([$model]);
		$queryData = ['conditions' => 'foobar'];
		$result = $this->SearchIndex->beforeFind($queryData);
		$this->assertSame($expected, $result);
	}

	/**
	 * Confirm that afterFind() will add the bound model's `displayField` to
	 * the returned search results.
	 *
	 * @return void
	 */
	public function testAfterFindNotPrimary() {
		$displayField = 'customers_email_address';
		$result = $this->SearchIndex->find('first');
		$this->assertSame($displayField, $result['SearchIndex']['displayField']);
	}

	/**
	 * Confirm the expected conditions are added to the query if none are
	 * supplied.
	 *
	 * @return void
	 */
	public function testBeforeFindBoundWithoutConditions() {
		$model = 'Customer';
		$expected = [
			'conditions' => [
				0 => [
					'OR' => [
						(int) 0 => 'Customer.customers_id IS NOT NULL'
					],
				],
			],
		];
		$this->SearchIndex->searchModels([$model]);
		$queryData = [];
		$result = $this->SearchIndex->beforeFind($queryData);
		$this->assertSame($expected, $result);
	}
}
