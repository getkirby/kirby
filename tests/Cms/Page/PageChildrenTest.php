<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChildrenTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChildren';

	public function testChildren(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(Pages::class, $page->children());
		$this->assertCount(0, $page->children());
	}

	public function testGrandChildren(): void
	{
		$page = new Page([
			'slug' => 'grandma',
			'children' => [
				[
					'slug' => 'mother',
					'children' => [
						['slug' => 'child']
					]
				]
			]
		]);

		$this->assertCount(1, $page->grandChildren());
		$this->assertSame('child', $page->grandChildren()->first()->slug());
	}

	public function testHasChildren(): void
	{
		$page = new Page([
			'slug' => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b']
			]
		]);

		$this->assertTrue($page->hasChildren());
	}

	public function testHasDrafts(): void
	{
		$page = new Page([
			'slug' => 'test',
			'drafts' => [
				['slug' => 'a'],
				['slug' => 'b']
			]
		]);

		$this->assertTrue($page->hasDrafts());
	}


	public function testHasListedChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'a',
					'num'  => 1
				]
			]
		]);

		$this->assertTrue($page->hasListedChildren());
	}

	public function testHasNoChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => []
		]);

		$this->assertFalse($page->hasChildren());
	}

	public function testHasNoDrafts(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFalse($page->hasDrafts());
	}

	public function testHasNoListedChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->assertFalse($page->hasListedChildren());
	}

	public function testHasNoUnlistedChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'a',
					'num'  => 1
				]
			]
		]);

		$this->assertFalse($page->hasUnlistedChildren());
	}

	public function testHasUnlistedChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->assertTrue($page->hasUnlistedChildren());
	}
}
