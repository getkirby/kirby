<?php

namespace Kirby\Cms;

use Kirby\Http\Server;
use Kirby\Http\Uri;

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
            'roots' => [
                'index' => '/dev/null'
            ],
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
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com',
                'media' => 'https://cdn.getkirby.com',
            ]
        ]);

        $urls = $app->urls();
        $this->assertEquals($url, $urls->$method());
    }

    public function testCurrent()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->assertEquals('/', $app->url('current'));
    }

    public function testCurrentInSubfolderSetup()
    {
        $server = $_SERVER;

        // remove any cached uri object
        Uri::$current = null;

        // if cli detection is activated the index url detection
        // will fail and fall back to /
        Server::$cli = false;

        // no additional path
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/starterkit/index.php';

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->assertEquals('http://localhost/starterkit', $app->url('index'));
        $this->assertEquals('http://localhost/starterkit', $app->url('current'));

        // additional path
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/starterkit/sub/folder';
        $_SERVER['SCRIPT_NAME'] = '/starterkit/index.php';

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->assertEquals('http://localhost/starterkit', $app->url('index'));
        $this->assertEquals('http://localhost/starterkit/sub/folder', $app->url('current'));

        $_SERVER = $server;
        Server::$cli = true;
        Uri::$current = null;
    }

    public function testCurrentWithCustomIndex()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'http://getkirby.com'
            ]
        ]);

        $this->assertEquals('http://getkirby.com', $app->url('current'));
    }

    public function testCurrentWithCustomPath()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'path' => 'test/path'
        ]);

        $this->assertEquals('/test/path', $app->url('current'));
    }

    public function testCurrentWithCustomPathAndCustomIndex()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'http://getkirby.com',
            ],
            'path' => 'test/path'
        ]);

        $this->assertEquals('http://getkirby.com/test/path', $app->url('current'));
    }
}
