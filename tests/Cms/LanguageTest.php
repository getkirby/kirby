<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testCode()
    {
        $language = new Language([
            'code' => 'en'
        ]);

        $this->assertEquals('en', $language->code());
    }

    public function testDefaultDefault()
    {
        $language = new Language([
            'code' => 'en'
        ]);

        $this->assertFalse($language->isDefault());
    }

    public function testDefaultIsTrue()
    {
        $language = new Language([
            'code'    => 'en',
            'default' => true
        ]);

        $this->assertTrue($language->isDefault());
    }

    public function testDefaultIsFalse()
    {
        $language = new Language([
            'code'    => 'en',
            'default' => false
        ]);

        $this->assertFalse($language->isDefault());
    }

    public function testDirection()
    {
        $language = new Language([
            'code'      => 'en',
            'direction' => 'rtl'
        ]);

        $this->assertEquals('rtl', $language->direction());

        $language = new Language([
            'code'      => 'en',
            'direction' => 'ltr'
        ]);

        $this->assertEquals('ltr', $language->direction());

        $language = new Language([
            'code'      => 'en',
            'direction' => 'invalid'
        ]);

        $this->assertEquals('ltr', $language->direction());
    }

    public function testLocale()
    {
        $language = new Language([
            'code' => 'en',
            'locale' => 'en_US'
        ]);

        $this->assertEquals('en_US', $language->locale());
    }

    public function testLocaleDefault()
    {
        $language = new Language([
            'code' => 'en',
        ]);

        $this->assertEquals('en', $language->locale());
    }

    public function testName()
    {
        $language = new Language([
            'code' => 'en',
            'name' => 'English'
        ]);

        $this->assertEquals('English', $language->name());
    }

    public function testNameDefault()
    {
        $language = new Language([
            'code' => 'en',
        ]);

        $this->assertEquals('en', $language->name());
    }

    public function testUrlWithRelativeValue()
    {
        $language = new Language([
            'code' => 'en',
            'url'  => 'super'
        ]);

        $this->assertEquals('/super', $language->url());
    }

    public function testUrlWithAbsoluteValue()
    {
        $language = new Language([
            'code' => 'en',
            'url'  => 'https://en.getkirby.com'
        ]);

        $this->assertEquals('https://en.getkirby.com', $language->url());
    }

    public function testUrlWithDash()
    {
        $language = new Language([
            'code' => 'en',
            'url'  => '/'
        ]);

        $this->assertEquals('/', $language->url());
    }

    public function testUrlDefault()
    {
        $language = new Language([
            'code' => 'en',
        ]);

        $this->assertEquals('/en', $language->url());
    }
}
