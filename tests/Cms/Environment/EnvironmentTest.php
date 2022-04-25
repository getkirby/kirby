<?php

namespace Kirby\Cms;

use Kirby\Http\Server;

/**
 * @coversDefaultClass \Kirby\Cms\Environment
 */
class EnvironmentTest extends TestCase
{
    protected $config = __DIR__ . '/fixtures';

    /**
     * @covers ::__construct
     * @covers ::blockEmptyHost
     * @covers ::host
     * @covers ::setupFromFlag
     * @covers ::url
     */
    public function testAllowFromInsecureHost()
    {
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => Server::HOST_FROM_HEADER
        ], [
            'HTTP_HOST' => 'example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => Server::HOST_FROM_HEADER
        ], [
            'HTTP_X_FORWARDED_HOST' => 'example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => '/'
        ]);

        $this->assertSame('/', $env->url());
        $this->assertNull($env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => '/subfolder'
        ]);

        $this->assertSame('/subfolder', $env->url());
        $this->assertNull($env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => Server::HOST_FROM_SERVER
        ], [
            'SERVER_NAME' => 'example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => 'http://example.com'
        ], [
            'HTTP_HOST' => 'example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => [
                'http://example.com',
                'http://staging.example.com'
            ]
        ], [
            'HTTP_HOST' => 'example.com'
        ]);

        $this->assertSame('http://example.com', $env->url());
        $this->assertSame('example.com', $env->host());
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
        $env = new Environment([
            'cli'     => false,
            'root'    => $this->config,
            'allowed' => [
                'http://localhost/path-a',
                'http://localhost/path-b'
            ]
        ], [
            'HTTP_HOST'   => 'localhost',
            'SCRIPT_NAME' => '/path-a/index.php'
        ]);

        $this->assertSame('http://localhost/path-a', $env->url());
        $this->assertSame('localhost', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => [
                'http://getkirby.com/',
            ]
        ], [
            'SERVER_NAME' => 'getkirby.com',
            'SCRIPT_NAME' => '/index.php'
        ]);

        $this->assertSame('http://getkirby.com', $env->url());
        $this->assertSame('getkirby.com', $env->host());
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
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => Server::HOST_FROM_SERVER
        ], [
            'HTTP_HOST' => 'example.com'
        ]);

        $this->assertNull($env->host());
    }

    public function testIgnoreFromInsecureForwardedHost()
    {
        $env = new Environment([
            'root'    => $this->config,
            'allowed' => Server::HOST_FROM_SERVER
        ], [
            'HTTP_X_FORWARDED_HOST' => 'example.com'
        ]);

        $this->assertNull($env->host());
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
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The environment is not allowed');

        new Environment([
            'root'    => $this->config,
            'allowed' => [
                'http://localhost/path-b',
                'http://localhost/path-c'
            ]
        ], [
            'HTTP_HOST'   => 'localhost',
            'SCRIPT_NAME' => '/path-a/index.php'
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidAllowList()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid allow list setup for base URLs');

        new Environment([
            'root'    => $this->config,
            'allowed' => new \stdClass()
        ], [
            'HTTP_HOST' => 'example.com'
        ]);
    }

    /**
     * @covers ::options
     */
    public function testOptions()
    {
        $env = new Environment([
            'root' => $this->config,
        ], [
            'SERVER_NAME' => 'example.com'
        ]);

        $this->assertSame('test option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromServerAddress()
    {
        $env = new Environment([
            'root' => $this->config,
        ], [
            'SERVER_ADDR' => '127.0.0.1'
        ]);

        $this->assertSame('test address option', $env->options()['test']);
    }

    /**
     * @covers ::options
     */
    public function testOptionsFromInvalidHost()
    {
        $env = new Environment([
            'root' => $this->config,
            'allowed' => [
                'http://example.de'
            ]
        ], [
            'SERVER_NAME' => 'example.com'
        ]);

        $this->assertSame([], $env->options());
    }
}
