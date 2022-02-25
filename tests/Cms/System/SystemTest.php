<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use ReflectionClass;

/**
 * @coversDefaultClass Kirby\Cms\System
 */
class SystemTest extends TestCase
{
    protected $_SERVER = null;
    protected $app;
    protected $fixtures;
    protected $subFixtures;

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

        if ($this->subFixtures !== null) {
            chmod($this->subFixtures, 0755);
            Dir::remove($this->subFixtures);
        }

        Dir::remove($this->fixtures);
    }

    public function providerForClientAddresses()
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

    public function providerForIndexUrls()
    {
        return [
            ['http://getkirby.com', 'getkirby.com'],
            ['https://getkirby.com', 'getkirby.com'],
            ['https://getkirby.com/test', 'getkirby.com/test'],
            ['/', '/'],
            ['/test', '/test']
        ];
    }

    public function providerForLicenseUrls()
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

    public function providerForLoginMethods()
    {
        return [
            [
                'password',
                ['password' => []]
            ],
            [
                'password-reset',
                ['password-reset' => []]
            ],
            [
                ['password-reset'],
                ['password-reset' => []]
            ],
            [
                ['password-reset' => true],
                ['password-reset' => []]
            ],
            [
                ['password-reset' => []],
                ['password-reset' => []]
            ],
            [
                ['password-reset' => ['option' => 'test']],
                ['password-reset' => ['option' => 'test']]
            ],
            [
                ['password', 'password-reset'],
                ['password' => [], 'password-reset' => []]
            ],
            [
                ['code', 'password'],
                ['code' => [], 'password' => []]
            ],
            [
                ['code', 'password-reset'],
                ['password-reset' => []]
            ],
            [
                ['password' => ['2fa' => true], 'code'],
                ['password' => ['2fa' => true]]
            ],
            [
                ['password' => ['2fa' => true], 'password-reset'],
                ['password' => ['2fa' => true]]
            ],
            [
                ['password' => ['2fa' => true], 'code', 'password-reset'],
                ['password' => ['2fa' => true]]
            ]
        ];
    }

    public function providerForRoots()
    {
        return [
            ['accounts'],
            ['content'],
            ['media'],
            ['sessions'],
        ];
    }

    public function providerForServerSoftware()
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

    public function providerForServerNames()
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
     * @covers ::indexUrl
     * @dataProvider providerForIndexUrls
     */
    public function testIndexUrl($indexUrl, $expected)
    {
        $system = new System($this->app->clone([
            'options' => [
                'url' => $indexUrl
            ]
        ]));
        $this->assertSame($expected, $system->indexUrl($indexUrl));
    }

    /**
     * @dataProvider providerForRoots
     * @param $root
     * @throws \Kirby\Exception\PermissionException
     */
    public function testInitPermission($root)
    {
        $this->subFixtures = $this->fixtures . '/' . ucfirst($root) . 'Test';

        $app = $this->app->clone([
            'roots' => [
                'index' => $this->fixtures,
                $root   => $this->subFixtures . '/' . $root,
            ]
        ]);

        // create test roots
        Dir::make($this->subFixtures);

        // set no writable
        chmod($this->subFixtures, 0444);

        // /site/accounts
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('The ' . $root . ' directory could not be created');

        new System($app);
    }

    /**
     * @covers ::isLocal
     * @dataProvider providerForServerNames
     */
    public function testIsLocalWithServerName($name, $expected)
    {
        $_SERVER['SERVER_NAME'] = $name;

        $system = new System($this->app);
        $this->assertSame($expected, $system->isLocal());
    }

    /**
     * @covers ::isLocal
     * @dataProvider providerForClientAddresses
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

    /**
     * @covers ::isInstallable
     */
    public function testIsInstallableOnLocalhost()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $system = new System($this->app);

        $this->assertTrue($system->isInstallable());
    }

    /**
     * @covers ::isInstallable
     */
    public function testIsInstallableOnPublicServer()
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        $system = new System($this->app);

        $this->assertFalse($system->isInstallable());
    }

    /**
     * @covers ::isInstallable
     */
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

    /**
     * @covers ::isInstalled
     */
    public function testIsInstalled()
    {
        $system = new System($this->app);
        $this->assertFalse($system->isInstalled());

        $this->app->users()->create([
            'email'    => 'test@getkirby.com',
            'password' => 'test123456'
        ]);

        $this->assertTrue($system->isInstalled());
    }

    /**
     * @covers ::isOk
     */
    public function testIsOk()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['SERVER_SOFTWARE'] = 'Apache';

        $system = new System($this->app);

        $this->assertTrue($system->isOk());
    }

    /**
     * @covers ::license
     */
    public function testLicense()
    {
        $system = new System($this->app);

        // no license file
        $this->assertFalse($system->license());

        // empty license file
        F::write($this->fixtures . '/site/config/.license', '');
        $this->assertFalse($system->license());

        // invalid license file
        $testLicense = [
            'license'  => 'K3-PRO-test',
            'order'    => 'ORDER-test',
            'email'    => 'bastian@getkirby.com',
            'domain'   => 'starterkit.test',
            'date'     => '2020-08-15',
            // invalid/random hexadecimal string
            'signature' => '67dad6736aa92a844bdba78256da5074e2b61fc0e82c872424f67a46267f1781948bb48a4c7dcc34e843448ec6d612584f210aee30681d89f20f8b7b02a7e8efb1d4b21dd129628a02b355abe2267913f663f5b1cc603cd66a047935bf69061c0f28e6343da220b01a240b49186c7bf143eae2b0d612e08cad5e09741cc888f9551bcb86ceed555e753092af69e1b4d13fa3228b0b9f417ec4ed8b2b148d8c9c1bca54813e0fde5bbb9a33e6b3ea47ddb1d45ca49654e6027696143302515802eac174a7f41dd70b4772245497e69c94aeece9f6b85d6a16005fd3bbaccde766ea7071161ba645853f88678dd935e8a248d12ca013f28ef34aa2865002e57667'
        ];

        F::write($this->fixtures . '/site/config/.license', json_encode($testLicense));
        $this->assertFalse($system->license());
    }

    /**
     * @covers ::licenseUrl
     * @dataProvider providerForLicenseUrls
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
        $this->assertSame($expected, $licenseUrl->invoke($system, $url));
    }

    /**
     * @covers ::loginMethods
     */
    public function testLoginMethods()
    {
        $this->assertSame(['password' => []], $this->app->system()->loginMethods());
    }

    /**
     * @covers ::loginMethods
     * @dataProvider providerForLoginMethods
     */
    public function testLoginMethodsCustom($option, $expected)
    {
        $app = $this->app->clone([
            'options' => [
                'auth.methods' => $option
            ]
        ]);
        $this->assertSame($expected, $app->system()->loginMethods());
    }

    /**
     * @covers ::loginMethods
     */
    public function testLoginMethodsDebug1()
    {
        $app = $this->app->clone([
            'options' => [
                'debug' => true,
                'auth.methods' => ['code', 'password-reset']
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "code" and "password-reset" login methods cannot be enabled together');
        $app->system()->loginMethods();
    }

    /**
     * @covers ::loginMethods
     */
    public function testLoginMethodsDebug2()
    {
        $app = $this->app->clone([
            'options' => [
                'debug' => true,
                'auth.methods' => [
                    'password' => ['2fa' => true],
                    'code'
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');
        $app->system()->loginMethods();
    }

    /**
     * @covers ::loginMethods
     */
    public function testLoginMethodsDebug3()
    {
        $app = $this->app->clone([
            'options' => [
                'debug' => true,
                'auth.methods' => [
                    'password' => ['2fa' => true],
                    'password-reset'
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "password-reset" login method cannot be enabled when 2FA is required');
        $app->system()->loginMethods();
    }

    /**
     * @covers ::plugins
     */
    public function testPlugins()
    {
        $system = new System($this->app);
        $this->assertInstanceOf('Kirby\Cms\Collection', $system->plugins());
    }

    /**
     * @covers ::register
     */
    public function testRegisterWithInvalidLicenseKey()
    {
        $system = new System($this->app);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid license key');

        $system->register('abc');
    }

    /**
     * @covers ::register
     */
    public function testRegisterWithInvalidEmail()
    {
        $system = new System($this->app);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid email address');

        $system->register('K3-PRO-abc', 'invalid');
    }

    /**
     * @covers ::server
     * @covers ::serverSoftware
     * @dataProvider providerForServerSoftware
     */
    public function testServer($software, $expected)
    {
        $_SERVER['SERVER_SOFTWARE'] = $software;

        $system = new System($this->app);
        $server = $system->server();

        $this->assertSame($expected, $server);

        if ($expected === true) {
            $this->assertSame($software, $system->serverSoftware());
        } else {
            $this->assertNull($system->serverSoftware());
        }
    }

    /**
     * @covers ::server
     * @covers ::serverSoftware
     */
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

        $this->assertSame('symfony', $system->serverSoftware());
        $this->assertTrue($server);

        // array of servers
        $app = $this->app->clone([
            'options' => [
                'servers' => ['symfony', 'apache']
            ]
        ]);

        $system = new System($app);
        $server = $system->server();

        $this->assertSame('symfony', $system->serverSoftware());
        $this->assertTrue($server);
    }

    /**
     * @covers ::accounts
     * @covers ::content
     * @covers ::curl
     * @covers ::sessions
     * @covers ::mbstring
     * @covers ::media
     * @covers ::php
     * @covers ::server
     * @covers ::status
     * @covers ::toArray
     */
    public function testStatus()
    {
        $system = new System($this->app);

        $expected = [
            'accounts' => true,
            'content' => true,
            'curl' => true,
            'sessions' => true,
            'mbstring' => true,
            'media' => true,
            'php' => true,
            'server' => false,
        ];
        $this->assertSame($expected, $system->status());
        $this->assertSame($expected, $system->toArray());
    }

    /**
     * @covers ::title
     */
    public function testTitle()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'site' => [
                    'title' => $expected = 'Great site'
                ]
            ]
        ]);

        $this->assertSame($expected, $app->system()->title());

        $app = $app->clone([
            'site' => [
                'content' => [
                    'title' => $expected = 'Better site'
                ]
            ]
        ]);

        $this->assertSame($expected, $app->system()->title());
    }
}
