<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(I18n::class)]
class I18nTest extends TestCase
{
	public function setUp(): void
	{
		I18n::$locale       = 'en';
		I18n::$load         = null;
		I18n::$fallback     = 'en';
		I18n::$translations = [];
	}

	public function testFallbacks()
	{
		I18n::$fallback = 'de';
		$this->assertSame(['de'], I18n::fallbacks());

		I18n::$fallback = ['de'];
		$this->assertSame(['de'], I18n::fallbacks());

		I18n::$fallback = ['en-us', 'en'];
		$this->assertSame(['en-us', 'en'], I18n::fallbacks());

		I18n::$fallback = null;
		$this->assertSame(['en'], I18n::fallbacks());

		I18n::$fallback = fn () => 'de';
		$this->assertSame(['de'], I18n::fallbacks());

		I18n::$fallback = fn () => ['de'];
		$this->assertSame(['de'], I18n::fallbacks());

		I18n::$fallback = fn () => ['de', 'en'];
		$this->assertSame(['de', 'en'], I18n::fallbacks());
	}

	public function testForm()
	{
		$this->assertSame('singular', I18n::form(1));
		$this->assertSame('plural', I18n::form(2));

		// simplified zero handling
		$this->assertSame('plural', I18n::form(0));

		// correct zero handling
		$this->assertSame('none', I18n::form(0, true));
	}

	public function testFormatNumber()
	{
		$this->assertSame('2', I18n::formatNumber(2));
		$this->assertSame('2', I18n::formatNumber(2, 'en'));
		$this->assertSame('2', I18n::formatNumber(2, 'de'));

		$this->assertSame('1,234,567', I18n::formatNumber(1234567));
		$this->assertSame('1,234,567', I18n::formatNumber(1234567, 'en'));
		$this->assertSame('1.234.567', I18n::formatNumber(1234567, 'de'));

		$this->assertSame('1,234,567.89', I18n::formatNumber(1234567.89));
		$this->assertSame('1,234,567.89', I18n::formatNumber(1234567.89, 'en'));
		$this->assertSame('1.234.567,89', I18n::formatNumber(1234567.89, 'de'));
	}

	public function testLocale()
	{
		I18n::$locale = 'de';
		$this->assertSame('de', I18n::locale());

		I18n::$locale = fn () => 'de';
		$this->assertSame('de', I18n::locale());

		I18n::$locale = null;
		$this->assertSame('en', I18n::locale());
	}

	public function testTemplate()
	{
		I18n::$translations = [
			'en' => [
				'template' => 'This is a {test}'
			]
		];
		$this->assertSame(
			'This is a test template',
			I18n::template('template', ['test' => 'test template'])
		);

		// with fallback
		I18n::$translations = [
			'en' => [
				'template' => 'This is a {test}'
			]
		];
		$this->assertSame(
			'This is a fallback',
			I18n::template('does-not-exist', 'This is a fallback', ['test' => 'test template'])
		);
		$this->assertSame(
			'This is a test fallback',
			I18n::template('does-not-exist', 'This is a {test}', ['test' => 'test fallback'])
		);

		// with locale
		I18n::$translations = [
			'en' => [
				'template' => 'This is a {test}'
			],
			'de' => [
				'template' => 'Das ist ein {test}'
			]
		];

		$this->assertSame(
			'Das ist ein test template',
			I18n::template('template', null, ['test' => 'test template'], 'de')
		);
	}

	public function testTranslateI18nKey()
	{
		I18n::$translations = [
			'en' => ['save' => 'Speichern']
		];

		$this->assertSame('Speichern', I18n::translate('save'));
		$this->assertNull(I18n::translate('invalid'));
	}

	public function testTranslateI18nKeyShortLocale()
	{
		I18n::$translations = [
			'en' => ['go' => 'Let\'s go'],
			'es' => ['go' => 'Vamos']
		];

		I18n::$locale = 'es_ES';
		$this->assertSame('Vamos', I18n::translate('go'));
	}

	public function testTranslateI18nKeyWithFallbackStringArgument()
	{
		$this->assertSame('My fallback', I18n::translate('not.exist', 'My fallback'));
	}

