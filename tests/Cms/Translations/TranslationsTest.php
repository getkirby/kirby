<?php

namespace Kirby\Cms;

class TranslationsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testFactory(): void
	{
		$translations = Translations::factory([
			'de' => [
				'translation.name' => 'Deutsch'
			],
			'en' => [
				'translation.name' => 'English'
			]
		]);

		$this->assertCount(2, $translations);
		$this->assertTrue($translations->has('de'));
		$this->assertTrue($translations->has('en'));
	}

	public function testLoad(): void
	{
		$translations = Translations::load(static::FIXTURES . '/translations');

		$this->assertCount(2, $translations);
		$this->assertTrue($translations->has('de'));
		$this->assertTrue($translations->has('en'));
	}
}
