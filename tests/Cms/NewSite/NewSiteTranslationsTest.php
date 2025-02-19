<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class NewSiteTranslationsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteTranslationsTest';

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpMultiLanguage();
	}

	public function testContentInEnglish(): void
	{
		$site = new Site([
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'title'        => 'Site',
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
		]);
		$this->assertSame('Site', $site->title()->value());
		$this->assertSame('Untranslated', $site->untranslated()->value());
	}

	public function testContentInDeutsch(): void
	{
		$site = new Site([
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'title'        => 'Site',
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
		]);

		$this->app->setCurrentLanguage('de');
		$this->app->setCurrentTranslation('de');

		$this->assertSame('Seite', $site->title()->value());
		$this->assertSame('Untranslated', $site->untranslated()->value());
	}

	public function testTranslations(): void
	{
		$site = new Site();
		$this->assertCount(2, $site->translations());
		$this->assertSame(['en', 'de'], $site->translations()->keys());
	}
}
