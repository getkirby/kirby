<?php

namespace Kirby\Cms;

use Kirby\Cms\App as Kirby;

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

    public function testJsHelper()
    {
        $result   = js('assets/js/index.js');
        $expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

}
