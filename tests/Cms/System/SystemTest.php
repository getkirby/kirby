<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use ReflectionClass;

class SystemTest extends TestCase
{
    protected $_SERVER = null;
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/SystemTest'
            ]
        ]);

        $this->_SERVER = $_SERVER;
    }

    public function tearDown(): void
    {
        $_SERVER = $this->_SERVER;

        Dir::remove($this->fixtures);
    }

    public function serverProvider()
    {
        return [
            ['apache', true],
            ['Apache', true],
            ['nginx', true],
            ['Nginx', true],
            ['caddy', true],
            ['Caddy', true],
            ['iis', false],
            ['something', false],
        ];
    }

    /**
     * @dataProvider serverProvider
     */
    public function testServer($software, $expected)
    {
        $_SERVER['SERVER_SOFTWARE'] = $software;

        $system = new System($this->app);
        $server = $system->server();

        $this->assertEquals($expected, $server);
    }

    public function testServerOverwrite()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'symfony';

        // single server
        $app = $this->app->clone([
            'options' => [
                'servers' => 'symfony'
            ]
        ]);

        $system = new System($app);
        $server = $system->server();

        $this->assertTrue($server);

        // array of servers
        $app = $this->app->clone([
            'options' => [
                'servers' => ['symfony', 'apache']
            ]
        ]);

        $system = new System($app);
        $server = $system->server();

        $this->assertTrue($server);
    }

    public function serverNameProvider()
    {
        return [
            ['localhost', true],
            ['mydomain.local', true],
            ['mydomain.test', true],
            ['mydomain.com', false],
            ['mydomain.dev', false],
        ];
    }

    /**
     * @dataProvider serverNameProvider
     */
    public function testIsLocalWithServerNames($name, $expected)
    {
        $_SERVER['SERVER_NAME'] = $name;

        $system = new System($this->app);
        $this->assertEquals($expected, $system->isLocal());
    }

    public function clientAddressProvider()
    {
        return [
            ['127.0.0.1', '127.0.0.1', true],
            ['::1', '::1', true],
            ['127.0.0.1', '::1', true],
            ['::1', '127.0.0.1', true],
            ['1.2.3.4', '127.0.0.1', false],
            ['127.0.0.1', '1.2.3.4', false],
        ];
    }

    /**
     * @dataProvider clientAddressProvider
     */
    public function testIsLocalWithClientAddresses(string $address, string $forwardedAddress, bool $expected)
    {
        $system = new System($this->app);

        $_SERVER['REMOTE_ADDR'] = $address;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $forwardedAddress;
        $this->assertSame($expected, $system->isLocal());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['HTTP_CLIENT_IP'] = $forwardedAddress;
        $this->assertSame($expected, $system->isLocal());
    }

    public function indexUrlProvider()
    {
        return [
            ['http://getkirby.com', 'getkirby.com'],
            ['https://getkirby.com', 'getkirby.com'],
            ['https://getkirby.com/test', 'getkirby.com/test'],
            ['/', 'example.com'],
            ['/test', 'example.com/test'],
            ['getkirby.com/test', 'example.com/getkirby.com/test'],
        ];
    }

    /**
     * @dataProvider indexUrlProvider
     */
    public function testIndexUrl($indexUrl, $expected)
    {
        $_SERVER['SERVER_ADDR'] = 'example.com';

        $system = new System($this->app->clone([
            'options' => [
                'url' => $indexUrl
            ]
        ]));
        $this->assertEquals($expected, $system->indexUrl($indexUrl));

        // reset SERVER_ADDR
        $_SERVER['SERVER_ADDR'] = null;
    }

    public function licenseUrlProvider()
    {
        return [
            [null, 'getkirby.com'],
            ['example.com', 'example.com'],
            ['www.example.com', 'example.com'],
            ['dev.example.com', 'example.com'],
            ['test.example.com', 'example.com'],
            ['staging.example.com', 'example.com'],
            ['sub.example.com', 'sub.example.com'],
            ['www.example.com/test', 'www.example.com/test'],
            ['dev.example.com/test', 'dev.example.com/test'],
            ['test.example.com/test', 'test.example.com/test'],
            ['staging.example.com/test', 'staging.example.com/test'],
            ['sub.example.com/test', 'sub.example.com/test'],
        ];
    }

    /**
     * @dataProvider licenseUrlProvider
     */
    public function testLicenseUrl($url, $expected)
    {
        $reflector = new ReflectionClass(System::class);
        $licenseUrl = $reflector->getMethod('licenseUrl');
        $licenseUrl->setAccessible(true);

        $system = new System($this->app->clone([
            'options' => [
                'url' => 'https://getkirby.com'
            ]
        ]));
        $this->assertEquals($expected, $licenseUrl->invoke($system, $url));
    }

    public function testIsInstallableOnLocalhost()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $system = new System($this->app);

        $this->assertTrue($system->isInstallable());
    }

    public function testIsInstallableOnPublicServer()
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        $system = new System($this->app);

        $this->assertFalse($system->isInstallable());
    }

    public function testIsInstallableOnPublicServerWithOverride()
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'install' => true
                ]
            ]
        ]);

        $system = new System($app);

        $this->assertTrue($system->isInstallable());
    }

    public function testRegisterWithInvalidLicenseKey()
    {
        $system = new System($this->app);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid license key');

        $system->register('abc');
    }

    public function testRegisterWithInvalidEmail()
    {
        $system = new System($this->app);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid email address');

        $system->register('K3-PRO-abc', 'invalid');
    }
}
