<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{
    public function setUp(): void
    {
        I18n::$locale       = 'en';
        I18n::$load         = null;
        I18n::$fallback     = 'en';
        I18n::$translations = [];
    }

    public function testForm()
    {
        $this->assertEquals('singular', I18n::form(1));
        $this->assertEquals('plural', I18n::form(2));

        // simplified zero handling
        $this->assertEquals('plural', I18n::form(0));

        // correct zero handling
        $this->assertEquals('none', I18n::form(0, true));
    }

    public function testTemplate()
    {
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ]
        ];

        $this->assertEquals('This is a test template', I18n::template('template', [
            'test' => 'test template'
        ]));
    }

    public function testTemplateWithFallback()
    {
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ]
        ];

        $this->assertEquals('This is a fallback', I18n::template('does-not-exist', 'This is a fallback', [
            'test' => 'test template'
        ]));

        $this->assertEquals('This is a test fallback', I18n::template('does-not-exist', 'This is a {test}', [
            'test' => 'test fallback'
        ]));
    }

    public function testTemplateWithLocale()
    {
        I18n::$translations = [
            'en' => [
                'template' => 'This is a {test}'
            ],
            'de' => [
                'template' => 'Das ist ein {test}'
            ]
        ];

        $this->assertEquals('Das ist ein test template', I18n::template('template', null, [
            'test' => 'test template'
        ], 'de'));
    }

    public function testTranslate()
    {
        I18n::$translations = [
            'en' => [
                'save' => 'Speichern'
            ]
        ];

        $this->assertEquals('Speichern', I18n::translate('save'));
    }

    public function testTranslateWithFallback()
    {
        I18n::$translations = [
            'en' => [
                'save' => 'Save'
            ]
        ];

        I18n::$locale = 'de';

        $this->assertEquals('Save', I18n::translate('save'));
    }

    public function testTranslateWithFallbackArgument()
    {
        $this->assertEquals('Save', I18n::translate('save', 'Save'));
    }

    public function testTranslateWithArrayFallback()
    {
        I18n::$locale = 'de';

        $input = [
        ];

        $fallback = [
            'en' => 'Save',
            'de' => 'Speichern'
        ];

        $this->assertEquals('Speichern', I18n::translate($input, $fallback));
    }

    public function testTranslateArray()
    {
        $this->assertEquals('Save', I18n::translate([
            'en' => 'Save',
        ]));
    }

    public function testTranslateArrayWithDifferentLocale()
    {
        I18n::$locale = 'de';

        $this->assertEquals('Speichern', I18n::translate([
            'en' => 'Save',
            'de' => 'Speichern'
        ]));
    }

    public function testTranslateCount()
    {
        I18n::$translations = [
            'en' => [
                'car' => ['No cars', 'One car', 'Many cars']
            ]
        ];

        $this->assertEquals('No cars', I18n::translateCount('car', 0));
        $this->assertEquals('One car', I18n::translateCount('car', 1));
        $this->assertEquals('Many cars', I18n::translateCount('car', 2));
        $this->assertEquals('Many cars', I18n::translateCount('car', 3));
    }

    public function testTranslateCountWithPlaceholders()
    {
        I18n::$translations = [
            'en' => [
                'car' => ['No cars', 'One car', '{{ count }} cars']
            ]
        ];

        $this->assertEquals('2 cars', I18n::translateCount('car', 2));
        $this->assertEquals('3 cars', I18n::translateCount('car', 3));
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
                'car' => 'One car'
            ]
        ];

        $this->assertEquals('One car', I18n::translateCount('car', 1));
        $this->assertEquals('One car', I18n::translateCount('car', 2));
    }

    public function testTranslateCountWithInvalidArgs()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Please provide 3 translations');

        I18n::$translations = [
            'en' => [
                'car' => ['No cars', 'One car']
            ]
        ];

        I18n::translateCount('car', 2);
    }

    public function testLoadTranslation()
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
        $this->assertEquals('yay', I18n::translate('test'));

        I18n::$locale = 'de';
        $this->assertEquals('juhu', I18n::translate('test'));
    }

    public function testLastResortFallback()
    {
        I18n::$fallback = null;

        $this->assertEquals('en', I18n::fallback());
    }

    public function testLazyFallback()
    {
        I18n::$fallback = function () {
            return 'de';
        };

        $this->assertEquals('de', I18n::fallback());
    }

    public function testLazyLocale()
    {
        I18n::$locale = function () {
            return 'de';
        };

        $this->assertEquals('de', I18n::locale());
    }

    public function testTranslations()
    {
        $this->assertEquals([], I18n::translations());

        I18n::$translations = $translations = [
            'en' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($translations, I18n::translations());
    }
}
