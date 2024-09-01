<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Translation
 */
class TranslationTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testProps()
	{
		$translation = new Translation('en', [
			'translation.author'    => 'Kirby',
			'translation.name'      => 'English',
			'translation.direction' => 'ltr',
			'translation.locale'    => 'en_GB',
			'test'                  => 'Test'
		]);

		$this->assertSame('Kirby', $translation->author());
		$this->assertSame('English', $translation->name());
		$this->assertSame('ltr', $translation->direction());
		$this->assertSame('en_GB', $translation->locale());
		$this->assertSame('Test', $translation->get('test'));
	}

	public function testLoad()
	{
		$translation = Translation::load('de', static::FIXTURES . '/translations/de.json');

		$this->assertSame('de', $translation->code());
		$this->assertSame('Deutsch', $translation->name());

		// invalid
		$translation = Translation::load('zz', static::FIXTURES . '/translations/zz.json');

		$this->assertSame('zz', $translation->code());
		$this->assertSame([], $translation->data());
	}

	public function testToArray()
	{
		$translation = Translation::load('de', static::FIXTURES . '/translations/de.json');

		$this->assertSame([
			'code' => 'de',
			'data' => [
				'translation.direction' => 'ltr',
				'translation.name' => 'Deutsch',
				'translation.author' => 'Kirby Team',
				'error.test' => 'Dies ist ein Testfehler',
			],
			'name' => 'Deutsch',
			'author' => 'Kirby Team',
		], $translation->toArray());
	}
}
