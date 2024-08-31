<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class SortMixinTest extends TestCase
{
	protected Page $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->page = new Page(['slug' => 'test']);

		Section::$types['test'] = [
			'mixins' => ['sort'],
			'props'  => [
				'query' => fn (string|null $query = null) => $query
			]
		];

		Section::$types['pages'] = [
			'mixins' => ['sort'],
			'props'  => [
				'status' => fn (string|null $status = null) => $status
			]
		];
	}

	public function testFlip()
	{
		// default
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertFalse($section->flip());

		// activated
		$section = new Section('test', [
			'model' => $this->page,
			'flip'  => true
		]);

		$this->assertTrue($section->flip());

		// deactivated
		$section = new Section('test', [
			'model' => $this->page,
			'flip'  => false
		]);

		$this->assertFalse($section->flip());
	}

	public function testSortable()
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertTrue($section->sortable());
	}

	public function testSortableWhileFlipped()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'flip'  => true
		]);

		$this->assertFalse($section->sortable());
	}

	public function testSortableWhileSearching()
	{
		$section = new Section('test', [
			'model'      => $this->page,
			'searchterm' => 'searching …'
		]);

		$this->assertFalse($section->sortable());
	}

	public function testSortableWhileSorted()
	{
		$section = new Section('test', [
			'model'  => $this->page,
			'sortBy' => 'title desc'
		]);

		$this->assertFalse($section->sortable());
	}

	public function testSortableWithUnsortableStatus()
	{
		$section = new Section('pages', [
			'model'  => $this->page,
			'status' => 'draft'
		]);

		$this->assertFalse($section->sortable());
	}

	public function testSortBy()
	{
		// default
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertNull($section->sortBy());

		// custom
		$section = new Section('test', [
			'model'  => $this->page,
			'sortBy' => 'title desc'
		]);

		$this->assertSame('title desc', $section->sortBy());
	}
}
