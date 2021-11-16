<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/LanguageTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testCodeAndId()
    {
        $language = new Language([
            'code' => 'en'
        ]);

        $this->assertEquals('en', $language->code());
        $this->assertEquals('en', $language->id());
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

    public function testLocaleArray3()
    {
        $language = new Language([
            'code' => 'en',
            'locale' => [
                'LC_ALL'   => 'en_US',
                'LC_CTYPE' => 'en_US.utf8'
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

    public function testLocaleInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

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
        $this->assertEquals(['LC_ALL' => 'de'], $data['locale']);
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

    public function testRouter()
    {
        $language = new Language([
            'code' => 'de'
        ]);

        $this->assertInstanceOf(LanguageRouter::class, $language->router());
    }

    public function testToArrayAndDebuginfo()
    {
        $language = new Language([
            'code'   => 'de',
            'name'   => 'Deutsch',
            'locale' => 'de_DE',
        ]);

        $expected = [
            'code'      => 'de',
            'default'   => false,
            'direction' => 'ltr',
            'locale'    => [LC_ALL => 'de_DE'],
            'name'      => 'Deutsch',
            'rules'     => $language->rules(),
            'url'       => '/de'
        ];

        $this->assertEquals($expected, $language->toArray());
        $this->assertEquals($expected, $language->__debugInfo());
    }

    public function testExists()
    {
        $app = new App([
            'roots' => [
                'index' => __DIR__ . '/fixtures'
            ]
        ]);

        $language = new Language([
            'code' => 'de'
        ]);

        $this->assertFalse($language->exists());

        F::write($language->root(), 'test');

        $this->assertTrue($language->exists());

        Dir::remove(__DIR__ . '/fixtures');
    }

    public function testRoot()
    {
        $app = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures'
            ]
        ]);

        $language = new Language([
            'code' => 'de'
        ]);

        $this->assertEquals($fixtures . '/site/languages/de.php', $language->root());
    }

    public function pathProvider()
    {
        return [
            [null, 'en'],
            ['/', ''],
            ['/en', 'en'],
            ['/en/', 'en'],
            ['https://getkirby.com/en', 'en'],
            ['https://getkirby.com/en/', 'en'],
            ['https://getkirby.com/sub/sub', 'sub/sub'],
            ['https://getkirby.com/sub/sub/', 'sub/sub'],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testPath($input, $expected)
    {
        $language = new Language([
            'code' => 'en',
            'url'  => $input
        ]);

        $this->assertEquals($expected, $language->path());
    }

    public function patternProvider()
    {
        return [
            [null, 'en/(:all?)'],
            ['/', '(:all)'],
            ['/en', 'en/(:all?)'],
            ['/en/', 'en/(:all?)'],
            ['https://getkirby.com', '(:all)'],
            ['https://getkirby.com/', '(:all)'],
            ['https://getkirby.com/en', 'en/(:all?)'],
            ['https://getkirby.com/en/', 'en/(:all?)'],
            ['https://getkirby.com/sub/sub', 'sub/sub/(:all?)'],
            ['https://getkirby.com/sub/sub/', 'sub/sub/(:all?)'],
        ];
    }

    /**
     * @dataProvider patternProvider
     */
    public function testPattern($input, $expected)
    {
        $language = new Language([
            'code' => 'en',
            'url'  => $input
        ]);

        $this->assertEquals($expected, $language->pattern());
    }

    public function baseUrlProvider()
    {
        return [
            ['https://getkirby.com', null, 'https://getkirby.com'],
            ['https://getkirby.com', '/en', 'https://getkirby.com'],
            ['https://getkirby.com', 'https://getkirby.de', 'https://getkirby.de'],
            ['https://getkirby.com', 'https://getkirby.de/en', 'https://getkirby.de'],
            ['http://localhost/example.com', null, 'http://localhost/example.com'],
            ['http://localhost/example.com', '/en', 'http://localhost/example.com'],
            ['http://localhost/example.com', 'http://getkirby.com', 'http://getkirby.com'],
            ['http://localhost/example.com', 'http://getkirby.com/en', 'http://getkirby.com'],
        ];
    }

    /**
     * @dataProvider baseUrlProvider
     */
    public function testBaseUrl($kirbyUrl, $url, $expected)
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => $kirbyUrl
            ]
        ]);

        // default
        $language = new Language([
            'code' => 'en',
            'url'  => $url
        ]);

        $this->assertEquals($expected, $language->baseUrl());
    }

    public function testCreate()
    {
        $language = Language::create([
            'code' => 'en'
        ]);

        $this->assertSame('en', $language->code());
        $this->assertSame(true, $language->isDefault());
        $this->assertSame('ltr', $language->direction());
        $this->assertSame('en', $language->name());
        $this->assertSame('/en', $language->url());
    }

    public function testDelete()
    {
        $language = Language::create([
            'code' => 'en'
        ]);

        $this->assertTrue($language->delete());
    }

    public function testUpdate()
    {
        Dir::make($contentDir = $this->fixtures . '/content');

        $language = Language::create([
            'code' => 'en'
        ]);

        $language = $language->update(['name' => 'English']);

        $this->assertSame('English', $language->name());
    }
}
