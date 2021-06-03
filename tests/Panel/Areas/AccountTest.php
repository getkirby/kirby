<?php

namespace Kirby\Panel\Areas;

class AccountTest extends AreaTestCase
{
    public function testAccountWithoutInstallation(): void
    {
        $this->assertRedirect('account', 'installation');
    }

    public function testAccountWithoutAuthentication(): void
    {
        $this->install();
        $this->assertRedirect('account', 'login');
    }

    public function testAccount(): void
    {
        $this->install();
        $this->login();

        $view = $this->view('account');
        $this->assertSame('account', $view['id']);
        $this->assertSame('k-account-view', $view['component']);
        $this->assertSame('test@getkirby.com', $view['props']['model']['email']);
    }
}
