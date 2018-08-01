<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{

    public function setUp(){
        I18n::$locale       = 'en';
        I18n::$load         = null;
        I18n::$fallback     = 'en';
        I18n::$translations = [];
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

}