	public function testTranslateI18nKeyWithFallbackArrayArgument()
	{
		$this->assertSame('My fallback in array', I18n::translate('not.exist', [
			'de' => 'Notfalllösung',
			'en' => 'My fallback in array'
		]));
	}

	public function testTranslateI18nKeyWithFallbackLocales()
	{
		I18n::$translations = [
			'en' => [
				'save1' => 'Save1',
				'save2' => 'Save2'
			],
			'de' => [
				'save1' => 'Speichern1'
			]
		];

		I18n::$locale = 'fr';

		I18n::$fallback = 'en';
		$this->assertSame('Save1', I18n::translate('save1'));
		$this->assertSame('Save2', I18n::translate('save2'));

		I18n::$fallback = ['de', 'en'];
		$this->assertSame('Speichern1', I18n::translate('save1'));
		$this->assertSame('Save2', I18n::translate('save2'));
	}

	public function testTranslateArray()
	{
		$this->assertSame('Save', I18n::translate(['en' => 'Save']));
	}

	public function testTranslateArrayShortLocale()
	{
		I18n::$locale = 'es_ES';
		$this->assertSame('Vamos', I18n::translate(['es' => 'Vamos']));
	}

	public function testTranslateArrayWildcard()
	{
		I18n::$locale = 'de';

		I18n::$translations = [
			'de' => ['save' => 'Speichern']
		];

		$this->assertSame('Speichern', I18n::translate(['*' => 'save']));
	}

	public function testTranslateArrayWithFallbackArray()
	{
		// English is current locale, not in first array,
		// but in fallback array
		$this->assertSame(
			'Save',
			I18n::translate(['de' => 'Speichern'], ['en' => 'Save'])
		);
	}

	public function testTranslateArrayWithFallbackArrayShortLocale()
	{
		I18n::$locale = 'es_ES';
		$this->assertSame(
			'Vamos',
			I18n::translate(['de' => 'Speichern'], ['es' => 'Vamos'])
		);
	}

	public function testTranslateArrayFallbackLocales()
	{
		I18n::$locale = 'fr';
		I18n::$fallback = 'de';
		$this->assertSame(
			'Speichern',
			I18n::translate(
				['es' => 'Vamos', 'de' => 'Speichern'],
				['en' => 'Save']
			)
		);

		I18n::$fallback = ['es', 'de'];
		$this->assertSame(
			'Vamos',
			I18n::translate(
				['es' => 'Vamos', 'de' => 'Speichern'],
				['en' => 'Save']
			)
		);
	}

	public function testTranslateArrayFallbackLocalesFromFallbackArray()
	{
		I18n::$locale = 'fr';
		I18n::$fallback = 'en';
		$this->assertSame(
			'Save',
			I18n::translate(
				['es' => 'Vamos', 'de' => 'Speichern'],
				['en' => 'Save']
			)
		);

		I18n::$fallback = ['de', 'en'];
		$this->assertSame(
			'Speichern',
			I18n::translate(
				['es' => 'Vamos'],
				['en' => 'Save', 'de' => 'Speichern']
			)
		);
	}

	public function testTranslateArrayWithFallbackString()
	{
		$this->assertSame(
			'fallback',
			I18n::translate(['de' => 'Save'], 'fallback')
		);
	}

	public function testTranslateArrayWithFallbackFirstKey()
	{
		$this->assertSame(
			'Algunos',
			I18n::translate(
				['es' => 'Algunos', 'de' => 'Einige'],
				['es' => 'Alguna', 'de' => 'Einzige']
			)
		);

		$this->assertSame(
			'Alguna',
			I18n::translate(
				null,
				['es' => 'Alguna', 'de' => 'Einzige']
			)
		);
	}

	public function testTranslateCount()
	{
		I18n::$translations = [
			'en' => [
				'car' => ['No cars', 'One car', 'Two cars', 'Many cars']
			]
		];

		$this->assertSame('No cars', I18n::translateCount('car', 0));
		$this->assertSame('One car', I18n::translateCount('car', 1));
		$this->assertSame('Two cars', I18n::translateCount('car', 2));
		$this->assertSame('Many cars', I18n::translateCount('car', 3));
		$this->assertSame('Many cars', I18n::translateCount('car', 4));
	}

