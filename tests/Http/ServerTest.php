<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Http\Server
 */
class ServerTest extends TestCase
{
    protected $_SERVER = null;

    public function setUp(): void
    {
        $this->_SERVER = $_SERVER;
        Server::$hosts = [];
        Server::$cli = null;
    }

    public function tearDown(): void
    {
        $_SERVER = $this->_SERVER;
        Server::$hosts = [];
        Server::$cli = null;
    }

    /**
     * @covers ::address
     */
    public function testAddress()
    {
        $_SERVER['SERVER_ADDR'] = $ip = '127.0.0.1';
        $this->assertSame($ip, Server::address());
    }

    /**
     * @covers ::address
     */
    public function testAddressOnCli()
    {
        $this->assertSame('', Server::address());
    }

    /**
     * @covers ::cli
     */
    public function testCli()
    {
        $this->assertTrue(Server::cli());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $_SERVER['TEST'] = 'foo';
        $this->assertSame('foo', Server::get('test'));
        $this->assertSame('foo', Server::get('TEST'));
    }

    /**
     * @covers ::get
     */
    public function testGetAll()
    {
        $this->assertSame($_SERVER, Server::get());
    }

    /**
     * @covers ::get
     */
    public function testGetFallback()
    {
        $this->assertSame('foo', Server::get('test', 'foo'));
    }

    /**
     * @covers ::host
     */
    public function testHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Server::$hosts = ['*'];
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostFromServerAddress()
    {
        $_SERVER['SERVER_ADDR'] = 'example.com';
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostFromServerName()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostFromProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';
        Server::$hosts = ['*'];
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostAllowlistExcluded()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Server::$hosts = ['getkirby.com'];
        $this->assertSame('', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostAllowlistIncluded()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Server::$hosts = ['example.com'];
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostAllowlistWildcard()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        Server::$hosts = ['*'];
        $this->assertSame('example.com', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostOnCli()
    {
        $this->assertSame('', Server::host());
    }

    /**
     * @covers ::host
     */
    public function testHostWithPort()
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8888';
        Server::$hosts = ['*'];
        $this->assertSame('example.com', Server::host());
    }

    public function provideHttps()
    {
        return [
            [null, false],
            ['', false],
            ['0', false],
            [0, false],
            ['false', false],
            [false, false],
            ['off', false],
            [true, true],
            ['1', true],
            ['on', true],
            ['https', true],
        ];
    }

    /**
     * @dataProvider provideHttps
     * @covers ::https
     */
    public function testHttpsFromHeader($input, $expected)
    {
        $_SERVER['HTTPS'] = $input;
        $this->assertSame($expected, Server::https());
    }

    /**
     * @covers ::https
     */
    public function testHttpsFromPort()
    {
        $_SERVER['SERVER_PORT'] = 443;
        $this->assertTrue(Server::https());
    }

    /**
     * @covers ::https
     */
    public function testHttpsFromForwardedPort()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 443;
        $this->assertTrue(Server::https());

        // HTTP_X_FORWARDED_PROTO = https
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(Server::https());
    }

    /**
     * @covers ::https
     */
    public function testHttpsFromForwardedProto()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST']  = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(Server::https());
    }

    public function provideIsAllowedHost()
    {
        return [
            [
                'getkirby.com',
                [],
                true
            ],
            [
                'getkirby.com',
                ['*'],
                true
            ],
            [
                'getkirby.com',
                ['*getkirby.com'],
                true
            ],
            [
                'chat.getkirby.de',
                ['*.getkirby.*'],
                true
            ],
            [
                'getkirby.com',
                ['getkirby.de'],
                false
            ],
            [
                'getkirby.com',
                ['getkirby.de', 'trykirby.com'],
                false
            ]
        ];
    }

    /**
     * @covers ::isAllowedHost
     * @dataProvider provideIsAllowedHost
     */
    public function testIsAllowedHost($input, $hosts, $expected)
    {
        Server::$hosts = $hosts;
        $this->assertSame($expected, Server::isAllowedHost($input));
    }

    /**
     * @covers ::isBehindProxy
     */
    public function testIsBehindProxy()
    {
        $this->assertFalse(Server::isBehindProxy());
    }

    /**
     * @covers ::port
     */
    public function testPortFromHost()
    {
        // HTTP_HOST
        $_SERVER['HTTP_HOST'] = 'localhost:8888';
        $this->assertSame(8888, Server::port());
    }

    /**
     * @covers ::port
     */
    public function testPortFromProxyHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com:8888';
        $this->assertSame(8888, Server::port());
    }

    /**
     * @covers ::port
     */
    public function testPortFromProxyPort()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 8888;
        $this->assertSame(8888, Server::port());
    }

    /**
     * @covers ::port
     */
    public function testPortFromProxyProto()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertSame(443, Server::port());
    }

    /**
     * @covers ::port
     */
    public function testPortFromServer()
    {
        // SERVER_PORT
        $_SERVER['SERVER_PORT'] = 777;
        $this->assertSame(777, Server::port());
    }

    /**
     * @covers ::port
     */
    public function testPortOnCli()
    {
        $this->assertSame(0, Server::port());
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
     * @covers ::requestUri
     */
    public function testRequestUri($input, $expected)
    {
        $_SERVER['REQUEST_URI'] = $input;
        $this->assertSame($expected, Server::requestUri());
    }

    public function provideSanitize()
    {
        return [
            [
                'HTTP_HOST',
                '<script>foo()</script>',
                'foo'
            ],
            [
                'HTTP_X_FORWARDED_HOST',
                '<script>foo()</script>',
                'foo'
            ],
            [
                'SERVER_PORT',
                'abc9999',
                9999
            ],
            [
                'HTTP_X_FORWARDED_PORT',
                'abc9999',
                9999
            ]
        ];
    }

    /**
     * @dataProvider provideSanitize
     */
    public function testSanitize($key, $value, $expected)
    {
        $this->assertSame($expected, Server::sanitize($key, $value));
    }

    public function provideScriptPath()
    {
        return [
            [
                null,
                ''
            ],
            [
                '',
                ''
            ],
            [
                '/index.php',
                ''
            ],
            [
                '/subfolder/index.php',
                'subfolder'
            ],
            [
                '/subfolder/test.php',
                'subfolder'
            ],
            [
                '\subfolder\subsubfolder\index.php',
                'subfolder/subsubfolder'
            ],
        ];
    }

    /**
     * @dataProvider provideScriptPath
     * @covers ::scriptPath
     */
    public function testScriptPath($scriptName, $expected)
    {
        $_SERVER['SCRIPT_NAME'] = $scriptName;
        // switch off cli detection to simulate
        // script path detection on the server
        Server::$cli = false;
        $this->assertSame($expected, Server::scriptPath());
    }

    /**
     * @covers ::scriptPath
     */
    public function testScriptPathOnCli()
    {
        $this->assertSame('', Server::scriptPath());
    }
}
