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

    public function testPort()
    {
        $this->assertIsInt(Server::port());
        $this->assertEquals(0, Server::port());

        // SERVER_PORT
        $_SERVER['SERVER_PORT'] = 777;
        $this->assertEquals(777, Server::port());
    }

    public function testForwardedPort()
    {
        // HTTP_X_FORWARDED_PORT
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 999;
        $this->assertEquals(999, Server::port(true));
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

    public function testHost()
    {
        $this->assertEquals('', Server::host());

        // SERVER_NAME
        $_SERVER['SERVER_NAME'] = 'foo';
        $this->assertEquals('foo', Server::host());

        // SERVER_ADDR

        // remove the server name to fall back on the address
        unset($_SERVER['SERVER_NAME']);

        // set the address
        $_SERVER['SERVER_ADDR'] = 'bar';

        $this->assertEquals('bar', Server::host());
    }

    public function testForwardedHost()
    {
        // HTTP_X_FORWARDED_HOST
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'kirby';
        $this->assertEquals('kirby', Server::host(true));
    }
}
