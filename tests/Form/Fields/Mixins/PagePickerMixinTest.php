<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;

class PagePickerMixinTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.PagePickerMixin';

	public function setUp(): void
	{
		parent::setUp();

		// otherwise all pages won't be readable
		$this->app->impersonate('kirby');
	}

	public function testPagesWithoutParent()
	{
		$app = $this->app->clone([
			'fields' => [
				'test' => [
					'mixins'  => ['pagepicker'],
					'methods' => [
						'pages' => function () {
							return $this->pagepicker();
						}
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					['slug' => 'b'],
					['slug' => 'c'],
				],
				'content' => [
					'title' => 'Test'
				]
			]
		]);

		$app->impersonate('kirby');

		$field = $this->field('test', [
			'model' => $this->app->site()
		]);

		$response = $field->pages();
		$pages    = $response['data'];
		$model    = $response['model'];

		$this->assertSame('Test', $model['title']);
		$this->assertNull($model['id']);
		$this->assertNull($model['parent']);

		$this->assertCount(3, $pages);
		$this->assertSame('a', $pages[0]['id']);
		$this->assertSame('b', $pages[1]['id']);
		$this->assertSame('c', $pages[2]['id']);
	}

	public function testPagesWithParent()
	{
		$app = $this->app->clone([
			'fields' => [
				'test' => [
					'mixins'  => ['pagepicker'],
					'methods' => [
						'pages' => function () {
							return $this->pagepicker([
								'parent' => 'a'
							]);
						}
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							['slug' => 'aa']
						]
					],
					['slug' => 'b'],
					['slug' => 'c'],
				],
				'content' => [
					'title' => 'Test'
				]
			]
		]);

		$app->impersonate('kirby');

		$field = $this->field('test', [
			'model' => $this->app->site()
		]);

		$response = $field->pages();
		$pages    = $response['data'];
		$model    = $response['model'];

		$this->assertSame('a', $model['title']);
		$this->assertSame('a', $model['id']);
		$this->assertNull($model['parent']);

		$this->assertCount(1, $pages);
		$this->assertSame('a/aa', $pages[0]['id']);
	}

	public function testPageChildren()
	{
		$this->app->clone([
			'fields' => [
				'test' => [
					'mixins'  => ['pagepicker'],
					'methods' => [
						'pages' => function () {
							return $this->pagepicker([
								'query' => 'page.children'
							]);
						}
					]
				]
			],
		]);

		$page = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug' => 'a',
					'children' => [
						['slug' => 'aa'],
						['slug' => 'ab'],
						['slug' => 'ac'],
					]
				],
				['slug' => 'b'],
				['slug' => 'c'],
			]
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$response = $field->pages();
		$pages    = $response['data'];
		$model    = $response['model'];

		$this->assertCount(3, $model);
		$this->assertNull($model['id']);
		$this->assertNull($model['parent']);
		$this->assertSame('test', $model['title']);

		$this->assertCount(3, $pages);
		$this->assertSame('test/a', $pages[0]['id']);
		$this->assertSame('test/b', $pages[1]['id']);
		$this->assertSame('test/c', $pages[2]['id']);
	}

	public function testPageChildrenWithoutSubpages()
	{
		$this->app->clone([
			'fields' => [
				'test' => [
					'mixins'  => ['pagepicker'],
					'methods' => [
						'pages' => function () {
							return $this->pagepicker([
								'query'    => 'page.children',
								'subpages' => false
							]);
						}
					]
				]
			],
		]);

		$page = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug' => 'a',
					'children' => [
						['slug' => 'aa'],
						['slug' => 'ab'],
						['slug' => 'ac'],
					]
				],
				['slug' => 'b'],
				['slug' => 'c'],
			]
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$response = $field->pages();
		$pages    = $response['data'];
		$model    = $response['model'];

		$this->assertNull($model);
		$this->assertCount(3, $pages);
		$this->assertSame('test/a', $pages[0]['id']);
		$this->assertSame('test/b', $pages[1]['id']);
		$this->assertSame('test/c', $pages[2]['id']);
	}

	public function testMap()
	{
		$this->app->clone([
			'fields' => [
				'test' => [
					'mixins'  => ['pagepicker'],
					'methods' => [
						'pages' => function () {
							return $this->pagepicker([
								'query' => 'page.children',
								'map'   => function ($page) {
									return $page->id();
								}
							]);
						}
					]
				]
			],
		]);

		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
				['slug' => 'c'],
			]
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$response = $field->pages();
		$pages    = $response['data'];

		$this->assertSame(['test/a', 'test/b', 'test/c'], $pages);
	}
}
