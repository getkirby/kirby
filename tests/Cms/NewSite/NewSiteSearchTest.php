<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class NewSiteSearchTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteSearchTest';

	public function testSearch(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => ['title' => 'Mountainbike']
				],
				[
					'slug'    => 'mountains',
					'content' => ['title' => 'Mountains']
				],
				[
					'slug'    => 'lakes',
					'content' => ['title' => 'Lakes']
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

	public function testSearchWords(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => ['title' => 'Mountainbike']
				],
				[
					'slug'    => 'mountain',
					'content' => ['title' => 'Mountain']
				],
				[
					'slug'    => 'everest-mountain',
					'content' => ['title' => 'Everest Mountain']
				],
				[
					'slug'    => 'mount',
					'content' => ['title' => 'Mount']
				],
				[
					'slug'    => 'lakes',
					'content' => ['title' => 'Lakes']
				]
			]
		]);

		$result = $site->search('mountain', ['words' => true]);
		$this->assertCount(2, $result);

		$result = $site->search('mount', ['words' => false]);
		$this->assertCount(4, $result);
	}
}
