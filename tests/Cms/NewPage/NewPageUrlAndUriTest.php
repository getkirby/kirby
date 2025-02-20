<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageUrlAndUriTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageUrlTest';

	public function testHomeUrlAndUriInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'home',
			'translations' => [
				[
					'code' => 'de',
					// Should not have an effect on the
					// base url. Only used for child urls.
					'slug' => 'zuhause'
				]
			]
		]);

		$this->assertSame('/en', $page->url());
		$this->assertSame('home', $page->uri());

		$this->assertSame('/en', $page->url('en'));
		$this->assertSame('/de', $page->url('de'));

		$this->assertSame('home', $page->uri('en'));
		$this->assertSame('zuhause', $page->uri('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de', $page->url());
		$this->assertSame('zuhause', $page->uri());
	}

	public function testHomeUrlAndUriInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'home'
		]);

		$this->assertSame('/', $page->url());
		$this->assertSame('home', $page->uri());
	}

	public function testHomeChildUrlAndUriInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'home',
			'translations' => [
				[
					'code' => 'de',
					'slug' => 'zuhause'
				]
			],
			'children' => [
				[
					'slug' => 'child',
					'translations' => [
						[
							'code' => 'de',
							'slug' => 'kind'
						]
					]
				]
			]
		]);

		$child = $page->find('child');

		$this->assertSame('/en/home/child', $child->url());
		$this->assertSame('home/child', $child->uri());

		$this->assertSame('/en/home/child', $child->url('en'));
		$this->assertSame('/de/zuhause/kind', $child->url('de'));

		$this->assertSame('home/child', $child->uri('en'));
		$this->assertSame('zuhause/kind', $child->uri('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/zuhause/kind', $child->url());
		$this->assertSame('zuhause/kind', $child->uri());
	}

	public function testHomeChildUrlAndUriInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug'     => 'home',
			'children' => [
				[
					'slug' => 'child'
				]
			]
		]);

		$child = $page->find('child');

		$this->assertSame('/home/child', $child->url());
		$this->assertSame('home/child', $child->uri());
	}

	public function testSetUrl(): void
	{
		$page = new Page([
			'slug' => 'test',
			'url'  => 'https://getkirby.com/test'
		]);

		$this->assertSame('https://getkirby.com/test', $page->url());
		$this->assertSame('test', $page->uri());
	}

	public function testSetUrlWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'url'  => []
		]);
	}

	public function testUrlAndUriInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page(['slug' => 'test']);
		$this->assertSame('/en/test', $page->url());
		$this->assertSame('test', $page->uri());

		$this->assertSame('/en/test', $page->url('en'));
		$this->assertSame('/de/test', $page->url('de'));

		$this->assertSame('test', $page->uri('en'));
		$this->assertSame('test', $page->uri('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/test', $page->url());
		$this->assertSame('test', $page->uri());
	}

	public function testUrlAndUriWithNestedPagesInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$grandma = new Page(['slug' => 'grandma']);
		$mother  = new Page(['slug' => 'mother', 'parent' => $grandma]);
		$child   = new Page(['slug' => 'child', 'parent' => $mother]);

		$this->assertSame('/en/grandma', $grandma->url());
		$this->assertSame('/en/grandma/mother', $mother->url());
		$this->assertSame('/en/grandma/mother/child', $child->url());

		$this->assertSame('grandma', $grandma->uri());
		$this->assertSame('grandma/mother', $mother->uri());
		$this->assertSame('grandma/mother/child', $child->uri());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/grandma', $grandma->url());
		$this->assertSame('/de/grandma/mother', $mother->url());
		$this->assertSame('/de/grandma/mother/child', $child->url());

		$this->assertSame('grandma', $grandma->uri());
		$this->assertSame('grandma/mother', $mother->uri());
		$this->assertSame('grandma/mother/child', $child->uri());
	}

	public function testUrlWithOptionsInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertSame('/en/test/foo:bar?q=search', $page->url([
			'params' => 'foo:bar',
			'query'  => 'q=search'
		]));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/test/foo:bar?q=search', $page->url([
			'params' => 'foo:bar',
			'query'  => 'q=search'
		]));
	}

	public function testUrlAndUriWithTranslatedSlugInMultiLanguageMode(): void
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

		$this->assertSame('grandma', $grandma->uri());
		$this->assertSame('grandma/mother', $mother->uri());
		$this->assertSame('grandma/mother/child', $child->uri());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/oma', $grandma->url());
		$this->assertSame('/de/oma/mutter', $mother->url());
		$this->assertSame('/de/oma/mutter/kind', $child->url());

		$this->assertSame('oma', $grandma->uri());
		$this->assertSame('oma/mutter', $mother->uri());
		$this->assertSame('oma/mutter/kind', $child->uri());
	}

	public function testUrlAndUriInSingleLanguageMode(): void
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('/test', $page->url());
		$this->assertSame('test', $page->uri());
	}

	public function testUrlAndUriWithNestedPagesInSingleLanguageMode(): void
	{
		$grandma = new Page(['slug' => 'grandma']);
		$mother  = new Page(['slug' => 'mother', 'parent' => $grandma]);
		$child   = new Page(['slug' => 'child', 'parent' => $mother]);

		$this->assertSame('/grandma', $grandma->url());
		$this->assertSame('/grandma/mother', $mother->url());
		$this->assertSame('/grandma/mother/child', $child->url());

		$this->assertSame('grandma', $grandma->uri());
		$this->assertSame('grandma/mother', $mother->uri());
		$this->assertSame('grandma/mother/child', $child->uri());
	}

	public function testUrlWithOptionsInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertSame('/test/foo:bar?q=search', $page->url([
			'params' => 'foo:bar',
			'query'  => 'q=search'
		]));
	}
}
