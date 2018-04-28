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

    public function serverNameProvider()
    {
        return [
            ['localhost', true],
            ['mydomain.local', true],
            ['mydomain.test', true],
            ['mydomain.dev', true],
            ['mydomain.com', false],
        ];
    }

    /**
     * @dataProvider serverNameProvider
     */
    public function testIsLocalWithServerNames($name, $expected)
    {
        $_SERVER['SERVER_NAME'] = $name;

        $system = new System(new App);
        $this->assertEquals($expected, $system->isLocal());

        // reset SERVER_NAME
        $_SERVER['SERVER_NAME'] = null;
    }

    public function serverAddressProvider()
    {
        return [
            ['127.0.0.1', true],
            ['::1', true],
            ['0.0.0.0', true],
            ['1.2.3.4', false],
        ];
    }

    /**
     * @dataProvider serverAddressProvider
     */
    public function testIsLocalWithServerAddresses($address, $expected)
    {
        $_SERVER['SERVER_ADDR'] = $address;

        $system = new System(new App);
        $this->assertEquals($expected, $system->isLocal());

        // reset SERVER_ADDR
        $_SERVER['SERVER_ADDR'] = null;
    }

}
