<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SiteTranslationsTest extends TestCase
{
	public function app($language = null)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			],
			'site' => [
				'translations' => [
					[
						'code' => 'en',
						'content' => [
							'title' => 'Site',
							'untranslated' => 'Untranslated'
						]
					],
					[
						'code' => 'de',
						'content' => [
							'title' => 'Seite',
						]
					],
				]
			]
		]);

		if ($language !== null) {
			$app->setCurrentLanguage($language);
			$app->setCurrentTranslation($language);
		}

		return $app;
	}

	public function site()
	{
		return $this->app()->site();
	}

	public function testUrl()
	{
		$site = $this->site();

		$this->assertSame('/en', $site->url());
		$this->assertSame('/de', $site->url('de'));

		// non-existing language
		$this->assertSame('/', $site->url('fr'));
	}

	public function testContentInEnglish()
	{
		$site = $this->site();
		$this->assertSame('Site', $site->title()->value());
		$this->assertSame('Untranslated', $site->untranslated()->value());
	}

	public function testContentInDeutsch()
	{
		$site = $this->app('de')->site();
		$this->assertSame('Seite', $site->title()->value());
		$this->assertSame('Untranslated', $site->untranslated()->value());
	}

	public function testTranslations()
	{
		$site = $this->site();
		$this->assertCount(2, $site->translations());
		$this->assertSame(['en', 'de'], $site->translations()->keys());
	}

	public static function visitProvider(): array
	{
		return [
			['en', 'Site', 'English Test'],
			['de', 'Seite', 'Deutsch Test']
		];
	}

	#[DataProvider('visitProvider')]
	public function testVisit($languageCode, $siteTitle, $pageTitle)
	{
		$app = $this->app()->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'translations' => [
							[
								'code' => 'en',
								'content' => [
									'title' => 'English Test'
								],
							],
							[
								'code' => 'de',
								'content' => [
									'title' => 'Deutsch Test'
								],
							]
						]
					]
				],
				'translations' => [
					[
						'code' => 'en',
						'content' => [
							'title' => 'Site',
							'untranslated' => 'Untranslated'
						]
					],
					[
						'code' => 'de',
						'content' => [
							'title' => 'Seite',
						]
					],
				]
			]
		]);

		$site = $app->site();
		$page = $site->visit('test', $languageCode);

		$this->assertSame($languageCode, $app->language()->code());
		$this->assertSame('test', $page->slug());
		$this->assertSame($siteTitle, $site->title()->value());
		$this->assertSame($pageTitle, $page->title()->value());
	}
}
