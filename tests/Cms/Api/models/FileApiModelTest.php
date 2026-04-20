<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class FileApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileApiModel';

	public function testNext(): void
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$next = $this->attr($page->file('a.jpg'), 'next');
		$this->assertSame('b.jpg', $next['filename']);
	}

	public function testNextInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'files/next-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'num'   => 1,
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg', 'template' => 'next-restricted'],
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

		$page = $this->app->page('test');
		$next = $this->attr($page->file('a.jpg'), 'next');
		$this->assertNull($next);
	}

	public function testNextWithTemplate()
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg', 'content' => ['template' => 'test']],
				['filename' => 'b.jpg', 'content' => ['template' => 'test']],
			]
		]);

		$next = $this->attr($page->file('a.jpg'), 'nextWithTemplate');
		$this->assertSame('b.jpg', $next['filename']);
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
						'slug'  => 'accessible',
						'num'   => 1,
						'files' => [['filename' => 'test.jpg']]
					],
					[
						'slug'     => 'restricted',
						'num'      => 2,
						'template' => 'parent-restricted',
						'files'    => [['filename' => 'test.jpg']]
					]
				]
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor');
		$this->api = $this->app->api();

		$file  = $this->app->page('accessible')->file('test.jpg');
		$model = $this->api->resolve($file)->select('parent')->toArray();
		$this->assertSame('accessible', $model['parent']['id']);

		$file  = $this->app->page('restricted')->file('test.jpg');
		$model = $this->api->resolve($file)->select('parent')->toArray();
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
								'files'    => [['filename' => 'test.jpg']]
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

		$file  = $this->app->page('grandparent/parent')->file('test.jpg');
		$model = $this->api->resolve($file)->select('parents')->toArray();

		$parentIds = array_column($model['parents'], 'id');
		$this->assertContains('grandparent', $parentIds);
		$this->assertNotContains('grandparent/parent', $parentIds);
	}

	public function testPrev(): void
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
			]
		]);

		$prev = $this->attr($page->file('b.jpg'), 'prev');
		$this->assertSame('a.jpg', $prev['filename']);
	}

	public function testPrevInaccessible(): void
	{
		$this->app = new App([
			'roots' => ['index' => static::TMP],
			'blueprints' => [
				'files/prev-restricted' => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor']
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'num'   => 1,
						'files' => [
							['filename' => 'a.jpg', 'template' => 'prev-restricted'],
							['filename' => 'b.jpg'],
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

		$page = $this->app->page('test');
		$prev = $this->attr($page->file('b.jpg'), 'prev');
		$this->assertNull($prev);
	}

	public function testPrevWithTemplate()
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg', 'content' => ['template' => 'test']],
				['filename' => 'b.jpg', 'content' => ['template' => 'test']],
			]
		]);

		$prev = $this->attr($page->file('b.jpg'), 'prevWithTemplate');
		$this->assertSame('a.jpg', $prev['filename']);
	}
}
