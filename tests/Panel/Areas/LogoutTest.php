<?php

namespace Kirby\Panel\Areas;

class LogoutTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testLogoutGuest(): void
	{
		$this->assertRedirect('logout', 'login');
	}

	public function testLogoutUser(): void
	{
		$this->login('test@getkirby.com');
		$this->assertSame('test@getkirby.com', $this->app->user()->email());

		$this->assertRedirect('logout', 'login');

		$this->assertNull($this->app->user());
	}

	public function testLogoutChallenge(): void
	{
		$this->app->session()->set('kirby.challenge.code', '123456');

		$this->assertRedirect('logout', 'login');

		$this->assertNull($this->app->session()->get('kirby.challenge.code'));
	}
}
