<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class AuthRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/AuthRoutesTest'
            ],
        ]);

        Dir::remove($fixtures);
    }

    public function tearDown(): void
    {
        $this->app->session()->destroy();
    }

    public function testGet()
    {
        $this->app->impersonate('kirby');

        $response = $this->app->api()->call('auth');

        $this->assertEquals('kirby@getkirby.com', $response['data']['email']);
    }

    public function testLoginWithoutCSRF()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid CSRF token');

        $response = $this->app->api()->call('auth/login', 'POST');
    }
}
