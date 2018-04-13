<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{

    public function setUp(){
        I18n::$locale      = null;
        I18n::$fallback    = [];
        I18n::$translation = [];
    }

    public function testTranslate()
    {
        I18n::$translation = [
            'save' => 'Speichern'
        ];

        $this->assertEquals('Speichern', I18n::translate('save'));
    }

    public function testTranslateWithFallback()
    {
        I18n::$translation = [];

        I18n::$fallback = [
            'save' => 'Save'
        ];

        $this->assertEquals('Save', I18n::translate('save'));
    }

    public function testTranslateWithFallbackArgument()
    {
        I18n::$translation = [];

        $this->assertEquals('Save', I18n::translate('save', 'Save'));
    }

    public function testTranslateArray()
    {
        I18n::$locale = 'en';

        $this->assertEquals('Save', I18n::translate([
            'en' => 'Save'
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
        I18n::$translation = [
            'car' => ['No cars', 'One car', 'Many cars']
        ];

        $this->assertEquals('No cars', I18n::translateCount('car', 0));
        $this->assertEquals('One car', I18n::translateCount('car', 1));
        $this->assertEquals('Many cars', I18n::translateCount('car', 2));
        $this->assertEquals('Many cars', I18n::translateCount('car', 3));
    }

    public function testTranslateCountWithPlaceholders()
    {
        I18n::$translation = [
            'car' => ['No cars', 'One car', '{{ count }} cars']
        ];

        $this->assertEquals('2 cars', I18n::translateCount('car', 2));
        $this->assertEquals('3 cars', I18n::translateCount('car', 3));
    }

}
