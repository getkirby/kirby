<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class UrlTest extends TestCase
{
    public function setUp()
    {
        new App([
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
        $this->assertEquals('https://getkirby.com/projects', Url::to('projects'));
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
