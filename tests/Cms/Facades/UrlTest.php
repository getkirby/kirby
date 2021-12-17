<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class UrlTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);
    }

    public function testHome()
    {
        $this->assertEquals('https://getkirby.com', Url::home());
    }

    public function testTo()
    {
        $this->assertSame('https://getkirby.com', Url::to());
        $this->assertSame('https://getkirby.com', Url::to(''));
        $this->assertSame('https://getkirby.com', Url::to('/'));
        $this->assertSame('https://getkirby.com/projects', Url::to('projects'));
    }

    public function testToWithLanguage()
    {
        $this->app->clone([
            'languages' => [
                'en' => [
                    'code' => 'en'
                ],
                'de' => [
                    'code' => 'de'
                ]
            ],
            'site' => [
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                    [
                        'slug' => 'c',
                        'translations' => [
                            [
                                'code' => 'de',
                                'content' => [
                                    'slug' => 'custom'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('https://getkirby.com/en/a', Url::to('a'));
        $this->assertEquals('https://getkirby.com/en/a', Url::to('a', 'en'));
        $this->assertEquals('https://getkirby.com/de/a', Url::to('a', 'de'));

        $this->assertEquals('https://getkirby.com/en/a', Url::to('a', ['language' => 'en']));
        $this->assertEquals('https://getkirby.com/de/a', Url::to('a', ['language' => 'de']));

        // translated slug
        $this->assertEquals('https://getkirby.com/de/custom', Url::to('c', 'de'));
    }

    public function testToTemplateAsset()
    {
        $app = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/UrlTest'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                    ]
                ]
            ]
        ]);

        $app->site()->visit('test');

        F::write($app->root('assets') . '/css/default.css', 'test');

        $expected = 'https://getkirby.com/assets/css/default.css';

        $this->assertEquals($expected, Url::toTemplateAsset('css', 'css'));

        F::write($app->root('assets') . '/js/default.js', 'test');

        $expected = 'https://getkirby.com/assets/js/default.js';

        $this->assertEquals($expected, Url::toTemplateAsset('js', 'js'));

        Dir::remove($fixtures);
    }
}
