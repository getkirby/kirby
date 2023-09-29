<?php

namespace Kirby\Cms;

class SiteChildrenTest extends TestCase
{
	public function testDefaultChildren()
	{
		$site = new Site();
		$this->assertInstanceOf(Pages::class, $site->children());
	}

	public function testInvalidChildren()
	{
		$this->expectException('TypeError');

		$site = new Site([
			'children' => 'children'
		]);
	}

	public function testPages()
	{
		$site = new Site([
			'children' => []
		]);

		$this->assertInstanceOf(Pages::class, $site->children());
	}

	public function testSearch()
	{
		$site = new Site([
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

		$result = $site->search('mountain');
		$this->assertCount(2, $result);

		$result = $site->search('mountain', 'title|text');
		$this->assertCount(2, $result);

		$result = $site->search('mountain', 'text');
		$this->assertCount(0, $result);
	}

	public function testSearchWords()
	{
		$site = new Site([
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

		$result = $site->search('mountain', ['words' => true]);
		$this->assertCount(2, $result);

		$result = $site->search('mount', ['words' => false]);
		$this->assertCount(4, $result);
	}
}
