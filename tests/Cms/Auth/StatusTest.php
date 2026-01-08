<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\TestCase;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated 6.0.0
 */
#[CoversClass(Status::class)]
class StatusTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com',
					'name'  => 'Homer Simpson'
				]
			]
		]);
	}

	public function testToString(): void
	{
		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'active'
		]);

		$this->assertSame('active', (string)$status);
	}

	public function testChallenge(): void
	{
		// no challenge when not in pending status
		$status = new Status([
			'kirby'             => $this->app,
			'challenge'         => 'totp',
			'challengeFallback' => 'email',
			'status'            => 'active'
		]);
		$this->assertNull($status->challenge());

		// with pending challenge
		$status = new Status([
			'kirby'             => $this->app,
			'challenge'         => 'totp',
			'challengeFallback' => 'email',
			'status'            => 'pending'
		]);
		$this->assertSame('totp', $status->challenge());

		// with faked challenge
		$status = new Status([
			'kirby'             => $this->app,
			'challenge'         => null,
			'challengeFallback' => 'email',
			'status'            => 'pending'
		]);
		$this->assertSame('email', $status->challenge());

		// with faked challenge, but without automatic fallback
		$status = new Status([
			'kirby'             => $this->app,
			'challenge'         => null,
			'challengeFallback' => 'email',
			'status'            => 'pending'
		]);
		$this->assertNull($status->challenge(false));
	}

	public function testEmail(): void
	{
		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'inactive'
		]);
		$this->assertNull($status->email());

		$status = new Status([
			'kirby'  => $this->app,
			'email'  => 'homer@simpsons.com',
			'status' => 'active'
		]);
		$this->assertSame('homer@simpsons.com', $status->email());
	}

	public function testMode(): void
	{
		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'inactive'
		]);
		$this->assertNull($status->mode());

		$status = new Status([
			'kirby'  => $this->app,
			'mode'   => 'password-reset',
			'status' => 'active'
		]);
		$this->assertSame('password-reset', $status->mode());
	}

	public function testStatus(): void
	{
		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'active'
		]);
		$this->assertSame('active', $status->status());

		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'impersonated'
		]);
		$this->assertSame('impersonated', $status->status());

		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'pending'
		]);
		$this->assertSame('pending', $status->status());

		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'inactive'
		]);
		$this->assertSame('inactive', $status->status());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid argument "$props[\'status\']" in method "Status::__construct"');
		$status = new Status([
			'kirby'  => $this->app,
			'status' => 'invalid'
		]);
	}

	public function testToArray(): void
	{
		$status = new Status([
			'kirby'             => $this->app,
			'challenge'         => null,
			'challengeFallback' => 'email',
			'email'             => 'homer@simpsons.com',
			'mode'              => 'password-reset',
			'status'            => 'pending'
		]);

		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'homer@simpsons.com',
			'mode'      => 'password-reset',
			'status'    => 'pending'
		], $status->toArray());
	}

	public function testUser(): void
	{
		// only return active users
		$status = new Status([
			'kirby'  => $this->app,
			'email'  => 'homer@simpsons.com',
			'status' => 'pending'
		]);
		$this->assertNull($status->user());

		// existing active user
		$status = new Status([
			'kirby'  => $this->app,
			'email'  => 'homer@simpsons.com',
			'status' => 'active'
		]);
		$this->assertSame('Homer Simpson', $status->user()->name()->value());

		// existing impersonated user
		$status = new Status([
			'kirby'  => $this->app,
			'email'  => 'homer@simpsons.com',
			'status' => 'impersonated'
		]);
		$this->assertSame('Homer Simpson', $status->user()->name()->value());

		// invalid active user
		$status = new Status([
			'kirby'  => $this->app,
			'email'  => 'invalid@simpsons.com',
			'status' => 'active'
		]);
		$this->assertNull($status->user());
	}
}
