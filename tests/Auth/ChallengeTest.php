<?php

namespace Kirby\Auth;

use Kirby\Cms\User;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

class AttemptChallenge extends Challenge
{
	public static bool $singleUse = false;
	public static bool $throws = false;

	public function create(): Pending|null
	{
		return null;
	}

	public function isSingleUse(): bool
	{
		return static::$singleUse;
	}

	public function verify(mixed $input, Pending $data): bool
	{
		if (static::$throws === true) {
			throw new LogicException(message: 'Broken challenge');
		}

		return $input === 'ok';
	}
}

#[CoversClass(Challenge::class)]
class ChallengeTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Challenge';

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'marge@simpsons.com',
					'id'    => 'marge',
				]
			]
		]);

		$this->user = $this->app->user('marge');
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		AttemptChallenge::$singleUse = false;
		AttemptChallenge::$throws    = false;
	}

	protected function challenge(): AttemptChallenge
	{
		return new AttemptChallenge(
			user:    $this->user,
			mode:    '2fa',
			timeout: 600
		);
	}

	public function testAttempt(): void
	{
		$invalidated = false;

		$this->challenge()->attempt(
			input:      'ok',
			pending:    new Pending(),
			invalidate: function () use (&$invalidated) {
				$invalidated = true;
			}
		);

		$this->assertFalse($invalidated);
	}

	public function testAttemptInvalid(): void
	{
		$invalidated = false;

		try {
			$this->challenge()->attempt(
				input:      'nope',
				pending:    new Pending(),
				invalidate: function () use (&$invalidated) {
					$invalidated = true;
				}
			);

			$this->fail('Expected PermissionException');
		} catch (PermissionException $e) {
			$this->assertSame('error.access.code', $e->getCode());
		}

		// a reusable code survives a failed attempt so that the
		// user can retry within its lifetime
		$this->assertFalse($invalidated);
	}

	public function testAttemptInvalidSingleUse(): void
	{
		AttemptChallenge::$singleUse = true;

		$invalidated = false;

		try {
			$this->challenge()->attempt(
				input:      'nope',
				pending:    new Pending(),
				invalidate: function () use (&$invalidated) {
					$invalidated = true;
				}
			);

			$this->fail('Expected PermissionException');
		} catch (PermissionException) {
			// expected
		}

		$this->assertTrue($invalidated);
	}

	public function testAttemptThrows(): void
	{
		AttemptChallenge::$throws = true;

		$invalidated = false;

		try {
			$this->challenge()->attempt(
				input:      'ok',
				pending:    new Pending(),
				invalidate: function () use (&$invalidated) {
					$invalidated = true;
				}
			);

			$this->fail('Expected LogicException');
		} catch (LogicException) {
			// expected
		}

		// a reusable code is kept even when the challenge itself blows up
		$this->assertFalse($invalidated);
	}

	public function testAttemptThrowsSingleUse(): void
	{
		AttemptChallenge::$singleUse = true;
		AttemptChallenge::$throws    = true;

		$invalidated = false;

		try {
			$this->challenge()->attempt(
				input:      'ok',
				pending:    new Pending(),
				invalidate: function () use (&$invalidated) {
					$invalidated = true;
				}
			);

			$this->fail('Expected LogicException');
		} catch (LogicException) {
			// expected
		}

		// the nonce must be burnt even if the exception
		// comes from the challenge instead of a wrong input
		$this->assertTrue($invalidated);
	}
}
