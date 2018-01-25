<?php

namespace Kirby\Cms;

class UrlsTest extends TestCase
{

    public function defaultUrlProvider(): array
    {
        return [
            ['/',      'index'],
            ['/media', 'media'],
            ['/panel', 'panel'],
            ['/api',   'api'],
        ];
    }

    /**
     * @dataProvider defaultUrlProvider
     */
    public function testDefaulUrl($url, $method)
    {
        $urls = new Urls();

        $this->assertEquals($url, $urls->$method());
    }

    public function customBaseUrlProvider(): array
    {
        return [
            ['https://getkirby.com',       'index'],
            ['https://getkirby.com/media', 'media'],
            ['https://getkirby.com/panel', 'panel'],
            ['https://getkirby.com/api',   'api'],
        ];
    }

    /**
     * @dataProvider customBaseUrlProvider
     */
    public function testWithCustomBaseUrl($url, $method)
    {
        $urls = new Urls([
            'index' => 'https://getkirby.com'
        ]);

        $this->assertEquals($url, $urls->$method());
    }

    public function customUrlProvider(): array
    {
        return [
            ['https://getkirby.com',       'index'],
            ['https://cdn.getkirby.com',   'media'],
            ['https://getkirby.com/admin', 'panel'],
            ['https://getkirby.com/rest',  'api'],
        ];
    }

    /**
     * @dataProvider customUrlProvider
     */
    public function testWithCustomUrl($url, $method)
    {

        $urls = new Urls([
            'index' => 'https://getkirby.com',
            'media' => 'https://cdn.getkirby.com',
            'panel' => 'https://getkirby.com/admin',
            'api'   => 'https://getkirby.com/rest'
        ]);

        $this->assertEquals($url, $urls->$method());
    }


}
