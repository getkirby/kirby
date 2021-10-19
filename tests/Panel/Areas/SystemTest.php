<?php

namespace Kirby\Panel\Areas;

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
        $this->assertNull($props['license']);
        $this->assertSame($this->app->version(), $props['version']);
    }
}
