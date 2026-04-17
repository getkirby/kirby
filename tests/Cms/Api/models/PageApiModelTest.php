<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class PageApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageApiModel';

	public function testChildren(): void
	{
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

	public function testSiblings(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'pages/siblings-restricted' => [
					'options' => ['access' => false]
				]
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
				['id' => 'test', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test');
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
				['id' => 'test', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test');
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
