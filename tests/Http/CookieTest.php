<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{

    public function testSalt()
    {
        $this->assertEquals('KirbyHttpCookieSalt', Cookie::$salt);
        Cookie::$salt = 'KirbyToolkitCookieSalt';
        $this->assertEquals('KirbyToolkitCookieSalt', Cookie::$salt);
    }

    public function testLifetime()
    {
        $this->assertEquals((600 + time()), Cookie::lifetime(10));
        $this->assertEquals(0, Cookie::lifetime(-10));
    }

    public function testSet()
    {
        Cookie::set('foo', 'bar');
        $this->assertEquals('040df854f89c9f9ca3490fb950c91ad9aa304c97+bar', $_COOKIE['foo']);
    }

    public function testForever()
    {
        Cookie::forever('forever', 'bar');
        $this->assertEquals('040df854f89c9f9ca3490fb950c91ad9aa304c97+bar', $_COOKIE['forever']);
        $this->assertTrue(Cookie::exists('forever'));
    }

    public function testRemove()
    {
        $this->assertTrue(Cookie::remove('forever'));
        $this->assertFalse(isset($_COOKIE['forever']));
        $this->assertFalse(Cookie::remove('none'));
    }

    public function testExists()
    {
        $this->assertTrue(Cookie::exists('foo'));
        $this->assertFalse(Cookie::exists('new'));
    }

    public function testGet()
    {
        $this->assertEquals('bar', Cookie::get('foo'));
        $this->assertEquals($_COOKIE, Cookie::get());
    }

    public function testParse()
    {
        // valid
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // separator missing
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97';
        $this->assertEquals(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // no value
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+';
        $this->assertEquals(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // no hash
        $_COOKIE['foo'] = '+bar';
        $this->assertEquals(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // wrong hash
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb958c91ad9aa304c97+bar';
        $this->assertEquals(null, Cookie::get('foo'));
    }
}
