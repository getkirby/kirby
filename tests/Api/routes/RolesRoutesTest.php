<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class RolesRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                [
                    'name'  => 'admin',
                    'title' => 'Admin',
                ],
                [
                    'name'  => 'editor',
                    'title' => 'Editor',
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testList()
    {
        $app = $this->app;

        $response = $app->api()->call('roles');

        $this->assertEquals('admin', $response['data'][0]['name']);
        $this->assertEquals('editor', $response['data'][1]['name']);
    }

    public function testGet()
    {
        $app = $this->app;

        $response = $app->api()->call('roles/editor');

        $this->assertEquals('editor', $response['data']['name']);
        $this->assertEquals('Editor', $response['data']['title']);
    }
}
