<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function testKey()
    {
        $this->assertSame('KirbyHttpCookieKey', Cookie::$key);
        Cookie::$key = 'KirbyToolkitCookieKey';
        $this->assertSame('KirbyToolkitCookieKey', Cookie::$key);
    }

    public function testLifetime()
    {
        $this->assertSame(253402214400, Cookie::lifetime(253402214400));
        $this->assertSame((600 + time()), Cookie::lifetime(10));
        $this->assertSame(0, Cookie::lifetime(-10));
    }

    public function testSet()
    {
        Cookie::set('foo', 'bar');
        $this->assertSame('703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar', $_COOKIE['foo']);
    }

    public function testForever()
    {
        Cookie::forever('forever', 'bar');
        $this->assertSame('703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar', $_COOKIE['forever']);
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
        $this->assertSame('bar', Cookie::get('foo'));
        $this->assertSame('some amazing default', Cookie::get('does_not_exist', 'some amazing default'));
        $this->assertSame($_COOKIE, Cookie::get());
    }

    public function testParse()
    {
        // valid
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertSame('bar', Cookie::get('foo'));

        // no value
        $_COOKIE['foo'] = '21fdd6d0d6f5b4ac8109e5f2d0c3f0f7e8e89492+';
        $this->assertSame('', Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertSame('bar', Cookie::get('foo'));

        // value with a plus sign
        $_COOKIE['foo'] = '9c8c403efa31d4e4598d75e9c394b48255b65154+bar+baz';
        $this->assertSame('bar+baz', Cookie::get('foo'));

        // separator missing
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85';
        $this->assertSame(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertSame('bar', Cookie::get('foo'));

        // no hash
        $_COOKIE['foo'] = '+bar';
        $this->assertSame(null, Cookie::get('foo'));
        $_COOKIE['foo'] = '703a07dc4edca348cb92d9fcb7da1b3931de0a85+bar';
        $this->assertSame('bar', Cookie::get('foo'));

        // wrong hash
        $_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
        $this->assertSame(null, Cookie::get('foo'));
    }
}
