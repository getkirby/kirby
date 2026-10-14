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

	public function testLogoutGuest(): void
	{
		$this->install();
		$this->assertRedirect('logout', 'login?_globals=system%2Ctranslation');
	}

	public function testLogoutUser(): void
	{
		$this->install();
		$this->login('test@getkirby.com');
		$this->assertSame('test@getkirby.com', $this->app->user()->email());

		$this->assertRedirect('logout', 'login?_globals=system%2Ctranslation');

		$this->assertNull($this->app->user());
	}

	public function testLogoutChallenge(): void
	{
		$this->install();
		$this->app->session()->set('kirby.challenge.data', ['secret' => '123456']);

		$this->assertRedirect('logout', 'login?_globals=system%2Ctranslation');

		$this->assertNull($this->app->session()->get('kirby.challenge.data'));
	}

	public function testLogoutCsrf(): void
	{
		$this->install();

		// the Panel follows the redirects in place, so the login
		// view it lands on has to hand out a new CSRF token
		$this->app([
			'request' => [
				'query' => [
					'_globals' => 'system,translation'
				]
			]
		]);

		$this->login('test@getkirby.com');

		$token = $this->app->auth()->csrfFromSession();

		$this->assertRedirect('logout', 'login?_globals=system%2Ctranslation');
		$this->assertNull($this->app->session()->get('kirby.csrf'));

		$this->assertRedirect('login', 'login/method/password?_json=1&_globals=system%2Ctranslation');

		$login = $this->response('login/method/password', true);

		$this->assertArrayHasKey('system', $login);
		$this->assertNotSame($token, $login['system']['csrf']);
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
