<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
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

        $this->assertEquals([
            LC_ALL => 'en_US'
        ], $language->locale());
        $this->assertEquals('en_US', $language->locale(LC_ALL));
    }

    public function testLocaleArray1()
    {
        $language = new Language([
            'code' => 'en',
            'locale' => [
                LC_ALL   => 'en_US',
                LC_CTYPE => 'en_US.utf8'
            ]
        ]);

        $this->assertEquals([
            LC_ALL   => 'en_US',
            LC_CTYPE => 'en_US.utf8'
        ], $language->locale());
        $this->assertEquals('en_US', $language->locale(LC_ALL));
        $this->assertEquals('en_US.utf8', $language->locale(LC_CTYPE));
        $this->assertEquals('en_US', $language->locale(LC_MONETARY));
    }

    public function testLocaleArray2()
    {
        $language = new Language([
            'code' => 'en',
            'locale' => [
                LC_CTYPE => 'en_US.utf8'
            ]
        ]);

        $this->assertEquals([
            LC_CTYPE => 'en_US.utf8'
        ], $language->locale());
        $this->assertEquals(null, $language->locale(LC_ALL));
        $this->assertEquals('en_US.utf8', $language->locale(LC_CTYPE));
        $this->assertEquals(null, $language->locale(LC_MONETARY));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testLocaleInvalid()
    {
        $language = new Language([
            'code' => 'en',
            'locale' => 123
        ]);
    }

    public function testLocaleDefault()
    {
        $language = new Language([
            'code' => 'en',
        ]);

        $this->assertEquals('en', $language->locale(LC_ALL));
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

    public function testSave()
    {
        $app = new App([
            'roots' => [
                'index'     => $fixtures = __DIR__ . '/fixtures/LanguageTest',
                'languages' => $fixtures
            ]
        ]);

        $file = $fixtures . '/de.php';

        // default
        $language = new Language([
            'code' => 'de',
        ]);

        $language->save();

        $data = include $file;

        $this->assertEquals('de', $data['code']);
        $this->assertEquals(false, $data['default']);
        $this->assertEquals('ltr', $data['direction']);
        $this->assertEquals([LC_ALL => 'de'], $data['locale']);
        $this->assertEquals('de', $data['name']);
        $this->assertEquals([], $data['translations']);
        $this->assertEquals(null, $data['url'] ?? null);


        // custom url
        $language = new Language([
            'code' => 'de',
            'url'  => '/'
        ]);

        $language->save();

        $data = include $file;

        $this->assertEquals('/', $data['url']);


        // custom translations
        $language = new Language([
            'code' => 'de',
            'translations'  => [
                'foo' => 'bar'
            ]
        ]);

        $language->save();

        $data = include $file;

        $this->assertEquals(['foo' => 'bar'], $data['translations']);


        // custom props in file
        Data::write($file, ['custom' => 'test']);

        $language = new Language([
            'code' => 'de'
        ]);

        $language->save();

        $data = include $file;

        $this->assertEquals('test', $data['custom']);

        Dir::remove($fixtures);
    }
}
