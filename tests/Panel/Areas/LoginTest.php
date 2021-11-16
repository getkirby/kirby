<?php

namespace Kirby\Panel\Areas;

class LoginTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
    }

    public function testLoginRedirectFromHome(): void
    {
        $this->assertRedirect('/', 'login');

        // last path gets remembered
        $this->assertSame('', $this->app->session()->get('panel.path'));
    }

    public function testLoginRedirectFromAnywhere(): void
    {
        $this->assertRedirect('somewhere', 'login');

        // last path gets remembered
        $this->assertSame('somewhere', $this->app->session()->get('panel.path'));
    }

    public function testLogin(): void
    {
        $view  = $this->view('login');
        $props = $view['props'];

        $this->assertSame('login', $view['id']);
        $this->assertSame('Login', $view['title']);
        $this->assertSame('k-login-view', $view['component']);
        $this->assertSame(['password'], $props['methods']);
        $this->assertNull($props['pending']['email']);
        $this->assertNull($props['pending']['challenge']);
    }
}
