<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class AppLanguagesTest extends TestCase
{
	public function testLanguages()
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

	public function testLanguageCode()
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

	public function detectedLanguageProvider(): array
	{
		return [
			['en', 'en'],
			['en-GB', 'en'],
			['en-US', 'us'],
			['de', 'de'],
			['fr', 'en'],
			['en-US,en;q=0.5', 'us'],
		];
	}

	/**
	 * @dataProvider detectedLanguageProvider
	 */
	public function testDetectedLanguage($accept, $expected)
	{
		// set the accepted visitor language
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
				]
			],
			'options' => [
				'languages' => true,
				'languages.detect' => true
			]
		]);

		$this->assertSame($app->detectedLanguage()->code(), $expected);
	}
}
