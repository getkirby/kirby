<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPagePickerTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPagePicker';

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
}
