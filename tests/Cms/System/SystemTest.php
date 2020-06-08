<?php

namespace Kirby\Cms;

use Kirby\Cms\System\RemoteMock;
use Kirby\Toolkit\Dir;
use ReflectionClass;
use ReflectionProperty;

/**
 * @coversDefaultClass Kirby\Cms\System
 */
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

        RemoteMock::$mockContent = '';
        RemoteMock::$mockCode = 200;
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

    /**
     * @covers ::updateStatus
     */
    public function testUpdateStatus()
    {
        $versionProperty = new ReflectionProperty('Kirby\Cms\App', 'version');
        $versionProperty->setAccessible(true);
        $versionProperty->setValue(null, '3.4.5');

        $data = [
            'latest'    => '3.4.5',
            'latestUrl' => 'https://kirby.test/release/3.4.5',
            'incidents' => []
        ];
        RemoteMock::$mockContent = json_encode($data);

        $app = $this->app->clone([
            'options' => [
                'cache.system' => false
            ]
        ]);
        $app->impersonate('kirby');
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');

        // latest version
        $status = $system->updateStatus();
        $this->assertSame('ok', $status['status']);
        $this->assertNull($status['severity']);
        $this->assertSame('3.4.5', $status['current']);
        $this->assertSame('3.4.5', $status['latest']);
        $this->assertSame('https://kirby.test/release/3.4.5', $status['latestUrl']);
        $this->assertSame([], $status['incidents']);

        // outdated version
        $data['latest'] = '3.4.6';
        RemoteMock::$mockContent = json_encode($data);
        $status = $system->updateStatus();
        $this->assertSame('outdated', $status['status']);
        $this->assertNull($status['severity']);
        $this->assertSame('3.4.5', $status['current']);
        $this->assertSame('3.4.6', $status['latest']);
        $this->assertSame([], $status['incidents']);

        // incidents
        $data['incidents'] = [
            [
                'affected'    => '>3.0.0 <=3.5.0',
                'description' => 'Incident 1',
                'fixed'       => '3.1.2',
                'severity'    => 'notable'
            ],
            [
                'affected'    => '<=3.1.0',
                'description' => 'Should be filtered out',
                'fixed'       => '3.1.2',
                'severity'    => 'minor'
            ],
            [
                'affected'    => '>3.0.0 invalid-constraint',
                'description' => 'Incident 2',
                'fixed'       => '3.1.2',
                'severity'    => 'minor'
            ],
            [
                'affected'    => '<3.5.0',
                'description' => 'Incident 3',
                'fixed'       => '3.5.0',
                'severity'    => 'invalid'
            ]
        ];
        RemoteMock::$mockContent = json_encode($data);
        $status = $system->updateStatus();
        $this->assertSame('at-risk', $status['status']);
        $this->assertSame('notable', $status['severity']);
        $this->assertSame('3.4.5', $status['current']);
        $this->assertSame('3.4.6', $status['latest']);
        $this->assertSame([
            [
                'affected'    => '>3.0.0 <=3.5.0',
                'description' => 'Incident 1',
                'fixed'       => '3.1.2',
                'severity'    => 'notable'
            ],
            [
                'affected'    => '>3.0.0 invalid-constraint',
                'description' => 'Incident 2',
                'fixed'       => '3.1.2',
                'severity'    => 'minor'
            ],
            [
                'affected'    => '<3.5.0',
                'description' => 'Incident 3',
                'fixed'       => '3.5.0',
                'severity'    => 'invalid'
            ]
        ], $status['incidents']);

        // disabled update option I
        $app = $this->app->clone([
            'options' => [
                'cache.system' => false,
                'update'       => 'manual'
            ]
        ]);
        $app->impersonate('kirby');
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');
        $this->assertNull($system->updateStatus());
        $this->assertIsArray($system->updateStatus(true));

        // disabled update option II
        $app = $this->app->clone([
            'options' => [
                'cache.system' => false,
                'update.kirby' => false
            ]
        ]);
        $app->impersonate('kirby');
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');
        $this->assertFalse($system->updateStatus());
        $this->assertFalse($system->updateStatus(true));

        // disabled update option III
        $app = $this->app->clone([
            'options' => [
                'cache.system' => false,
                'update.kirby' => 'manual'
            ]
        ]);
        $app->impersonate('kirby');
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');
        $this->assertNull($system->updateStatus());
        $this->assertIsArray($system->updateStatus(true));

        // cache
        $app = $this->app->clone([
            'options' => [
                'cache.system' => [
                    'type' => 'memory'
                ]
            ]
        ]);
        $app->impersonate('kirby');
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');
        $app->cache('system')->set('updateStatus', $cached = ['current' => '3.4.5', 'latest' => '3.5.0']);

        // use cached data if the version is the same
        $this->assertSame($cached, $system->updateStatus());

        // always get current data if requested
        $this->assertSame('3.4.6', $system->updateStatus(true)['latest']);

        // the new data should be cached
        $status = $system->updateStatus();
        $this->assertNotSame($cached, $status);
        $this->assertSame('3.4.5', $status['current']);

        // update data if the version has changed
        $versionProperty->setValue(null, '3.4.6');
        $this->assertSame('3.4.6', $system->updateStatus()['current']);
    }

    /**
     * @covers ::updateStatus
     */
    public function testUpdateStatusNoUser()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to do this');

        $system = new System($this->app, 'Kirby\Cms\System\RemoteMock');

        $system->updateStatus();
    }

    /**
     * @covers ::updateStatus
     */
    public function testUpdateStatusNoPermission()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to do this');

        $app = $this->app->clone([
            'user' => 'user@domain.com',
            'users' => [
                ['email' => 'user@domain.com', 'role' => 'editor'],
            ]
        ]);
        $system = new System($app, 'Kirby\Cms\System\RemoteMock');

        $system->updateStatus();
    }
}
