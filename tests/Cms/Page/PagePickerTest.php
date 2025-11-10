<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PagePickerTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PagePicker';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'grandmother',
						'children' => [
							[
								'slug' => 'mother',
								'children' => [
									['slug' => 'child-a', 'template' => 'child-a'],
									['slug' => 'child-b', 'template' => 'child-b'],
									['slug' => 'child-c', 'template' => 'child-c']
								]
							]
						]
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults(): void
	{
		$picker = new PagePicker();

		$this->assertSame($this->app->site(), $picker->model());
		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother', $picker->items()->first()->id());
	}

	public function testParent(): void
	{
		$picker = new PagePicker([
			'parent' => 'grandmother'
		]);

		$this->assertCount(1, $picker->items());
		$this->assertSame('grandmother/mother', $picker->items()->first()->id());
		$this->assertSame('grandmother', $picker->model()->id());
	}

	public function testParentStart(): void
	{
		$picker = new PagePicker([
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame($picker->start(), $this->app->site());
	}

	public function testQuery(): void
	{
		$picker = new PagePicker([
			'query' => 'site.find("grandmother/mother").children'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryAndParent(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(3, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}

	public function testQueryStart(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother").children',
			'parent' => 'grandmother/mother'
		]);

		$this->assertSame('grandmother', $picker->start()->id());
	}

	public function testSameQueryAndParent(): void
	{
		$picker = new PagePicker([
			'query'  => 'site.find("grandmother/mother").children.filterBy("intendedTemplate", "in", ["child-a", "child-c"])',
			'parent' => 'grandmother/mother'
		]);

		$this->assertCount(2, $picker->items());
		$this->assertSame('grandmother/mother/child-a', $picker->items()->first()->id());
		$this->assertSame('grandmother/mother/child-c', $picker->items()->last()->id());
	}
}
