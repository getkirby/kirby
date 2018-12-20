<?php

namespace Kirby\Cms;

class UrlsTest extends TestCase
{
    public function defaultUrlProvider(): array
    {
        return [
            ['/',      'index'],
            ['/media', 'media'],
            ['/api',   'api'],
        ];
    }

    /**
     * @dataProvider defaultUrlProvider
     */
    public function testDefaulUrl($url, $method)
    {
        $app  = new App([
            'roots' => [
                'index' => __DIR__
            ]
        ]);

        $urls = $app->urls();

        $this->assertEquals($url, $urls->$method());
    }

    public function customBaseUrlProvider(): array
    {
        return [
            ['https://getkirby.com',       'index'],
            ['https://getkirby.com/media', 'media'],
        ];
    }

    /**
     * @dataProvider customBaseUrlProvider
     */
    public function testWithCustomBaseUrl($url, $method)
    {
        $app = new App([
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);

        $urls = $app->urls();
        $this->assertEquals($url, $urls->$method());
    }

    public function customUrlProvider(): array
    {
        return [
            ['https://getkirby.com',       'index'],
            ['https://cdn.getkirby.com',   'media'],
        ];
    }

    /**
     * @dataProvider customUrlProvider
     */
    public function testWithCustomUrl($url, $method)
    {
        $app = new App([
            'urls' => [
                'index' => 'https://getkirby.com',
                'media' => 'https://cdn.getkirby.com',
            ]
        ]);

        $urls = $app->urls();
        $this->assertEquals($url, $urls->$method());
    }
}
