<?php

namespace Kirby\Panel\Areas;

class LogoutTest extends AreaTestCase
{
	public const LOGIN = 'login?_globals=%24system%2C%24translation';

	protected function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testLogoutGuest(): void
	{
		$this->assertRedirect('logout', static::LOGIN);
	}

	public function testLogoutUser(): void
	{
		$this->login('test@getkirby.com');
		$this->assertSame('test@getkirby.com', $this->app->user()->email());

		$this->assertRedirect('logout', static::LOGIN);

		$this->assertNull($this->app->user());
	}

	public function testLogoutChallenge(): void
	{
		$this->app->session()->set('kirby.challenge.code', '123456');

		$this->assertRedirect('logout', static::LOGIN);

		$this->assertNull($this->app->session()->get('kirby.challenge.code'));
	}

	public function testLogoutCsrf(): void
	{
		// the Panel follows the redirect in place, so the login view
		// it lands on has to hand out a new CSRF token
		$this->app([
			'request' => [
				'query' => [
					'_json'    => true,
					'_globals' => '$system,$translation'
				]
			]
		]);

		$this->login('test@getkirby.com');

		$token = $this->app->auth()->csrfFromSession();

		$this->assertRedirect('logout', static::LOGIN);
		$this->assertNull($this->app->session()->get('kirby.csrf'));

		$login = $this->response('login', true);

		$this->assertArrayHasKey('$system', $login);
		$this->assertNotSame($token, $login['$system']['csrf']);
	}
}
