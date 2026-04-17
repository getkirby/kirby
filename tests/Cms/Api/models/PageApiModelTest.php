<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class PageApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageApiModel';

	public function testChildren(): void
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$model = $this->api->resolve($page)->select('children')->toArray();

		$this->assertSame('test/a', $model['children'][0]['id']);
		$this->assertSame('test/b', $model['children'][1]['id']);
	}

	public function testChildrenInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/children-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent',
						'children' => [
							['slug' => 'a', 'num' => 1],
							['slug' => 'b', 'num' => 2, 'template' => 'children-restricted'],
							['slug' => 'c', 'num' => 3],
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$page  = $this->app->page('parent');
		$model = $this->api->resolve($page)->select('children')->toArray();

		$childIds = array_column($model['children'], 'id');
		$this->assertContains('parent/a', $childIds);
		$this->assertNotContains('parent/b', $childIds);
		$this->assertContains('parent/c', $childIds);
	}

	public function testContent(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => $content = [
				'a' => 'A',
				'b' => 'B',
			]
		]);

		$this->assertAttr($page, 'content', $content);
	}

	public function testDrafts(): void
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$model = $this->api->resolve($page)->select('drafts')->toArray();

		$this->assertSame('test/a', $model['drafts'][0]['id']);
		$this->assertSame('test/b', $model['drafts'][1]['id']);
	}

	public function testDraftsInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/drafts-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'   => 'parent',
						'drafts' => [
							['slug' => 'a'],
							['slug' => 'b', 'template' => 'drafts-restricted'],
							['slug' => 'c'],
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$page  = $this->app->page('parent');
		$model = $this->api->resolve($page)->select('drafts')->toArray();

		$draftIds = array_column($model['drafts'], 'id');
		$this->assertContains('parent/a', $draftIds);
		$this->assertNotContains('parent/b', $draftIds);
		$this->assertContains('parent/c', $draftIds);
	}

	public function testFiles(): void
	{
		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$model = $this->api->resolve($page)->select('files')->toArray();

		$this->assertSame('a.jpg', $model['files'][0]['filename']);
		$this->assertSame('b.jpg', $model['files'][1]['filename']);
	}

	public function testHasDrafts(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertAttr($page, 'hasDrafts', false);

		$page = new Page([
			'slug' => 'test',
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$this->assertAttr($page, 'hasDrafts', true);
	}

	public function testHasChildren(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertAttr($page, 'hasChildren', false);

		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$this->assertAttr($page, 'hasChildren', true);
	}

	public function testId(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'id', 'test');
	}

	public function testIsSortable(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'isSortable', $page->isSortable());
	}

	public function testNum(): void
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 2
		]);

		$this->assertAttr($page, 'num', 2);
	}

	public function testParent(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/parent-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'accessible',
						'num'      => 1,
						'children' => [
							['slug' => 'child', 'num' => 1]
						]
					],
					[
						'slug'     => 'restricted',
						'num'      => 2,
						'template' => 'parent-restricted',
						'children' => [
							['slug' => 'child', 'num' => 1]
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$page  = $this->app->page('accessible/child');
		$model = $this->api->resolve($page)->select('parent')->toArray();
		$this->assertSame('accessible', $model['parent']['id']);

		$page  = $this->app->page('restricted/child');
		$model = $this->api->resolve($page)->select('parent')->toArray();
		$this->assertNull($model['parent']);
	}

	public function testParents(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/parents-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'grandparent',
						'num'      => 1,
						'children' => [
							[
								'slug'     => 'parent',
								'num'      => 1,
								'template' => 'parents-restricted',
								'children' => [
									['slug' => 'child', 'num' => 1]
								]
							]
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$page  = $this->app->page('grandparent/parent/child');
		$model = $this->api->resolve($page)->select('parents')->toArray();

		$parentIds = array_column($model['parents'], 'id');
		$this->assertContains('grandparent', $parentIds);
		$this->assertNotContains('grandparent/parent', $parentIds);
	}

	public function testSiblings(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/siblings-restricted' => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent',
						'children' => [
							['slug' => 'a', 'num' => 1],
							['slug' => 'b', 'num' => 2, 'template' => 'siblings-restricted'],
							['slug' => 'c', 'num' => 3],
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$page  = $this->app->page('parent/a');
		$model = $this->api->resolve($page)->select('siblings')->toArray();

		$siblingIds = array_column($model['siblings'], 'id');
		$this->assertContains('parent/a', $siblingIds);
		$this->assertNotContains('parent/b', $siblingIds);
		$this->assertContains('parent/c', $siblingIds);
	}

	public function testSiblingsForDraft(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/draft-siblings-restricted' => [
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent',
						'children' => [
							['slug' => 'a', 'num' => 1],
							['slug' => 'b', 'num' => 2, 'template' => 'draft-siblings-restricted'],
						],
						'drafts' => [
							['slug' => 'draft-test']
						]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$parent = $this->app->page('parent');
		$draft  = $parent->draft('draft-test');

		$model = $this->api->resolve($draft)->select('siblings')->toArray();

		$siblingIds = array_column($model['siblings'], 'id');
		$this->assertContains('parent/a', $siblingIds);
		$this->assertNotContains('parent/b', $siblingIds);
		$this->assertNotContains('parent/draft-test', $siblingIds);
	}

	public function testSlug(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'slug', 'test');
	}

	public function testStatus(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'status', 'unlisted');
	}

	public function testTemplate(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$this->assertAttr($page, 'template', 'test');
	}

	public function testTitle(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$this->assertAttr($page, 'title', 'Test');
	}

	public function testUrl(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertAttr($page, 'url', '/test');
	}
}
