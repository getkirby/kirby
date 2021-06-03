<?php

namespace Kirby\Panel\Areas;

class SettingsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testSettingsWithoutAuthentication(): void
    {
        $this->assertRedirect('settings', 'login');
    }

    public function testSettings(): void
    {
        $this->login();

        $view  = $this->view('settings');
        $props = $view['props'];

        $this->assertSame('settings', $view['id']);
        $this->assertSame('Settings', $view['title']);
        $this->assertSame('k-settings-view', $view['component']);

        // TODO: add more props tests
    }
}
