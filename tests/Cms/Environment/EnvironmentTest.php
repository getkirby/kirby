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

    public function server(array $server = [])
    {
        $_SERVER = $server;
        return new Server();
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromInsecureHost()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, true);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromInsecureForwardedHost()
    {
        $server = $this->server([
            'HTTP_X_FORWARDED_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, true);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['*'], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromServerName()
    {
        $server = $this->server([
            'SERVER_NAME' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, false);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame([], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromUrl()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, 'http://example.com');

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['example.com'], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testAllowFromUrls()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, [
            'http://example.com',
            'http://staging.example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
        $this->assertSame(['example.com', 'staging.example.com'], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromInsecureHost()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, false);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame([], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromInsecureForwardedHost()
    {
        $server = $this->server([
            'HTTP_X_FORWARDED_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, false);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame([], $server->hosts());
    }

    /**
     * @covers ::host
     * @covers ::url
     */
    public function testDisallowFromUnkownInsecureHost()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, ['http://example.de']);

        $this->assertSame('/', $env->url());
        $this->assertSame('', $env->host());
        $this->assertSame(['example.de'], $server->hosts());
    }

    public function testInvalidAllowList()
    {
        $server = $this->server([
            'HTTP_HOST' => 'example.com',
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid allow list setup for base URLs');

        new Environment($server, $this->config, new \stdClass());
    }

    /**
     * @covers ::options
     */
    public function testOptions()
    {
        $server = $this->server([
            'SERVER_NAME' => 'example.com',
        ]);

        $env = new Environment($server, $this->config);

        $this->assertSame('test option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromServerAddress()
    {
        $server = $this->server([
            'SERVER_ADDR' => '127.0.0.1'
        ]);

        $env = new Environment($server, $this->config);

        $this->assertSame('test address option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromInvalidHost()
    {
        $server = $this->server([
            'SERVER_NAME' => 'example.com',
        ]);

        $env = new Environment($server, $this->config, 'http://example.de');

        $this->assertSame([], $env->options());
    }
}
