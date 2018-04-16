<?php

namespace Kirby\Cms;

use Exception;

class SystemTest extends TestCase
{

    public function serverProvider()
    {
        return [
            ['apache', true],
            ['Apache', true],
            ['nginx', true],
            ['Nginx', true],
            ['caddy', true],
            ['Caddy', true],
            ['iis', false],
            ['something', false],
        ];
    }

    /**
     * @dataProvider serverProvider
     */
    public function testServer($software, $expected)
    {
        $_SERVER['SERVER_SOFTWARE'] = $software;

        $system = new System(null);
        $server = $system->server();

        $this->assertEquals($expected, $server);
    }

}
