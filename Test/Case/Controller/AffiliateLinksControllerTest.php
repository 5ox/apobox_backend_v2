<?php
App::uses('AffiliateLinksController', 'Controller');

/**
 * AffiliateLinksController Test Case
 */
class AffiliateLinksControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'app.affiliate_link'
	);

	/**
	 * setUp method
	 */
	public function setUp() {
		parent::setUp();
		$this->AffiliateLink = ClassRegistry::init('AffiliateLink');
	}

	/**
	 * testManagerIndex method
	 *
	 * @return void
	 */
	public function testManagerIndex() {
		$this->testAction('/manager/affiliate-links', ['return' => 'vars']);
		$this->assertNotEmpty($this->vars['affiliateLinks']);
		$this->assertTrue(is_array($this->vars['affiliateLinks']));
	}

	/**
	 * testManagerAdd method
	 *
	 * @return void
	 */
	public function testManagerAdd() {
		$AffiliateLinks = $this->generate('AffiliateLinks', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$AffiliateLinks->Auth->staticExpects($this->any())
			->method('user')
			->will($this->returnValue('manager'));

		$this->testAction('/manager/affiliate-links/add', ['method' => 'get']);
		$this->assertEquals(200, $AffiliateLinks->response->statusCode());
	}

	/**
	 * testManagerAdd method post
	 *
	 * @return void
	 */
	public function testManagerAddPost() {
		$data['AffiliateLink'] = [
			'title' => 'Foo Bar',
			'url' => 'http://foobar.com',
			'enabled' => 1,
		];

		$countBefore = $this->AffiliateLink->find('count');
		$this->testAction('/manager/affiliate-links/add', ['method' => 'post', 'data' => $data]);

		$countAfter = $this->AffiliateLink->find('count');
		$this->assertEqual(++$countBefore, $countAfter);
	}

	/**
	 * testManagerAdd method post fail
	 *
	 * @return void
	 */
	public function testManagerAddPostFail() {
        $AffiliateLinks = $this->generate('AffiliateLinks', [
            'models' => [
                'AffiliateLink' => ['save']
            ],
            'components' => [
                'Flash' => ['error']
            ],
        ]);
        $AffiliateLinks->AffiliateLink->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        $AffiliateLinks->Flash->expects($this->once())
            ->method('error')
            ->will($this->returnValue($this->any()));

		$data['AffiliateLink'] = [
			'title' => 'Foo Bar',
			'url' => 'http://foobar.com',
			'enabled' => 1,
		];

		$countBefore = $this->AffiliateLink->find('count');
		$this->testAction('/manager/affiliate-links/add', ['method' => 'post', 'data' => $data]);

		$countAfter = $this->AffiliateLink->find('count');
		$this->assertEqual($countBefore, $countAfter);
	}

	/**
	 * testManagerEdit method
	 *
	 * @return void
	 */
	public function testManagerEdit() {
		$AffiliateLinks = $this->generate('AffiliateLinks', [
			'components' => [
				'Auth' => ['user'],
			],
		]);
		$AffiliateLinks->Auth->staticExpects($this->any())
			->method('user')
			->will($this->returnValue('manager'));

		$this->testAction('/manager/affiliate-links/edit/1', ['method' => 'get']);
		$this->assertEquals(200, $AffiliateLinks->response->statusCode());
	}

	/**
	 * testManagerEdit method not found
	 *
	 * @expectedException NotFoundException
	 * @return void
	 */
	public function testManagerEditNotFound() {
		$this->testAction('/manager/affiliate-links/edit/999', ['method' => 'get']);
	}

	/**
	 * testManagerEdit method post
	 *
	 * @return void
	 */
	public function testManagerEditPost() {
		$id = '1';
		$title = 'Foo Bar';
		$data['AffiliateLink'] = [
			'id' => $id,
			'title' => $title,
			'url' => 'http://foobar.com',
			'enabled' => '1',
		];
		$this->testAction('/manager/affiliate-links/edit/' . $id, ['method' => 'post', 'data' => $data]);

		$affiliateLink = $this->AffiliateLink->findById($id);
		$this->assertEqual($title, $affiliateLink['AffiliateLink']['title']);
	}

	/**
	 * testManagerEdit method post fail
	 *
	 * @return void
	 */
	public function testManagerEditPostFail() {
        $AffiliateLinks = $this->generate('AffiliateLinks', [
            'models' => [
                'AffiliateLink' => ['save']
            ],
            'components' => [
                'Flash' => ['error']
            ],
        ]);
        $AffiliateLinks->AffiliateLink->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        $AffiliateLinks->Flash->expects($this->once())
            ->method('error')
            ->will($this->returnValue($this->any()));

		$id = '1';
		$title = 'Foo Bar';
		$data['AffiliateLink'] = [
			'id' => $id,
			'title' => $title,
			'url' => 'http://foobar.com',
			'enabled' => '1',
		];
		$affiliateLinkBefore = $this->AffiliateLink->findById($id);

		$this->testAction('/manager/affiliate-links/edit/' . $id, ['method' => 'post', 'data' => $data]);

		$affiliateLinkAfter = $this->AffiliateLink->findById($id);
		$this->assertEqual($affiliateLinkBefore, $affiliateLinkAfter);
	}

	/**
	 * testManagerDelete method
	 *
	 * @return void
	 */
	public function testManagerDelete() {
		$id = '1';
		$affliliateLink = $this->AffiliateLink->findById($id);
		$this->assertNotEmpty($affliliateLink);
        $this->testAction('/manager/affiliate-links/delete/' . $id, ['method' => 'post']);
        $landing_categories = $this->AffiliateLink->find('all', [
            'recursive' => -1,
        ]);
		$affliliateLink = $this->AffiliateLink->findById($id);
		$this->assertEmpty($affliliateLink);
	}

    /**
	 * Test exception is thrown on GET request for delete method
	 *
     * @expectedException MethodNotAllowedException
	 * @return void
     */
	public function testManagerDeleteGet() {
        $this->testAction('/manager/affiliate-links/delete/1', array('method' => 'get'));
	}

	/**
	 * Test failed delete
	 *
	 * @return void
	 */
	public function testManagerDeleteFail() {
		$id = '1';
		$AffiliateLinks = $this->generate('AffiliateLinks', [
			'models' => [
				'AffiliateLink' => ['delete'],
			],
			'components' => [
				'Flash' => ['error'],
			],
		]);
		$AffiliateLinks->AffiliateLink->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));
		$AffiliateLinks->Flash->expects($this->once())
			->method('error')
			->will($this->returnValue($this->any()));
		$this->testAction('/manager/affiliate-links/delete/' . $id, ['method' => 'post']);
		$affiliateLink = $this->AffiliateLink->findById($id);
		$this->assertNotEmpty($affiliateLink);
	}
}
