<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AppLanguagesTest extends TestCase
{
	protected string|null $acceptLang;

	public function setUp(): void
	{
		$this->acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
	}

	public function tearDown(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->acceptLang;
	}

	public function testLanguages(): void
	{
		$app = new App([
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
			]
		]);

		$this->assertTrue($app->multilang());
		$this->assertCount(2, $app->languages());
		$this->assertSame('en', $app->languageCode());
	}

	public function testLanguageCode(): void
	{
		$app = new App([
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
			]
		]);

		$this->assertSame('de', $app->languageCode('de'));
		$this->assertSame('en', $app->languageCode('en'));
		$this->assertSame('en', $app->languageCode());
		$this->assertNull($app->languageCode('fr'));
	}

	public static function detectedLanguageProvider(): array
	{
		return [
			['en', 'en'],
			['en-GB', 'en'],
			['en-US', 'us'],
			['de', 'de'],
			['fr', 'en'],
			['en-US, en;q=0.5', 'us'],
			['en-US;q=0.5, de;q=0.8, fr;q=0.9', 'de'],
			['tr, en-US;q=0.9, en;q=0.8', 'tr']
		];
	}

	/**
	 * @backupGlobals enabled
	 */
	#[DataProvider('detectedLanguageProvider')]
	public function testDetectedLanguage($accept, $expected): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept;

		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English (GB)',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				],
				[
					'code'    => 'us',
					'name'    => 'English (US)',
					'locale'  => 'en_US'
				],
				[
					'code'    => 'tr',
					'name'    => 'Turkish (TR)',
					'locale'  => 'tr_TR.utf-8'
				]
			],
			'options' => [
				'languages' => true,
				'languages.detect' => true
			]
		]);

		$this->assertSame($expected, $app->detectedLanguage()->code());
	}

	public static function detectedLanguageWithLocaleProvider(): array
	{
		return [
			['en', 'en'],
			['en-GB', 'en'],
			['en-US', 'en-us'],
			['de', 'de'],
			['fr', 'en'],
			['en-US, en;q=0.5', 'en-us'],
			['en-US;q=0.5, de;q=0.8, fr;q=0.9', 'de'],
		];
	}

	/**
	 * @backupGlobals enabled
	 */
	#[DataProvider('detectedLanguageWithLocaleProvider')]
	public function testDetectedLanguageWithLocale($accept, $expected): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept;

		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_GB'
				],
				[
					'code'    => 'at',
					'name'    => 'Deutsch (Österreich)',
					'default' => false,
					'locale'  => 'de_AT'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'default' => false,
					'locale'  => 'de_DE'
				],
				[
					'code'    => 'en-us',
					'name'    => 'English',
					'default' => false,
					'locale'  => 'en_US'
				]
			],
			'options' => [
				'languages' => true,
				'languages.detect' => true
			]
		]);

		$this->assertSame($expected, $app->detectedLanguage()->code());
	}
}
