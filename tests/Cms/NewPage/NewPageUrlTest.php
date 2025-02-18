<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageUrlTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageUrlTest';

	public function testHomeUrlInMultiLanguageMode()
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
		$this->assertSame('/en', $page->url('en'));
		$this->assertSame('/de', $page->url('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de', $page->url());
	}

	public function testHomeUrlInSingleLanguageMode()
	{	
		$page = new Page([
			'slug' => 'home'
		]);

		$this->assertSame('/', $page->url());
	}

	public function testHomeChildUrlInMultiLanguageMode()
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

		$this->assertSame('/en/home/child', $page->find('child')->url());
		$this->assertSame('/en/home/child', $page->find('child')->url('en'));
		$this->assertSame('/de/zuhause/kind', $page->find('child')->url('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/zuhause/kind', $page->find('child')->url());
	}

	public function testHomeChildUrlInSingleLanguageMode()
	{	
		$page = new Page([
			'slug'     => 'home',
			'children' => [
				[
					'slug' => 'child'
				]
			]
		]);

		$this->assertSame('/home/child', $page->find('child')->url());
	}

	public function testSetUrl()
	{
		$page = new Page([
			'slug' => 'test',
			'url'  => 'https://getkirby.com/test'
		]);

		$this->assertSame('https://getkirby.com/test', $page->url());
	}

	public function testSetUrlWithInvalidValue()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'url'  => []
		]);
	}

	public function testUrlInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$page = new Page(['slug' => 'test']);
		$this->assertSame('/en/test', $page->url());
		$this->assertSame('/en/test', $page->url('en'));
		$this->assertSame('/de/test', $page->url('de'));

		$this->app->setCurrentLanguage('de');

		$this->assertSame('/de/test', $page->url());
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

	public function testUrlWithOptionsInMultiLanguageMode()
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

	public function testUrlWithOptionsInSingleLanguageMode()
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
