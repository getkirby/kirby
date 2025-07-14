<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageSearchTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageSearch';

	public function testSearch(): void
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

	public function testSearchWords(): void
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
