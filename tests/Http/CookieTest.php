<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function testKey()
    {
        $this->assertEquals('KirbyHttpCookieKey', Cookie::$key);
        Cookie::$key = 'KirbyToolkitCookieKey';
        $this->assertEquals('KirbyToolkitCookieKey', Cookie::$key);
    }

    public function testLifetime()
    {
        $this->assertEquals(12345678901, Cookie::lifetime(12345678901));
        $this->assertEquals((600 + time()), Cookie::lifetime(10));
        $this->assertEquals(0, Cookie::lifetime(-10));
    }

    public function testSet()
    {
        Cookie::set('foo', 'bar');
        $this->assertEquals('703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar', $_COOKIE['foo']);
    }

    public function testForever()
    {
        Cookie::forever('forever', 'bar');
        $this->assertEquals('703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar', $_COOKIE['forever']);
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
        $this->assertEquals('some amazing default', Cookie::get('does_not_exist', 'some amazing default'));
        $this->assertEquals($_COOKIE, Cookie::get());
    }

    public function testParse()
    {
        // valid
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // no value
        $_COOKIE['foo'] = '21fdd6d0d6f5b4ac8109e5f2d0c3f0f7e8e89492+';
        $this->assertEquals('', Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // value with a plus sign
        $_COOKIE['foo'] = '9c8c403efa31d4e4598d75e9c394b48255b65154+bar+baz';
        $this->assertEquals('bar+baz', Cookie::get('foo'));

        // separator missing
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85';
        $this->assertEquals(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // no hash
        $_COOKIE['foo'] = '+bar';
        $this->assertEquals(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertEquals('bar', Cookie::get('foo'));

        // wrong hash
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertEquals(null, Cookie::get('foo'));
    }
}
