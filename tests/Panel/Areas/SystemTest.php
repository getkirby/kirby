<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;

class SystemTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testViewWithoutAuthentication(): void
    {
        $this->assertRedirect('system', 'login');
    }

    public function testView(): void
    {
        $this->login();

        $view  = $this->view('system');
        $props = $view['props'];

        $this->assertSame('system', $view['id']);
        $this->assertSame('System', $view['title']);
        $this->assertSame('k-system-view', $view['component']);
        $this->assertFalse($props['debug']);
        $this->assertNull($props['license']);
        $this->assertSame([], $props['plugins']);
        $this->assertSame(phpversion(), $props['php']);
        $this->assertSame('php', $props['server']);
        $this->assertSame($this->app->version(), $props['version']);
    }

    public function testViewWithPlugins(): void
    {
        App::plugin('getkirby/test', [
            'info' => [
                'authors' => [
                    [
                        'name' => 'A'
                    ],
                    [
                        'name' => 'B'
                    ]
                ],
                'homepage' => 'https://getkirby.com',
                'version'  => '1.0.0',
                'license'  => 'MIT'
            ]
        ]);

        $this->login();

        $view     = $this->view('system');
        $expected = [
            [
                'author'  => 'A, B',
                'license' => 'MIT',
                'link'    => 'https://getkirby.com',
                'name'    => 'getkirby/test',
                'version' => '1.0.0'
            ]
        ];

        $this->assertSame($expected, $view['props']['plugins']);

        App::destroy();
    }
}
