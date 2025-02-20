<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class NewSiteVisitTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteVisit';

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
}
