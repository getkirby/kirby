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

    public function testCssHelper()
    {
        $result   = css('assets/css/index.css');
        $expected = '<link rel="stylesheet" href="https://getkirby.com/assets/css/index.css">';

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

}
