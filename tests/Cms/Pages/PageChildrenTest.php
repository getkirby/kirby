<?php

namespace Kirby\Cms;

class PageChildrenTest extends TestCase
{
	public function testDefaultChildren()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertInstanceOf(Pages::class, $page->children());
		$this->assertCount(0, $page->children());
	}

	public function testGrandChildren()
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

	public function testHasChildren()
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

	public function testHasNoChildren()
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => []
		]);

		$this->assertFalse($page->hasChildren());
	}

	public function testHasListedChildren()
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a', 'num' => 1]
			]
		]);

		$this->assertTrue($page->hasListedChildren());
	}

	public function testHasNoListedChildren()
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->assertFalse($page->hasListedChildren());
	}

	public function testHasUnlistedChildren()
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a']
			]
		]);

		$this->assertTrue($page->hasUnlistedChildren());
	}

	public function testHasNoUnlistedChildren()
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a', 'num' => 1]
			]
		]);

		$this->assertFalse($page->hasUnlistedChildren());
	}

	public function testHasDrafts()
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

	public function testHasNoDrafts()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFalse($page->hasDrafts());
	}

	public function testSearch()
	{
		$page = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => [
						'title' => 'Mountainbike'
					]
				],
				[
					'slug'    => 'mountains',
					'content' => [
						'title' => 'Mountains'
					]
				],
				[
					'slug'    => 'lakes',
					'content' => [
						'title' => 'Lakes'
					]
				]
			]
		]);

		$result = $page->search('mountain');
		$this->assertCount(2, $result);

		$result = $page->search('mountain', 'title|text');
		$this->assertCount(2, $result);

		$result = $page->search('mountain', 'text');
		$this->assertCount(0, $result);
	}

	public function testSearchWords()
	{
		$page = new Page([
			'slug' => 'test',
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => [
						'title' => 'Mountainbike'
					]
				],
				[
					'slug'    => 'mountain',
					'content' => [
						'title' => 'Mountain'
					]
				],
				[
					'slug'    => 'everest-mountain',
					'content' => [
						'title' => 'Everest Mountain'
					]
				],
				[
					'slug'    => 'mount',
					'content' => [
						'title' => 'Mount'
					]
				],
				[
					'slug'    => 'lakes',
					'content' => [
						'title' => 'Lakes'
					]
				]
			]
		]);

		$result = $page->search('mountain', ['words' => true]);
		$this->assertCount(2, $result);

		$result = $page->search('mount', ['words' => false]);
		$this->assertCount(4, $result);
	}
}
