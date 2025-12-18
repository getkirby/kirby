<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class BatchSectionMixinTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.BatchSectionMixin';

	protected Page $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'translations' => [
				'en' => [
					'error.section.test.min.plural' => 'The section requires at least {min} items',
				]
			]
		]);

		$this->page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
				['slug' => 'c']
			]
		]);

		Dir::make($this->page->root() . '/a');
		Dir::make($this->page->root() . '/b');
		Dir::make($this->page->root() . '/c');

		Section::$types['test'] = [
			'mixins' => ['batch'],
			'props' => [
				'min' => function (int $min = 0) {
					return $min;
				}
			],
			'computed' => [
				'models' => function () {
					return $this->model()->children();
				},
				'total' => function () {
					return $this->models()->count();
				},
			]
		];

		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
	}

	public function testBatchDisabled(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertFalse($section->batch());
	}

	public function testBatchEnabled(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'batch' => true
		]);

		$this->assertTrue($section->batch());
	}

	public function testDeleteSelectedWithoutIds(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'batch' => true
		]);

		$this->assertTrue($section->deleteSelected([]));
	}

	public function testDeleteSelectedWhenDisabled(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'batch' => false
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The section does not support batch actions');

		$section->deleteSelected(['test/a', 'test/b']);
	}

	public function testDeleteSelectedWhenExceedingMin(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'batch' => true,
			'min'   => 2
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The section requires at least 2 items');

		$section->deleteSelected([
			'test/a', 'test/b', 'test/c'
		]);
	}

	public function testDeleteSelected(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'batch' => true,
		]);

		$this->app->impersonate('kirby');

		$this->assertTrue($section->deleteSelected([
			'test/a', 'test/b', 'test/c'
		]));
	}
}
