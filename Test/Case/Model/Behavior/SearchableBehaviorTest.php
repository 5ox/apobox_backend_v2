<?php
App::uses('SearchableBehavior', 'Model/Behavior');

/**
 * SearchableBehavior Test Case
 *
 */
class SearchableBehaviorTest extends CakeTestCase {

	/**
	 * fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.search_index',
	];

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
		$this->testModel = 'Customer';
		$this->{$this->testModel} = $this->getMockForModel($this->testModel, ['indexData', 'delete']);
		$this->Searchable = new SearchableBehavior();
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		unset($this->Searchable);

		parent::tearDown();
	}

	/**
	 * Confirm the default settings can be properly set.
	 *
	 * @return	void
	 */
	public function testSetupDefaults() {
		$this->Searchable->setup($this->{$this->testModel});
		$this->assertSame(
			$this->Searchable->defaultSettings,
			$this->Searchable->settings[$this->testModel],
			'Should have matching default settings arrays'
		);
	}

	/**
	 * Confirm the default settings can be properly merged with optional
	 * configuration settings.
	 *
	 * @return void
	 */
	public function testSetupDefaultsWithConfig() {
		$this->assertTrue($this->Searchable->defaultSettings['rebuildOnUpdate']);
		$this->assertFalse($this->Searchable->defaultSettings['foreignKey']);
		$this->Searchable->setup($this->{$this->testModel}, ['rebuildOnUpdate' => false]);
		$this->assertFalse($this->Searchable->settings[$this->testModel]['rebuildOnUpdate']);
		$this->assertFalse($this->Searchable->defaultSettings['foreignKey']);
	}

	/**
	 * Confirm that the processData method calls the model's indexData method.
	 *
	 * @return void
	 */
	public function testProcessData() {
		$this->{$this->testModel}->expects($this->once())
			->method('indexData')
			->will($this->returnValue(true));
		$this->Searchable->processData($this->{$this->testModel});
	}

	/**
	 * Confirm beforeSave will set the settings foreignKey value to $Model->id
	 * if one is set.
	 *
	 * @return void
	 */
	public function testBeforeSaveWithModelId() {
		$id = 9;
		$this->{$this->testModel}->id = $id;
		$this->Searchable->setup($this->{$this->testModel});
		$this->assertFalse($this->Searchable->settings[$this->testModel]['foreignKey']);
		$this->Searchable->beforeSave($this->{$this->testModel});
		$this->assertSame(
			$id,
			$this->Searchable->settings[$this->testModel]['foreignKey'],
			'Should set foreignKey to $id'
		);
	}

	/**
	 * Confirm beforeSave will set the settings foreignKey value to `0` if a
	 * $Model->id is not set.
	 *
	 * @return void
	 */
	public function testBeforeSaveWithoutModelId() {
		$this->Searchable->setup($this->{$this->testModel});
		$this->assertFalse($this->Searchable->settings[$this->testModel]['foreignKey']);
		$this->Searchable->beforeSave($this->{$this->testModel});
		$this->assertSame(
			0,
			$this->Searchable->settings[$this->testModel]['foreignKey'],
			'Should set foreignKey to `0`'
		);
	}

	/**
	 * Confirm beforeSave calls the supplied model's indexData() method when
	 * `foreignKey` is 0 and `rebuildOnUpdate` is true.
	 *
	 * @return void
	 */
	public function testBeforeSaveProcessData() {
		$this->{$this->testModel}->expects($this->once())
			->method('indexData')
			->will($this->returnValue(true));

		$this->Searchable->setup($this->{$this->testModel});
		$result = $this->Searchable->beforeSave($this->{$this->testModel});
		$this->assertTrue($result);
	}

	/**
	 * Confirm afterSave adds a SearchIndex record.
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$SearchIndex = ClassRegistry::init('SearchIndex');
		$countBefore = $SearchIndex->find('count');

		$this->Searchable->setup($this->{$this->testModel}, ['foreignKey' => 9, '_index' => 'foo. bar']);
		$result = $this->Searchable->afterSave($this->{$this->testModel}, true);

		$countAfter = $SearchIndex->find('count');

		$this->assertSame(
			$countAfter,
			$countBefore + 1,
			'Should add a record and increase the count.'
		);
		$this->assertTrue($result);
	}

	/**
	 * Confirm that when the model adding search data's foreign key is `0`
	 * the expected search data is saved and the method returns bool true.
	 *
	 * @return void
	 */
	public function testAfterSaveForeignKeyIsZero() {
		$key = 0;
		$Behavior = $this->getMockBuilder('SearchableBehavior')
			->setMethods(null)
			->getMock();
		$Model = $this->getMockForModel('Customer', ['getLastInsertID']);
		$SearchIndex = $this->getMockBuilder('SearchIndex')
			->setMethods(['create', 'save'])
			->getMock();

		$Behavior->setup($Model, ['_index' => true, 'foreignKey' => $key]);

		$saveData = ['SearchIndex' => [
			'model' => $Model->alias,
			'association_key' => $Behavior->settings[$Model->alias]['foreignKey'],
			'data' => $Behavior->settings[$Model->alias]['_index'],
		]];

		$Behavior->SearchIndex = $SearchIndex;
		$Model->expects($this->once())
			->method('getLastInsertID')
			->will($this->returnValue($key));
		$Behavior->SearchIndex->expects($this->once())
			->method('create');
		$Behavior->SearchIndex->expects($this->once())
			->method('save')
			->with($this->identicalTo($saveData));

		$result = $Behavior->afterSave($Model, true);

		$this->assertTrue($result);
	}

	/**
	 * Confirm afterDelete will call the SearchIndex deleteAll() method.
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$SearchIndex = $this->getMockForModel('SearchIndex', ['deleteAll']);

		$SearchIndex->expects($this->once())
			->method('deleteAll')
			->will($this->returnValue(true));
		$this->Searchable->setup($this->{$this->testModel});
		$result = $this->Searchable->afterDelete($this->{$this->testModel});
	}
}
