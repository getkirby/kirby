<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    protected $_SERVER = null;

    public function setUp(): void
    {
        $this->_SERVER = $_SERVER;
    }

    public function tearDown(): void
    {
        $_SERVER = $this->_SERVER;
    }

    public function testGet()
    {
        $this->assertIsArray(Server::get());
        $this->assertIsString(Server::get('SERVER_ADDR'));
    }

    public function testHost()
    {
        $this->assertSame('', Server::host());

        // SERVER_NAME
        $_SERVER['SERVER_NAME'] = 'foo';
        $this->assertSame('foo', Server::host());

        // SERVER_ADDR

        // remove the server name to fall back on the address
        unset($_SERVER['SERVER_NAME']);

        // set the address
        $_SERVER['SERVER_ADDR'] = 'bar';

        $this->assertSame('bar', Server::host());
    }

    public function testHostForwarded()
    {
        // HTTP_X_FORWARDED_HOST
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'kirby';
        $this->assertSame('kirby', Server::host(true));
    }

    public function testHttps()
    {
        $this->assertFalse(Server::https());

        // $_SERVER['HTTPS']
        $_SERVER['HTTPS'] = 'https';
        $this->assertTrue(Server::https());

        // Port 443
        $_SERVER['SERVER_PORT'] = 443;
        $this->assertTrue(Server::https());

        // HTTP_X_FORWARDED_PROTO = https
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(Server::https());
    }

    public function testPort()
    {
        // SERVER_PORT
        $_SERVER['SERVER_PORT'] = 777;
        $this->assertSame(777, Server::port());

        // HTTP_HOST
        $_SERVER['HTTP_HOST'] = 'localhost:776';
        $this->assertSame(776, Server::port());
    }

    public function testPortOnCli()
    {
        $this->assertSame(0, Server::port());
    }

    public function testPortForwarded()
    {
        // HTTP_X_FORWARDED_PORT
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 999;
        $this->assertSame(999, Server::port(true));
    }

    public function provideRequestUri(): array
    {
        return [
            [
                null,
                [
                    'path'  => '',
                    'query' => null
                ]
            ],
            [
                '/',
                [
                    'path'  => '/',
                    'query' => null
                ]
            ],
            [
                '/foo/bar',
                [
                    'path'  => '/foo/bar',
                    'query' => null
                ]
            ],
            [
                '/foo/bar?foo=bar',
                [
                    'path'  => '/foo/bar',
                    'query' => 'foo=bar'
                ]
            ],
            [
                'index.php?foo=bar',
                [
                    'path'  => 'index.php',
                    'query' => 'foo=bar'
                ]
            ],
            [
                'https://getkirby.com/foo/bar?foo=bar',
                [
                    'path'  => '/foo/bar',
                    'query' => 'foo=bar'
                ]
            ]
        ];
    }

    /**
     * @dataProvider provideRequestUri
     */
    public function testRequestUri($input, $expected)
    {
        $_SERVER['REQUEST_URI'] = $input;
        $this->assertSame($expected, Server::requestUri());
    }

}
