<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageTitleTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageTitle';

	public function testTitleFromSlug(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertSame('test', $page->title()->value());
	}

	public function testTitleInSingleLanguageMode(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test Title'
			]
		]);

		$this->assertSame('Test Title', $page->title()->value());
	}

	public function testTitleInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'title' => 'Test Title EN'
					]
				],
				[
					'code' => 'de',
					'content' => [
						'title' => 'Test Title DE'
					]
				]
			]
		]);

		$this->assertSame('Test Title EN', $page->title()->value());

		// Switch to German
		$this->app->setCurrentLanguage('de');

		$this->assertSame('Test Title DE', $page->title()->value());
	}
}
