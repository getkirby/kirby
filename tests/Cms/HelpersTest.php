<?php

namespace Kirby\Cms;

use Kirby\Cms\App as Kirby;
use Kirby\Http\Server;

class HelpersTest extends TestCase
{

    public function setUp()
    {
        $kirby = new Kirby([
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);
    }

    public function testCollectionHelper()
    {
        $app = new App([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'collections' => [
                'test' => function ($pages) {
                    return $pages;
                }
            ]
        ]);

        $collection = collection('test');

        $this->assertCount(1, $collection);
        $this->assertEquals('test', $collection->first()->slug());
    }

    public function testCssHelper()
    {
        $result   = css('assets/css/index.css');
        $expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

        $this->assertEquals($expected, $result);
    }

    public function testCssHelperWithMediaOption()
    {
        $result   = css('assets/css/index.css', 'print');
        $expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

        $this->assertEquals($expected, $result);
    }

    public function testCssHelperWithAttrs()
    {
        $result   = css('assets/css/index.css', ['integrity' => 'nope']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

        $this->assertEquals($expected, $result);
    }

    public function testDumpHelperOnCli()
    {
        $this->assertEquals("test\n", dump('test', false));
    }

    public function testDumpHelperOnServer()
    {
        Server::$cli = false;
        $this->assertEquals("<pre>test</pre>", dump('test', false));
        Server::$cli = null;
    }

    public function testJsHelper()
    {
        $result   = js('assets/js/index.js');
        $expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testJsHelperWithAsyncOption()
    {
        $result   = js('assets/js/index.js', true);
        $expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testJsHelperWithAttrs()
    {
        $result   = js('assets/js/index.js', ['integrity' => 'nope']);
        $expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

}