	public function testTranslateCountWithPlaceholders()
	{
		I18n::$translations = [
			'en' => [
				'car' => ['No cars', 'One car', '{{ count }} cars']
			],
			'de' => [
				'car' => ['Keine Autos', 'Ein Auto', '{{ count }} Autos']
			]
		];

		$this->assertSame('2 cars', I18n::translateCount('car', 2));
		$this->assertSame('3 cars', I18n::translateCount('car', 3));
		$this->assertSame('1,234,567 cars', I18n::translateCount('car', 1234567));
		$this->assertSame('1,234,567 cars', I18n::translateCount('car', 1234567, null));
		$this->assertSame('1,234,567 cars', I18n::translateCount('car', 1234567, null, true));
		$this->assertSame('1234567 cars', I18n::translateCount('car', 1234567, null, false));
		$this->assertSame('1.234.567 Autos', I18n::translateCount('car', 1234567, 'de'));
		$this->assertSame('1.234.567 Autos', I18n::translateCount('car', 1234567, 'de', true));
		$this->assertSame('1234567 Autos', I18n::translateCount('car', 1234567, 'de', false));
	}

	public function testTranslateCountWithMissingTranslation()
	{
		I18n::$translations = [
			'en' => []
		];

		$this->assertNull(I18n::translateCount('car', 1));
	}

	public function testTranslateCountWithStringTranslation()
	{
		I18n::$translations = [
			'en' => [
				'car'  => '{{ count }} car(s)',
				'bike' => '{ count } bike(s)'
			]
		];

		$this->assertSame('1 car(s)', I18n::translateCount('car', 1));
		$this->assertSame('2 car(s)', I18n::translateCount('car', 2));
		$this->assertSame('1 bike(s)', I18n::translateCount('bike', 1));
		$this->assertSame('2 bike(s)', I18n::translateCount('bike', 2));
	}

	public function testTranslateCountWithCallback()
	{
		I18n::$translations = [
			'en' => [
				'car' => fn ($count) => match ($count) {
					0       => 'No car',
					1       => 'One car',
					2, 3, 4 => 'Few cars',
					default => 'Many cars'
				}
			]
		];

		$this->assertSame('No car', I18n::translateCount('car', 0));
		$this->assertSame('One car', I18n::translateCount('car', 1));
		$this->assertSame('Few cars', I18n::translateCount('car', 2));
		$this->assertSame('Many cars', I18n::translateCount('car', 5));
	}

	public function testTranslation()
	{
		I18n::$translations = [
			'en' => ['test' => 'yay'],
			'de' => ['test' => 'juhu'],
			'es' => ['test' => 'vamos']
		];

		I18n::$locale = 'en';
		$this->assertSame('yay', I18n::translate('test'));

		I18n::$locale = 'de';
		$this->assertSame('juhu', I18n::translate('test'));

		I18n::$locale = 'es_ES';
		$this->assertSame('vamos', I18n::translate('test'));

		I18n::$locale = 'fr';
		I18n::$fallback = 'fr';
		$this->assertNull(I18n::translate('test'));
	}

	public function testTranslationLoad()
	{
		$translations = [
			'en' => ['test' => 'yay'],
			'de' => ['test' => 'juhu']
		];

		I18n::$load = fn ($locale) => $translations[$locale] ?? [];

		I18n::$locale = 'en';
		$this->assertSame('yay', I18n::translate('test'));

		I18n::$locale = 'de';
		$this->assertSame('juhu', I18n::translate('test'));

		I18n::$locale = 'fr';
		I18n::$fallback = 'fr';
		$this->assertNull(I18n::translate('test'));
	}

	public function testTranslations()
	{
		$this->assertSame([], I18n::translations());

		I18n::$translations = $translations = [
			'en' => ['foo' => 'bar']
		];

		$this->assertSame($translations, I18n::translations());
	}
}
