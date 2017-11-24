<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{

    public function testGet()
    {
        $this->assertInternalType('array', Server::get());
        $this->assertInternalType('string', Server::get('SERVER_ADDR'));
    }

    public function testPort()
    {
        $this->assertInternalType('int', Server::port());
        $this->assertEquals(0, Server::port());

        // SERVER_PORT
        $_SERVER['SERVER_PORT'] = 777;
        $this->assertEquals(777, Server::port());
        unset($_SERVER['SERVER_PORT']);

        // HTTP_X_FORWARDED_PORT
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 999;
        $this->assertEquals(999, Server::port());
        unset($_SERVER['HTTP_X_FORWARDED_PORT']);
    }

    public function testHttps()
    {
        $this->assertFalse(Server::https());

        // $_SERVER['HTTPS']
        $_SERVER['HTTPS'] = 'https';
        $this->assertTrue(Server::https());
        unset($_SERVER['HTTPS']);

        // Port 443
        $_SERVER['SERVER_PORT'] = 443;
        $this->assertTrue(Server::https());
        unset($_SERVER['SERVER_PORT']);

        // HTTP_X_FORWARDED_PROTO = https
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(Server::https());
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    public function testHost()
    {
        $this->assertEquals('', Server::host());

        // HTTP_X_FORWARDED_HOST
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'kirby';
        $this->assertEquals('kirby', Server::host());
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);

        // SERVER_NAME
        $_SERVER['SERVER_NAME'] = 'foo';
        $this->assertEquals('foo', Server::host());
        unset($_SERVER['SERVER_NAME']);

        // SERVER_ADDR
        $_SERVER['SERVER_ADDR'] = 'bar';
        $this->assertEquals('bar', Server::host());
        unset($_SERVER['SERVER_ADDR']);
    }
}
