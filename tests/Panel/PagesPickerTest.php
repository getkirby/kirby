<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\PagesPicker
 */
class PagesPickerTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'grandmother',
						'children' => [
							[
								'slug' => 'mother',
								'children' => [
									['slug' => 'child-a'],
									['slug' => 'child-b'],
									['slug' => 'child-c']
								]
							]
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$picker = new PagesPicker();

		$this->assertSame($this->app->site(), $picker->model());
		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother', $picker->items()->first()->id());
	}

	/**
	 * @covers ::model
	 * @covers ::modelForQuery
	 */
	public function testModel()
	{
		$picker = new PagesPicker(['subpages' => false]);
		$this->assertNull($picker->model());

		$picker = new PagesPicker(['parent' => 'grandmother']);
		$this->assertSame('grandmother', $picker->model()->id());

		$picker = new PagesPicker(['query' => 'site.find("grandmother").children']);
		$this->assertSame('grandmother', $picker->model()->id());
	}

	/**
	 * @covers ::parent
	 */
	public function testParent()
	{
		$picker = new PagesPicker(['parent' => 'grandmother']);

		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother/mother', $picker->items()->first()->id());
		$this->assertSame('grandmother', $picker->model()->id());
	}

	/**
	 * @covers ::start
	 */
	public function testParentStart()
	{
		$picker = new PagesPicker([
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame($picker->start(), $this->app->site());
	}

	public function testQuery()
	{
		$picker = new PagesPicker([
			'query' => 'site.find("grandmother/mother").children'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryAndParent()
	{
		$picker = new PagesPicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryStart()
	{
		$picker = new PagesPicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame('grandmother', $picker->start()->id());
	}
}
