<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageUrlTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageUrlTest';

	public function testUrlInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$page = new Page(['slug' => 'test']);
		$this->assertSame('/en/test', $page->url());
		$this->assertSame('/en/test', $page->url('en'));
		$this->assertSame('/de/test', $page->url('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/test', $page->url());
		$this->assertSame('/de/test', $page->url('de'));
	}

	public function testUrlWithNestedPagesInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$grandma = new Page(['slug' => 'grandma']);
		$mother  = new Page(['slug' => 'mother', 'parent' => $grandma]);
		$child   = new Page(['slug' => 'child', 'parent' => $mother]);

		$this->assertSame('/en/grandma', $grandma->url());
		$this->assertSame('/en/grandma/mother', $mother->url());
		$this->assertSame('/en/grandma/mother/child', $child->url());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/grandma', $grandma->url());
		$this->assertSame('/de/grandma/mother', $mother->url());
		$this->assertSame('/de/grandma/mother/child', $child->url());
	}

	public function testUrlWithTranslatedSlugInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$grandma = new Page([
			'slug'         => 'grandma',
			'translations' => [
				[
					'code' => 'en',
					'slug' => 'grandma'
				],
				[
					'code' => 'de',
					'slug' => 'oma'
				]
			]
		]);

		$mother = new Page([
			'parent'       => $grandma,
			'slug'         => 'mother',
			'translations' => [
				[
					'code' => 'en',
					'slug' => 'mother'
				],
				[
					'code' => 'de',
					'slug' => 'mutter'
				]
			]
		]);

		$child = new Page([
			'parent'       => $mother,
			'slug'         => 'child',
			'translations' => [
				[
					'code' => 'en',
					'slug' => 'child'
				],
				[
					'code' => 'de',
					'slug' => 'kind'
				]
			]
		]);

		$this->assertSame('/en/grandma', $grandma->url());
		$this->assertSame('/en/grandma/mother', $mother->url());
		$this->assertSame('/en/grandma/mother/child', $child->url());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/oma', $grandma->url());
		$this->assertSame('/de/oma/mutter', $mother->url());
		$this->assertSame('/de/oma/mutter/kind', $child->url());
	}

	public function testUrlInSingleLanguageMode()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('/test', $page->url());
	}

	public function testUrlWithNestedPagesInSingleLanguageMode()
	{
		$grandma = new Page(['slug' => 'grandma']);
		$mother  = new Page(['slug' => 'mother', 'parent' => $grandma]);
		$child   = new Page(['slug' => 'child', 'parent' => $mother]);

		$this->assertSame('/grandma', $grandma->url());
		$this->assertSame('/grandma/mother', $mother->url());
		$this->assertSame('/grandma/mother/child', $child->url());
	}
}
