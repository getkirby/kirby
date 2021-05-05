<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class SystemRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/SystemRoutesTest'
            ],
        ]);

        Dir::remove($fixtures);
    }

    public function testGetWithInvalidServerSoftware()
    {
        // keep the original software to reset it later
        $originalSoftware = $_SERVER['SERVER_SOFTWARE'] ?? null;

        // set invalid server software
        $_SERVER['SERVER_SOFTWARE'] = 'invalid';

        $response = $this->app->api()->call('system', 'GET');

        $this->assertFalse($response['data']['isOk']);
        $this->assertFalse($response['data']['requirements']['server']);

        // reset the server software
        $_SERVER['SERVER_SOFTWARE'] = $originalSoftware;
    }

    public function testGetWithValidServerSoftware()
    {
        // keep the original software to reset it later
        $originalSoftware = $_SERVER['SERVER_SOFTWARE'] ?? null;

        // set invalid server software
        $_SERVER['SERVER_SOFTWARE'] = 'apache';

        $response = $this->app->api()->call('system', 'GET');

        $this->assertTrue($response['data']['isOk']);

        // reset the server software
        $_SERVER['SERVER_SOFTWARE'] = $originalSoftware;
    }

    public function testGetWithoutUser()
    {
        $response = $this->app->api()->call('system', 'GET');

        $this->assertArrayNotHasKey('user', $response['data']);
    }

    public function testGetWithUser()
    {
        $this->app->impersonate('kirby');

        $response = $this->app->api()->call('system', 'GET');

        $this->assertArrayHasKey('user', $response['data']);
    }
}
