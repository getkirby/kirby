<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{

    protected function setUp()
    {
        $this->example1 = 'https://getkirby.com';
        $this->example2 = 'https://testuser:weakpassword@getkirby.com:3000/docs/getting-started/?q=awesome#top';
    }

    public function testOriginal()
    {
        $url = new Url($this->example1);
        $this->assertEquals($url->original(), $this->example1);
    }

    public function testValidScheme()
    {
        $url = new Url;

        $url->scheme('http');
        $this->assertEquals('http', $url->scheme());

        $url->scheme('https');
        $this->assertEquals('https', $url->scheme());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid URL scheme: abc
     */
    public function testInvalidScheme()
    {
        $url = new Url;
        $url->scheme('abc');
    }

    public function testValidHost()
    {
        $url = new Url;

        $url->host('getkirby.com');
        $this->assertEquals('getkirby.com', $url->host());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid host format:
     */
    public function testInvalidHost()
    {
        $url = new Url;
        $url->host(false);
    }

    public function testValidPort()
    {
        $url = new Url;

        $url->port(1234);
        $this->assertEquals(1234, $url->port());

        $url->port(false);
        $this->assertEquals(false, $url->port());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid port format: 0
     */
    public function testInvalidPortFormat1()
    {
        $url = new Url;
        $url->port(0);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid port format: a
     */
    public function testInvalidPortFormat2()
    {
        $url = new Url;
        $url->port('a');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid port format: 12010210210
     */
    public function testInvalidPortFormat3()
    {
        $url = new Url;
        $url->port(12010210210);
    }

    public function testValidUsername()
    {
        $url = new Url;

        $url->username('testuser');
        $this->assertEquals('testuser', $url->username());

        $url->username(false);
        $this->assertEquals(false, $url->username());
    }

    public function testValidPassword()
    {
        $url = new Url;

        $url->password('weakpassword');
        $this->assertEquals('weakpassword', $url->password());

        $url->password(false);
        $this->assertEquals(false, $url->password());
    }

    public function testValidPath()
    {
        $url = new Url;

        $url->path('/a/b/c');
        $this->assertEquals('/a/b/c', $url->path());

        $url->path(false);
        $this->assertEquals(false, $url->path());
    }

    public function testValidQuery()
    {
        $url = new Url;

        $url->query('foo=bar');
        $this->assertEquals('foo=bar', $url->query());

        $url->query('?foo=bar');
        $this->assertEquals('foo=bar', $url->query());

        $url->query(false);
        $this->assertEquals(false, $url->query());
    }

    public function testValidFragment()
    {
        $url = new Url;

        $url->fragment('top');
        $this->assertEquals('top', $url->fragment());

        $url->fragment('#top');
        $this->assertEquals('top', $url->fragment());

        $url->fragment(false);
        $this->assertEquals(false, $url->fragment());
    }

    public function testAuth()
    {
        $url = new Url;
        $url->username('testuser');
        $url->password('weakpassword');

        $this->assertEquals('testuser:weakpassword', $url->auth());
    }

    public function testBase()
    {
        $url = new Url;
        $url->scheme('https');
        $url->host('getkirby.com');

        $this->assertEquals('https://getkirby.com', $url->base());

        $url->username('testuser');
        $url->password('weakpassword');

        $this->assertEquals('https://testuser:weakpassword@getkirby.com', $url->base());

        $url->port(3000);
        $this->assertEquals('https://testuser:weakpassword@getkirby.com:3000', $url->base());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The host address is missing
     */
    public function testBaseWithoutHost()
    {
        $url = new Url;
        $url->base();
    }

    public function testShort()
    {
        $url = new Url($this->example2);
        $this->assertEquals('getkirby.com/docs/getting-started', $url->short());
        $this->assertEquals('getkirby.com/…', $url->short(13));
    }

    public function testToArray()
    {
        $url = new Url($this->example2);

        $this->assertEquals([
            'scheme'   => 'https',
            'host'     => 'getkirby.com',
            'port'     => 3000,
            'path'     => '/docs/getting-started/',
            'username' => 'testuser',
            'password' => 'weakpassword',
            'query'    => 'q=awesome',
            'fragment' => 'top',
        ], $url->toArray());
    }

    public function testToString()
    {
        $url = new Url($this->example1);
        $this->assertEquals($this->example1, $url->toString());
        $this->assertEquals($this->example1, (string)$url);

        $url = new Url($this->example2);
        $this->assertEquals($this->example2, $url->toString());
        $this->assertEquals($this->example2, (string)$url);
    }
}
