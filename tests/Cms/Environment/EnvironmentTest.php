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
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, true);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromInsecureForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';

        $env = new Environment($this->config, true);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromServerName()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';

        $env = new Environment($this->config, false);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::host
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
     * @covers ::host
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
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, false);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromInsecureForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';

        $env = new Environment($this->config, false);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame([], Server::hosts());
    }

    /**
     * @covers ::__construct
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromUnkownInsecureHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $env = new Environment($this->config, ['http://example.de']);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame(['example.de'], Server::hosts());
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
