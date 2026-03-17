<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SiteVisitTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.SiteVisit';

	public function testVisitWithPageObject(): void
	{
		$site = new Site();
		$page = $site->visit(new Page(['slug' => 'test']));

		$this->assertIsPage('test', $site->page());
		$this->assertIsPage($page, $site->page());
	}

	public function testVisitWithId(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'test']
			]
		]);

		$page = $site->visit('test');

		$this->assertIsPage('test', $site->page());
		$this->assertIsPage($page, $site->page());
	}

	public function testVisitInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid page object');

		$site = new Site();
		$site->visit('nonexists');
	}

	public static function visitMultilangProvider(): array
	{
		return [
			['en', 'Site', 'English Test'],
			['de', 'Seite', 'Deutsch Test']
		];
	}

	#[DataProvider('visitMultilangProvider')]
	public function testVisitInMultiLanguageMode(
		string $languageCode,
		string $siteTitle,
		string $pageTitle
	): void {
		$this->setUpMultiLanguage();

		$site = new Site([
			'children' => [
				[
					'slug' => 'test',
					'translations' => [
						[
							'code'    => 'en',
							'content' => ['title' => 'English Test'],
						],
						[
							'code'    => 'de',
							'content' => ['title' => 'Deutsch Test'],
						]
					]
				]
			],
			'translations' => [
				[
					'code'    => 'en',
					'content' => ['title' => 'Site']
				],
				[
					'code'    => 'de',
					'content' => ['title' => 'Seite']
				],
			]
		]);
		$page = $site->visit('test', $languageCode);

		$this->assertSame($languageCode, $this->app->language()->code());
		$this->assertSame('test', $page->slug());
		$this->assertSame($siteTitle, $site->title()->value());
		$this->assertSame($pageTitle, $page->title()->value());
	}

	public function testVisitWithoutLanguageCode(): void
	{
		// create app with locale option
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'locale' => 'de_DE.UTF-8'
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$site = $this->app->site();
		$site->visit('test');

		// verify locale was set from config
		$this->assertTrue(
			in_array(
				setlocale(LC_TIME, 0),
				['de', 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF8', 'de_DE.ISO8859-1']
			)
		);
	}
}
