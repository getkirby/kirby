<?php

namespace Kirby\Cms;

use Kirby\Http\Server;

/**
 * @coversDefaultClass \Kirby\Cms\Environment
 */
class EnvironmentTest extends TestCase
{
    protected $_SERVER = null;
    protected $config = null;

    public function setUp(): void
    {
        $this->config = __DIR__ . '/fixtures';
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
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testAllowFromInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, Server::HOST_FROM_HEADER);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testAllowFromInsecureForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';

        $env = new Environment($this->config, Server::HOST_FROM_HEADER);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromString
     * @covers ::url
     */
    public function testAllowFromRelativeUrl()
    {
        $env = new Environment($this->config, '/');

        $this->assertSame('/', $env->url());
        $this->assertNull($env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromString
     * @covers ::url
     */
    public function testAllowFromRelativeUrlWithSubfolder()
    {
        $env = new Environment($this->config, '/subfolder');

        $this->assertSame('/subfolder', $env->url());
        $this->assertNull($env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testAllowFromServerName()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';

        $env = new Environment($this->config, Server::HOST_FROM_SERVER);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromString
     * @covers ::url
     */
    public function testAllowFromUrl()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, 'http://example.com');

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['example.com'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromArray
     * @covers ::url
     */
    public function testAllowFromUrls()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, [
            'http://example.com',
            'http://staging.example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['example.com', 'staging.example.com'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromArray
     * @covers ::url
     */
    public function testAllowFromUrlsWithSubfolders()
    {
        Server::$cli = false;

        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/path-a/index.php';

        $env = new Environment($this->config, [
            'http://localhost/path-a',
            'http://localhost/path-b'
        ]);

        $this->assertSame('http://localhost/path-a', $env->url());
        $this->assertSame('localhost', $env->host());
        $this->assertSame(['localhost'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromArray
     * @covers ::url
     */
    public function testAllowFromUrlsWithSlash()
    {
        Server::$cli = false;

        $_SERVER['SERVER_NAME'] = 'getkirby.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $env = new Environment($this->config, [
            'http://getkirby.com/',
        ]);

        $this->assertSame('http://getkirby.com', $env->url());
        $this->assertSame('getkirby.com', $env->host());
        $this->assertSame(['getkirby.com'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testDisallowFromInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid host setup. The detected host is not allowed.');

        new Environment($this->config, Server::HOST_FROM_SERVER);
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testDisallowFromInsecureForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid host setup. The detected host is not allowed.');

        new Environment($this->config, Server::HOST_FROM_SERVER);
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromArray
     * @covers ::url
     */
    public function testDisallowFromInvalidSubfolders()
    {
        Server::$cli = false;

        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/path-a/index.php';

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The subfolder is not in the allowed base URL list');

        new Environment($this->config, [
            'http://localhost/path-b',
            'http://localhost/path-c'
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromArray
     * @covers ::url
     */
    public function testDisallowFromUnkownInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid host setup. The detected host is not allowed.');

        new Environment($this->config, ['http://example.de']);
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidAllowList()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid allow list setup for base URLs');

        new Environment($this->config, new \stdClass());
    }

    /**
     * @covers ::options
     */
    public function testOptions()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';

        $env = new Environment($this->config);

        $this->assertSame('test option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromServerAddress()
    {
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';

        $env = new Environment($this->config);

        $this->assertSame('test address option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromInvalidHost()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';

        $env = new Environment($this->config, 'http://example.de');

        $this->assertSame([], $env->options());
    }
}
