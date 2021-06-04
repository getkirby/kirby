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
        $this->assertSame([], $props['languages']);
        $this->assertFalse($props['license']);
        $this->assertSame($this->app->version(), $props['version']);
    }

    public function testSettingsWithMultilangSetup(): void
    {
        $this->enableMultilang();
        $this->login();

        $response = $this->response('settings', true);
        $view     = $response['$view'];
        $props    = $view['props'];

        $this->assertTrue($response['$multilang']);
    }

    public function testSettingsWithMultilangSetupAndLanguages(): void
    {
        $this->enableMultilang();
        $this->installLanguages();
        $this->login();

        $response = $this->response('settings', true);
        $view     = $response['$view'];
        $props    = $view['props'];

        $languages = [
            [
                'default' => true,
                'id' => 'en',
                'info' => 'en',
                'text' => 'English'
            ],
            [
                'default' => false,
                'id' => 'de',
                'info' => 'de',
                'text' => 'Deutsch'
            ]
        ];

        $this->assertTrue($response['$multilang']);
        $this->assertSame($languages, $props['languages']);
    }

}
