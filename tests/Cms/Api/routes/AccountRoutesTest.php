<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class AccountRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/AccountRoutesTest'
            ],
            'users' => [
                [
                    'name'    => 'Bastian',
                    'email'   => 'bastian@getkirby.com',
                    'role'    => 'admin',
                    'content' => [
                        'twitter' => '@bastianallgeier'
                    ]
                ]
            ]
        ]);

        Dir::remove($fixtures);
    }

    public function testGet()
    {
        $this->app->impersonate('bastian@getkirby.com');

        $response = $this->app->api()->call('account');

        $this->assertSame('bastian@getkirby.com', $response['data']['email']);
        $this->assertSame('@bastianallgeier', $response['data']['content']['twitter']);
    }

    public function testPatch()
    {
        $user = $this->app->impersonate('bastian@getkirby.com');

        $response = $this->app->api()->call('account', 'PATCH', [
            'body' => [
                'twitter' => '@getkirby'
            ]
        ]);

        $this->assertSame('bastian@getkirby.com', $user->email());
        $this->assertSame('@bastianallgeier', $user->twitter()->value());
        $this->assertSame('bastian@getkirby.com', $response['data']['email']);
        $this->assertSame('@getkirby', $response['data']['content']['twitter']);
    }
}
