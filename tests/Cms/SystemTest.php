<?php

namespace Kirby\Cms;

use Exception;
use ReflectionClass;

class SystemTest extends TestCase
{
    protected $_SERVER = null;

    public function setUp()
    {
        $this->_SERVER = $_SERVER;
    }

    public function tearDown()
    {
        $_SERVER = $this->_SERVER;
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

        $system = new System(new App);
        $server = $system->server();

        $this->assertEquals($expected, $server);
    }

    public function serverNameProvider()
    {
        return [
            ['localhost', true],
            ['mydomain.local', true],
            ['mydomain.test', true],
            ['mydomain.dev', true],
            ['mydomain.com', false],
        ];
    }

    /**
     * @dataProvider serverNameProvider
     */
    public function testIsLocalWithServerNames($name, $expected)
    {
        $_SERVER['SERVER_NAME'] = $name;

        $system = new System(new App);
        $this->assertEquals($expected, $system->isLocal());

        // reset SERVER_NAME
        $_SERVER['SERVER_NAME'] = null;
    }

    public function serverAddressProvider()
    {
        return [
            ['127.0.0.1', true],
            ['::1', true],
            ['0.0.0.0', true],
            ['1.2.3.4', false],
        ];
    }

    /**
     * @dataProvider serverAddressProvider
     */
    public function testIsLocalWithServerAddresses($address, $expected)
    {
        $_SERVER['SERVER_ADDR'] = $address;

        $system = new System(new App);
        $this->assertEquals($expected, $system->isLocal());

        // reset SERVER_ADDR
        $_SERVER['SERVER_ADDR'] = null;
    }

    public function licenseUrlProvider()
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
     * @dataProvider licenseUrlProvider
     */
    public function testLicenseUrl($indexUrl, $expected)
    {
        $reflector = new ReflectionClass(System::class);
        $licenseUrl = $reflector->getMethod('licenseUrl');
        $licenseUrl->setAccessible(true);

        $_SERVER['SERVER_ADDR'] = 'example.com';

        $system = new System(new App([
            'options' => [
                'url' => $indexUrl
            ]
        ]));
        $this->assertEquals($expected, $licenseUrl->invoke($system));

        // reset SERVER_ADDR
        $_SERVER['SERVER_ADDR'] = null;
    }

    public function licenseUrlNormalizedProvider()
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
     * @dataProvider licenseUrlNormalizedProvider
     */
    public function testLicenseUrlNormalized($url, $expected)
    {
        $reflector = new ReflectionClass(System::class);
        $licenseUrlNormalized = $reflector->getMethod('licenseUrlNormalized');
        $licenseUrlNormalized->setAccessible(true);

        $system = new System(new App([
            'options' => [
                'url' => 'https://getkirby.com'
            ]
        ]));
        $this->assertEquals($expected, $licenseUrlNormalized->invoke($system, $url));
    }
}
