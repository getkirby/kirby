<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Status::class)]
class StatusTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Status';

	protected App $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com',
					'name'  => 'Homer Simpson'
				]
			]
		]);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testActive(): void
	{
		$user   = $this->app->user('homer@simpsons.com');
		$active = Status::active($this->app, $user);
		$this->assertTrue($active->is(State::Active));
	}

	public function testChallenge(): void
	{
		// no challenge when not pending
		$user   = $this->app->user('homer@simpsons.com');
		$status = Status::active($this->app, $user);
		$this->assertNull($status->challenge());

		// with pending challenge
		$status = Status::pending(
			kirby: $this->app,
			email: 'homer@simpsons.com',
			mode: 'login',
			challenge: 'totp',
			fallback: 'email'
		);
		$this->assertSame('totp', $status->challenge());

		// with faked challenge uses fallback
		$status = Status::pending(
			kirby: $this->app,
			email: 'homer@simpsons.com',
			mode: 'login',
			fallback: 'email'
		);
		$this->assertSame('email', $status->challenge());
		$this->assertNull($status->challenge(false));
	}

	public function testEmail(): void
	{
		$status = Status::inactive($this->app);
		$this->assertNull($status->email());
	}

	public function testFor(): void
	{
		$session = $this->app->session();
		$user    = $this->app->user('homer@simpsons.com');
		$status  = Status::for(
			kirby: $this->app,
			user: $user,
			impersonated: false,
			session: $session,
			challenges: ['totp']
		);
		$this->assertTrue($status->is(State::Active));

		// pending status from session with fallback challenge
		$session->set('kirby.challenge.email', 'homer@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', null);
		$session->set('kirby.challenge.data', ['public' => 'bar']);

		$status = Status::for(
			kirby: $this->app,
			user: null,
			impersonated: false,
			session: $session,
			challenges: ['totp', 'email']
		);

		$this->assertTrue($status->is(State::Pending));
		$this->assertSame('email', $status->challenge());
		$this->assertSame('login', $status->mode());
		$this->assertSame('bar', $status->data()?->public());

		// inactive if no user and no challenge in session
		$session->clear();
		$status = Status::for(
			kirby: $this->app,
			user: null,
			impersonated: false,
			session: $session,
			challenges: []
		);
		$this->assertTrue($status->is(State::Inactive));
	}

	public function testImpersonated(): void
	{
		$user         = $this->app->user('homer@simpsons.com');
		$impersonated = Status::impersonated($this->app, $user);
		$this->assertTrue($impersonated->is(State::Impersonated));
	}

	public function testInactive(): void
	{
		$inactive = Status::inactive($this->app);
		$this->assertTrue($inactive->is(State::Inactive));
	}

	public function testMode(): void
	{
		$status = Status::inactive($this->app);
		$this->assertNull($status->mode());

		$status = Status::pending(
			kirby: $this->app,
			email: 'homer@simpsons.com',
			mode: 'password-reset'
		);
		$this->assertSame('password-reset', $status->mode());
	}

	public function testPending(): void
	{
		$pending = Status::pending($this->app, 'homer@simpsons.com');
		$this->assertTrue($pending->is(State::Pending));
	}

	public function testState(): void
	{
		$user         = $this->app->user('homer@simpsons.com');
		$active       = Status::active($this->app, $user);
		$impersonated = Status::impersonated($this->app, $user);
		$pending      = Status::pending($this->app, 'homer@simpsons.com');
		$inactive     = Status::inactive($this->app);

		$this->assertTrue($active->is(State::Active));
		$this->assertTrue($impersonated->is(State::Impersonated));
		$this->assertTrue($pending->is(State::Pending));
		$this->assertTrue($inactive->is(State::Inactive));

		$this->assertFalse($active->is(State::Impersonated));
		$this->assertFalse($impersonated->is(State::Active));
		$this->assertFalse($pending->is(State::Active));
		$this->assertFalse($inactive->is(State::Pending));
	}

	public function testToArray(): void
	{
		$status = Status::pending(
			kirby:     $this->app,
			email:     'homer@simpsons.com',
			mode:      'password-reset',
			challenge: 'totp',
			data:       new Pending(public: ['foo' => 'bar'])
		);

		$this->assertSame([
			'challenge' => 'totp',
			'data'      => ['foo' => 'bar'],
			'email'     => 'homer@simpsons.com',
			'mode'      => 'password-reset',
			'status'    => 'pending'
		], $status->toArray());
	}

	public function testUser(): void
	{
		// only active/impersonated states return a user
		$pending = Status::pending($this->app, 'homer@simpsons.com');
		$this->assertNull($pending->user());

		$inactive = Status::inactive($this->app);
		$this->assertNull($inactive->user());

		$user   = $this->app->user('homer@simpsons.com');
		$active = Status::active($this->app, $user);
		$name   = $active->user()?->name()->value();
		$this->assertSame('Homer Simpson', $name);

		$impersonated = Status::impersonated($this->app, $user);
		$name         = $impersonated->user()?->name()->value();
		$this->assertSame('Homer Simpson', $name);

		// if the stored email no longer exists, return null
		$app   = $this->app->clone(['users' => []]);
		$user  = new User(['email' => 'invalid@simpsons.com']);
		$stale = Status::active($app, $user);
		$this->assertNull($stale->user());
	}
}
