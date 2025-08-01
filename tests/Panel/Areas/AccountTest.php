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
		$this->assertSame('test@getkirby.com', $view['props']['email']);
	}

	public function testAccountFiles(): void
	{
		$this->app([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			]
		]);

		$this->install();
		$this->login();

		$view  = $this->view('account/files/test.jpg');
		$props = $view['props'];

		$this->assertSame('account', $view['id']);
		$this->assertSame('k-file-view', $view['component']);
		$this->assertSame('test.jpg', $view['title']);

		// invalid request
		$view  = $this->view('account/files/no-exist.jpg');
		$props = $view['props'];

		$this->assertSame('account', $view['id']);
		$this->assertSame('k-error-view', $view['component']);
		$this->assertSame('Error', $view['title']);
		$this->assertSame('The file "no-exist.jpg" cannot be found', $props['error']);
	}

	public function testResetPassword(): void
	{
		$this->install();
		$this->login();
		$this->app->session()->set('kirby.resetPassword', true);

		$view = $this->view('reset-password');
		$this->assertSame('k-reset-password-view', $view['component']);
		$this->assertFalse($view['props']['requirePassword']);
	}

	public function testResetPasswordWithoutResetMode(): void
	{
		$this->install();
		$this->login();

		$view = $this->view('reset-password');
		$this->assertSame('k-reset-password-view', $view['component']);
		$this->assertTrue($view['props']['requirePassword']);
	}
}
