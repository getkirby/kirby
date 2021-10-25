<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\I18n
 */
class I18nTest extends TestCase
{
    public function setUp(): void
    {
        I18n::$locale       = 'en';
        I18n::$load         = null;
        I18n::$fallback     = 'en';
        I18n::$translations = [];
    }

    /**
     * @covers ::fallbacks
     */
    public function testFallbacks()
    {
        I18n::$fallback = 'en';
        $this->assertSame(['en'], I18n::fallbacks());

        I18n::$fallback = ['en'];
        $this->assertSame(['en'], I18n::fallbacks());

        I18n::$fallback = ['en-us', 'en'];
        $this->assertSame(['en-us', 'en'], I18n::fallbacks());

        I18n::$fallback = null;
        $this->assertSame(['en'], I18n::fallbacks());

        I18n::$fallback = function () {
            return 'de';
        };
        $this->assertSame(['de'], I18n::fallbacks());

        I18n::$fallback = function () {
            return ['de'];
        };
        $this->assertSame(['de'], I18n::fallbacks());

        I18n::$fallback = function () {
            return ['de', 'en'];
        };
        $this->assertSame(['de', 'en'], I18n::fallbacks());
    }

    /**
     * @covers ::form
     */
    public function testForm()
    {
        $this->assertSame('singular', I18n::form(1));
        $this->assertSame('plural', I18n::form(2));

        // simplified zero handling
        $this->assertSame('plural', I18n::form(0));

        // correct zero handling
        $this->assertSame('none', I18n::form(0, true));
    }

    /**
     * @covers ::formatNumber
     * @covers ::decimalNumberFormatter
     */
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

    /**
     * @covers ::locale
     */
    public function testLocale()
    {
        I18n::$locale = 'de';
        $this->assertSame('de', I18n::locale());

        I18n::$locale = function () {
            return 'de';
        };
        $this->assertSame('de', I18n::locale());

        I18n::$locale = null;
        $this->assertSame('en', I18n::locale());
    }

    /**
     * @covers ::template
     */
    public function testTemplate()
    {
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ]
        ];
        $this->assertSame('This is a test template', I18n::template('template', [
            'test' => 'test template'
        ]));

        // with fallback
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ]
        ];
        $this->assertSame('This is a fallback', I18n::template('does-not-exist', 'This is a fallback', [
            'test' => 'test template'
        ]));
        $this->assertSame('This is a test fallback', I18n::template('does-not-exist', 'This is a {test}', [
            'test' => 'test fallback'
        ]));

        // with locale
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ],
            'de' => [
                'template' => 'Das ist ein {test}'
            ]
        ];

        $this->assertSame('Das ist ein test template', I18n::template('template', null, [
            'test' => 'test template'
        ], 'de'));
    }

    /**
     * @covers ::translate
     */
    public function testTranslate()
    {
        I18n::$translations = [
            'en' => [
                'save' => 'Speichern'
            ]
        ];

        $this->assertSame('Speichern', I18n::translate('save'));
        $this->assertNull(I18n::translate('invalid'));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateWithFallback()
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

    /**
     * @covers ::translate
     */
    public function testTranslateWithFallbackArgument()
    {
        $this->assertSame('Save', I18n::translate('save', 'Save'));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateWithArrayFallback()
    {
        I18n::$locale = 'de';

        $input = [
        ];

        $fallback = [
            'en' => 'Save',
            'de' => 'Speichern'
        ];

        $this->assertSame('Speichern', I18n::translate($input, $fallback));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateArray()
    {
        $this->assertSame('Save', I18n::translate([
            'en' => 'Save',
        ]));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateArrayWithFallback()
    {
        $this->assertSame('fallback', I18n::translate([
            'de' => 'Save',
        ], 'fallback'));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateArrayWithArrayFallback()
    {
        // use the english translation as fallback if available
        $this->assertSame('Save', I18n::translate(['de' => 'Speichern'], ['en' => 'Save']));

        // use the first value if there's no english translation
        $this->assertSame('Fallback', I18n::translate(['de' => 'Speichern'], ['first' => 'Fallback']));
    }

    /**
     * @covers ::translate
     */
    public function testTranslateArrayWithDifferentLocale()
    {
        I18n::$locale = 'de';

        $this->assertSame('Speichern', I18n::translate([
            'en' => 'Save',
            'de' => 'Speichern'
        ]));
    }

    /**
     * @covers ::translateCount
     */
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

    /**
     * @covers ::translateCount
     */
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

    /**
     * @covers ::translateCount
     */
    public function testTranslateCountWithMissingTranslation()
    {
        I18n::$translations = [
            'en' => []
        ];

        $this->assertNull(I18n::translateCount('car', 1));
    }

    /**
     * @covers ::translateCount
     */
    public function testTranslateCountWithStringTranslation()
    {
        I18n::$translations = [
            'en' => [
                'car' => '{{ count }} car(s)'
            ]
        ];

        $this->assertSame('1 car(s)', I18n::translateCount('car', 1));
        $this->assertSame('2 car(s)', I18n::translateCount('car', 2));
    }

    /**
     * @covers ::translateCount
     */
    public function testTranslateCountWithCallback()
    {
        I18n::$translations = [
            'en' => [
                'car' => function ($count) {
                    switch ($count) {
                        case 0:
                            return 'No car';
                        case 1:
                            return 'One car';
                        case in_array($count, [2, 3, 4]) === true:
                            return 'Few cars';
                        default:
                            return 'Many cars';
                    }
                }
            ]
        ];

        $this->assertSame('No car', I18n::translateCount('car', 0));
        $this->assertSame('One car', I18n::translateCount('car', 1));
        $this->assertSame('Few cars', I18n::translateCount('car', 2));
        $this->assertSame('Many cars', I18n::translateCount('car', 5));
    }

    /**
     * @covers ::translation
     */
    public function testTranslation()
    {
        I18n::$translations = [
            'en' => [
                'test' => 'yay'
            ],
            'de' => [
                'test' => 'juhu'
            ]
        ];

        I18n::$locale = 'en';
        $this->assertSame('yay', I18n::translate('test'));

        I18n::$locale = 'de';
        $this->assertSame('juhu', I18n::translate('test'));

        I18n::$locale = 'fr';
        I18n::$fallback = 'fr';
        $this->assertNull(I18n::translate('test'));
    }

    /**
     * @covers ::translation
     */
    public function testTranslationLoad()
    {
        $translations = [
            'en' => [
                'test' => 'yay'
            ],
            'de' => [
                'test' => 'juhu'
            ]
        ];

        I18n::$load = function ($locale) use ($translations) {
            return $translations[$locale] ?? [];
        };

        I18n::$locale = 'en';
        $this->assertSame('yay', I18n::translate('test'));

        I18n::$locale = 'de';
        $this->assertSame('juhu', I18n::translate('test'));

        I18n::$locale = 'fr';
        I18n::$fallback = 'fr';
        $this->assertNull(I18n::translate('test'));
    }

    /**
     * @covers ::translations
     */
    public function testTranslations()
    {
        $this->assertSame([], I18n::translations());

        I18n::$translations = $translations = [
            'en' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertSame($translations, I18n::translations());
    }
}
