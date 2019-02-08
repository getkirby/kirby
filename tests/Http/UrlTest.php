<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    protected $_yt   = 'http://www.youtube.com/watch?v=9q_aXttJduk';
    protected $_yts  = 'https://www.youtube.com/watch?v=9q_aXttJduk';
    protected $_docs = 'http://getkirby.com/docs/';
    protected $_SERVER = null;

    public function setUp(): void
    {
        Uri::$current = null;
        Url::$current = null;
        Url::$home    = '/';

        $this->_SERVER = $_SERVER;
    }

    public function tearDown(): void
    {
        $_SERVER = $this->_SERVER;
    }

    public function testCurrent()
    {
        $this->assertEquals('/', Url::current());

        Url::$current = $this->_yts;
        $this->assertEquals($this->_yts, Url::current());
    }

    public function testCurrentDir()
    {
        Url::$current = $this->_yts;
        $this->assertEquals('https://www.youtube.com', Url::currentDir());
    }

    public function testHome()
    {
        $this->assertEquals('/', Url::home());
    }

    public function testTo()
    {
        $this->assertEquals('/', Url::to());
        $this->assertEquals($this->_yt, Url::to($this->_yt));
        $this->assertEquals('/getkirby.com', Url::to('getkirby.com'));
    }

    public function testLast()
    {
        $this->assertEquals('', Url::last());
    }

    public function testBuild()
    {
        $this->assertEquals('/', Url::build());

        Url::$current = $this->_yts;

        // build with defaults
        $this->assertEquals('https://www.youtube.com/watch?v=9q_aXttJduk', Url::build());

        // build with different host
        $this->assertEquals('https://kirbyvideo.com/watch?v=9q_aXttJduk', Url::build(['host' => 'kirbyvideo.com']));

        // build from parts
        $parts = [
            'path'     => ['hello', 'kitty', 'mickey', 'mouse'],
            'query'    => ['get' => 'kirby'],
            'fragment' => 'foo'
        ];
        $result = 'http://getkirby.com/hello/kitty/mickey/mouse?get=kirby#foo';
        $this->assertEquals($result, Url::build($parts, 'http://getkirby.com'));
    }

    public function testIsAbsolute()
    {
        $this->assertTrue(Url::isAbsolute('http://getkirby.com/docs'));
        $this->assertTrue(Url::isAbsolute('https://getkirby.com/docs'));
        $this->assertTrue(Url::isAbsolute('//getkirby.com/docs'));
        $this->assertTrue(Url::isAbsolute('mailto:mail@getkirby.com'));
        $this->assertTrue(Url::isAbsolute('tel:1234567'));
        $this->assertFalse(Url::isAbsolute('../getkirby.com/docs'));
    }

    public function testMakeAbsolute()
    {
        $this->assertEquals('http://getkirby.com', Url::makeAbsolute('http://getkirby.com'));
        $this->assertEquals('/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet'));
        $this->assertEquals('http://getkirby.com/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet', 'http://getkirby.com'));
        $this->assertEquals('http://getkirby.com', Url::makeAbsolute('', 'http://getkirby.com'));
    }

    public function testFix()
    {
        $this->assertEquals('http://getkirby.com', Url::fix('getkirby.com'));
        $this->assertEquals('ftp://getkirby.com', Url::fix('ftp://getkirby.com'));
    }

    public function testBase()
    {
        $this->assertEquals(null, Url::base());
        $this->assertEquals('http://getkirby.com', Url::base('http://getkirby.com/docs/cheatsheet'));
    }

    public function testShort()
    {
        $this->assertEquals('getkirby.com/docs', Url::short($this->_docs));
        $this->assertEquals('getkirby.com/docs', Url::short($this->_docs, 100));
        $this->assertEquals('getkirby.com…', Url::short($this->_docs, 12));
        $this->assertEquals('getkirby.com', Url::short($this->_docs, 20, true));
        $this->assertEquals('getkirby.com###', Url::short($this->_docs, 12, false, '###'));
    }

    public function testIdn()
    {
        $this->assertEquals('https://täst.de', Url::idn('https://xn--tst-qla.de'));
    }

    public function scriptNameProvider()
    {
        return [
            [null, 'index.php', '/'],
            [null, '/index.php', '/'],
            [null, '', '/'],
            [null, '/', '/'],
            [null, '/kirby/index.php', '/kirby'],
            [null, 'kirby/index.php', '/kirby'],
            [null, '/kirby/super.php', '/kirby'],
            [null, 'kirby/super.php', '/kirby'],
            [null, 'kirby\super.php', '/kirby'],

            ['localhost', 'index.php', 'http://localhost'],
            ['localhost', '/index.php', 'http://localhost'],
            ['localhost', '', 'http://localhost'],
            ['localhost', '/', 'http://localhost'],
            ['localhost', '/kirby/index.php', 'http://localhost/kirby'],
            ['localhost', 'kirby/index.php', 'http://localhost/kirby'],
            ['localhost', '/kirby/super.php', 'http://localhost/kirby'],
            ['localhost', 'kirby/super.php', 'http://localhost/kirby'],
            ['localhost', 'kirby\super.php', 'http://localhost/kirby'],
        ];
    }

    /**
     * @dataProvider scriptNameProvider
     */
    public function testIndex($host, $scriptName, $expected)
    {
        $_SERVER['SERVER_NAME'] = $host;
        $_SERVER['SCRIPT_NAME'] = $scriptName;

        // overwrite cli detection
        Server::$cli = false;

        $this->assertEquals($expected, Url::index());

        Server::$cli = null;
    }
}
