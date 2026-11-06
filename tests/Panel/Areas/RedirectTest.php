<?php

namespace Kirby\Panel\Areas;

class RedirectTest extends AreaTestCase
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

	public function testInstallationRedirectFromHome(): void
	{
		$this->assertRedirect('/', 'installation');
	}

	public function testInstallationRedirectFromAnywhere(): void
	{
		$this->assertRedirect('somewhere', 'installation');
	}

	public function testInstallationWhenInstalled(): void
	{
		$this->install();
		$this->assertRedirect('installation', 'login');
	}

	public function testInstallationWhenAuthenticated(): void
	{
		$this->install();
		$this->login();
		$this->assertRedirect('installation', 'site');
	}

	public function testLoginRedirectFromHome(): void
	{
		$this->install();

		$this->assertRedirect('/', 'login');

		// last path gets remembered
		$this->assertSame('', $this->app->session()->get('panel.path'));
	}

	public function testLoginRedirectFromAnywhere(): void
	{
		$this->install();

		$this->assertRedirect('somewhere', 'login');

		// last path gets remembered
		$this->assertSame('somewhere', $this->app->session()->get('panel.path'));
	}

	public function testLoginRedirectWithQuery(): void
	{
		$this->install();
		$this->app([
			'request' => [
				'query' => ['email' => 'test@getkirby.com']
			]
		]);

		$this->assertRedirect('login', 'login/method/password?email=test%40getkirby.com');
	}

	public function testLogoutGuest(): void
	{
		$this->install();
		$this->assertRedirect('logout', 'login');
	}

	public function testLogoutUser(): void
	{
		$this->install();
		$this->login('test@getkirby.com');
		$this->assertSame('test@getkirby.com', $this->app->user()->email());

		$this->assertRedirect('logout', 'login');

		$this->assertNull($this->app->user());
	}

	public function testLogoutChallenge(): void
	{
		$this->install();
		$this->app->session()->set('kirby.challenge.data', ['secret' => '123456']);

		$this->assertRedirect('logout', 'login');

		$this->assertNull($this->app->session()->get('kirby.challenge.data'));
	}

	public function testLogoutCsrf(): void
	{
		$this->install();
		$this->login('test@getkirby.com');

		$token = $this->app->auth()->csrfFromSession();

		$this->assertRedirect('logout', 'login');
		$this->assertNull($this->app->session()->get('kirby.csrf'));

		$this->assertRedirect('login', 'login/method/password');

		// the Panel follows the redirects in place, so the login
		// view it lands on has to hand out a new CSRF token
		$login = $this->response('login/method/password', true);

		$this->assertArrayHasKey('csrf', $login);
		$this->assertNotSame($token, $login['csrf']);
	}

	public function testPageWithoutAuthentication(): void
	{
		$this->install();
		$this->assertRedirect('pages/home', 'login');
	}

	public function testPageWithMissingModel(): void
	{
		$this->install();
		$this->login();
		$this->assertErrorView('pages/does-not-exist', 'The page "does-not-exist" cannot be found');
	}

	public function testSiteWithoutAuthentication(): void
	{
		$this->install();
		$this->assertRedirect('site', 'login');
	}

	public function testSiteRedirectFromHome(): void
	{
		$this->install();
		$this->login();
		$this->assertRedirect('/', 'site');
	}
}
