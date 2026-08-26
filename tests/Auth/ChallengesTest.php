<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Exception\UserNotFoundException;
use Kirby\Filesystem\F;
use Kirby\Session\Session;
use Kirby\Tests\MockTime;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyChallenge extends Challenge
{
	public static bool $available = true;
	public static bool $enabled = true;
	public static bool $singleUse = false;
	public static array $created  = [];
	public static array $verified = [];
	public static Pending|null $pending = null;
	public static \Throwable|null $throw = null;

	public static function isEnabled(Auth $auth): bool
	{
		return static::$enabled;
	}

	public function isSingleUse(): bool
	{
		return static::$singleUse;
	}

	public function create(): Pending|null
	{
		static::$created[] = func_get_args();

		if (static::$throw !== null) {
			throw static::$throw;
		}

		return static::$pending ?? new Pending(
			public: ['foo' => 'bar'],
			secret: 'secret'
		);
	}

	public static function isAvailable(User $user, string $mode): bool
	{
		return static::$available;
	}

	public function verify(mixed $input, Pending $data): bool
	{
		static::$verified[] = ['input' => $input, 'data' => $data->toArray()];
		return $input === 'ok';
	}
}

class DummyChallenge2 extends Challenge
{
	public static bool $available = true;

	public static function isAvailable(User $user, string $mode): bool
	{
		return static::$available;
	}

	public function create(): Pending|null
	{
		return null;
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

class DummyChallenge3 extends Challenge
{
	public function create(): Pending|null
	{
		return null;
	}

	// a plugin challenge may define its own lifetime
	public function timeout(): int
	{
		return 42;
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

#[CoversClass(Challenges::class)]
class ChallengesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Challenges';

	protected Challenges $challenges;
	protected array $original;

	protected function setUp(): void
	{
		parent::setUp();

		DummyChallenge::$available  = true;
		DummyChallenge::$enabled    = true;
		DummyChallenge::$singleUse  = false;
		DummyChallenge::$created    = [];
		DummyChallenge::$verified   = [];
		DummyChallenge::$pending    = null;
		DummyChallenge::$throw      = null;
		DummyChallenge2::$available = true;

		Challenges::$challenges['dummy']  = DummyChallenge::class;
		Challenges::$challenges['dummy2'] = DummyChallenge2::class;
		Challenges::$challenges['dummy3'] = DummyChallenge3::class;

		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['dummy']
				]
			],
			'users' => [
				[
					'email' => 'marge@simpsons.com',
					'id'    => 'marge',
				]
			]
		]);

		$this->auth       = $this->app->auth();
		$this->challenges = new Challenges($this->auth, $this->app);
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		unset(
			Challenges::$challenges['dummy'],
			Challenges::$challenges['dummy2'],
			Challenges::$challenges['dummy3']
		);

	}

	protected function session(): Session
	{
		return $this->app->session();
	}

	public function testAvailable(): void
	{
		$user = $this->app->user('marge');

		$available = $this->challenges->available($user, 'login');
		$this->assertSame(['dummy'], $available);

		DummyChallenge::$available = false;
		$available = $this->challenges->available($user, 'login');
		$this->assertSame([], $available);
	}

	public function testAvailableLimitedToSecondFactor(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['totp', 'email']
				]
			]
		]);

		// the user has set up TOTP as their second factor
		F::write(
			static::TMP . '/site/accounts/marge/.htpasswd',
			User::hashPassword('12345678') . "\n" . '{"totp":"JBSWY3DPEHPK3PXP"}'
		);

		$challenges = new Challenges($this->app->auth(), $this->app);
		$user       = $this->app->user('marge');

		$this->assertSame(['totp'], $challenges->available($user, '2fa'));

		// the single-factor flows must not offer the weaker email
		// code to a user who has set up a second factor
		$this->assertSame(['totp'], $challenges->available($user, 'login'));
		$this->assertSame(['totp'], $challenges->available($user, 'password-reset'));
	}

	public function testAvailableWithoutSecondFactor(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['totp', 'email']
				]
			]
		]);

		$challenges = new Challenges($this->app->auth(), $this->app);
		$user       = $this->app->user('marge');

		$this->assertSame([], $challenges->available($user, '2fa'));

		// without a second factor the single-factor flows are unrestricted
		$this->assertSame(['email'], $challenges->available($user, 'login'));
		$this->assertSame(['email'], $challenges->available($user, 'password-reset'));
	}

	public function testClass(): void
	{
		$this->assertSame(
			DummyChallenge::class,
			$this->challenges->class('dummy')
		);
	}

	public function testClassInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth challenge class for: unknown');
		$this->challenges->class('unknown');
	}

	public function testClear(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.type', 'dummy');

		$this->challenges->clear($session);

		$this->assertNull($session->get('kirby.challenge.data'));
		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.timeout'));
		$this->assertNull($session->get('kirby.challenge.type'));
	}

	public function testCreate(): void
	{
		$session   = $this->session();
		$challenge = $this->challenges->create($session, 'marge@simpsons.com', 'login');

		$this->assertInstanceOf(DummyChallenge::class, $challenge);
		$this->assertSame('dummy', $session->get('kirby.challenge.type'));
		$this->assertSame(['foo' => 'bar'], $session->get('kirby.challenge.data')['public']);
		$this->assertIsInt($session->get('kirby.challenge.timeout'));
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame(MockTime::$time + $this->challenges->timeout(), $session->get('kirby.challenge.timeout'));
	}

	public function testCreateChallengeTimeout(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['dummy3']
				]
			]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);

		$session = $this->app->session();
		$this->challenges->create($session, 'marge@simpsons.com', 'login');

		// the session expiry follows the challenge's own lifetime,
		// not the `auth.challenge.timeout` option
		$this->assertNotSame(42, $this->challenges->timeout());
		$this->assertSame(
			MockTime::$time + 42,
			$session->get('kirby.challenge.timeout')
		);
	}

	public function testCreateUnavailable(): void
	{
		DummyChallenge::$available = false;
		$session = $this->session();

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Could not find a suitable authentication challenge');

		$this->challenges->create($session, 'marge@simpsons.com', 'login');
	}

	public function testCreateInvalidUser(): void
	{
		$session = $this->session();

		$this->expectException(UserNotFoundException::class);
		$this->challenges->create($session, 'invalid@example.com', 'login');
	}

	public function testCreateClearsPreviousChallengeOnFailure(): void
	{
		$session = $this->session();

		// a previous, fully issued challenge for another identity
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'other@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		// re-issuing fails while creating the challenge (e.g. the
		// email transport throwing before the state is stored)
		DummyChallenge::$throw = new \Exception('mail transport failed');

		try {
			$this->challenges->create($session, 'marge@simpsons.com', 'login');
			$this->fail('Expected the challenge creation to throw');
		} catch (\Exception) {
			// expected
		}

		// the previous secret must not survive a failed re-issuance,
		// otherwise it could be verified against the new identity
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.data'));
		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.mode'));
	}

	public function testCreateClearsStaleDataOnReissue(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['dummy2']
				]
			]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);
		$session          = $this->app->session();

		// stale data from a previous challenge that stored a secret
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		// dummy2::create() returns null, so it writes no data of its own;
		// the stale data must still be gone after a successful re-issuance
		$this->challenges->create($session, 'marge@simpsons.com', 'login');

		$this->assertSame('dummy2', $session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.data'));
	}

	public function testEnabledDefaults(): void
	{
		$app        = new App(['roots' => ['index' => static::TMP]]);
		$challenges = new Challenges($app->auth(), $app);

		$this->assertSame(['webauthn', 'totp', 'email'], $challenges->enabled());
	}

	public function testEnabledConfig(): void
	{
		$this->assertSame(['dummy'], $this->challenges->enabled());
	}

	public function testEnabledClassDisabled(): void
	{
		DummyChallenge::$enabled = false;
		$this->assertSame([], $this->challenges->enabled());
	}

	public function testFirstAvailable(): void
	{
		$user = $this->app->user('marge');

		$this->assertInstanceOf(
			DummyChallenge::class,
			$this->challenges->firstAvailable($user, 'login')
		);

		DummyChallenge::$available = false;
		$this->assertNull($this->challenges->firstAvailable($user, 'login'));
	}

	public function testGet(): void
	{
		$user      = $this->app->user('marge');
		$challenge = $this->challenges->get('dummy', $user, 'login', 123);

		$this->assertInstanceOf(DummyChallenge::class, $challenge);
		$this->assertSame($user, $challenge->user());
		$this->assertSame('login', $challenge->mode());
		$this->assertSame(123, $challenge->timeout());
	}

	public function testHasAvailable(): void
	{
		$user = $this->app->user('marge');

		$this->assertTrue($this->challenges->hasAvailable($user, 'login'));

		DummyChallenge::$available = false;
		$this->assertFalse($this->challenges->hasAvailable($user, 'login'));
	}

	public function testSwitch(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['dummy', 'dummy2']
				]
			]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		$challenge = $this->challenges->switch($session, 'dummy2');

		$this->assertInstanceOf(DummyChallenge2::class, $challenge);
		$this->assertSame('dummy2', $session->get('kirby.challenge.type'));
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame(MockTime::$time + $this->challenges->timeout(), $session->get('kirby.challenge.timeout'));

		// dummy2::create() returns null, so no data should be written
		$this->assertNull($session->get('kirby.challenge.data'));
	}

	public function testSwitchSameType(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$timeout = time() + 1000;
		$session->set('kirby.challenge.timeout', $timeout);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		$challenge = $this->challenges->switch($session, 'dummy');

		$this->assertInstanceOf(DummyChallenge::class, $challenge);

		// session must remain untouched
		$this->assertSame('dummy', $session->get('kirby.challenge.type'));
		$this->assertSame($timeout, $session->get('kirby.challenge.timeout'));
		$this->assertSame(['public' => 'x', 'secret' => 'y'], $session->get('kirby.challenge.data'));
	}

	public function testSwitchSameTypeRateLimited(): void
	{
		// the same-type shortcut must consume rate-limit budget just
		// like every other exit path; otherwise an existing user could
		// switch indefinitely while a missing user gets blocked, which
		// would be an enumeration oracle that needs no timing analysis
		$this->app = $this->app->clone([
			'options' => ['auth' => ['trials' => 1]]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);

		// first attempt keeps the existing challenge, but tracks the trial
		$this->assertInstanceOf(
			DummyChallenge::class,
			$this->challenges->switch($session, 'dummy')
		);

		// second attempt is blocked by the rate limit, exactly like
		// the missing user in `::testSwitchRateLimited()`
		$this->expectException(RateLimitException::class);
		$this->challenges->switch($session, 'dummy');
	}

	public function testSwitchTimeout(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() - 10);

		$this->expectException(ChallengeTimeoutException::class);

		$this->challenges->switch($session, 'dummy');
	}

	public function testSwitchNoActiveChallenge(): void
	{
		$session = $this->session();

		$this->expectException(InvalidArgumentException::class);

		$this->challenges->switch($session, 'dummy');
	}

	public function testSwitchUnavailable(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['dummy', 'dummy2']
				]
			]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);

		DummyChallenge2::$available = false;

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);

		$this->expectException(InvalidArgumentException::class);

		$this->challenges->switch($session, 'dummy2');
	}

	public function testSwitchUserNotFound(): void
	{
		// a missing user must not be observable here;
		// instead it keeps the session generically pending
		$session = $this->session();
		$session->set('kirby.challenge.email', 'unknown@example.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		$challenge = $this->challenges->switch($session, 'dummy2');

		// no challenge is created, but the session stays pending
		$this->assertNull($challenge);
		$this->assertSame('unknown@example.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame(MockTime::$time + $this->challenges->timeout(), $session->get('kirby.challenge.timeout'));

		// stale challenge type and data are cleared so nothing leaks
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.data'));
	}

	public function testSwitchUserNotFoundSameType(): void
	{
		// even when the requested type matches the active one,
		// a missing user must not short-circuit into an existing
		// challenge but end up in the generic pending state
		$session = $this->session();
		$session->set('kirby.challenge.email', 'unknown@example.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'y']);

		$challenge = $this->challenges->switch($session, 'dummy');

		$this->assertNull($challenge);
		$this->assertSame('unknown@example.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.data'));
	}

	public function testSwitchRateLimited(): void
	{
		// switching consumes rate-limit budget even for a missing
		// user, so that the endpoint cannot be used for enumeration
		$this->app = $this->app->clone([
			'options' => ['auth' => ['trials' => 1]]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'unknown@example.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.timeout', time() + 1000);

		// first attempt tracks the trial and keeps the session pending
		$this->assertNull($this->challenges->switch($session, 'dummy2'));

		// second attempt is blocked by the rate limit
		$this->expectException(RateLimitException::class);
		$this->challenges->switch($session, 'dummy2');
	}

	public function testVerify(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'secret']);

		$result = $this->challenges->verify($session, 'ok');

		$this->assertInstanceOf(DummyChallenge::class, $result);
		$this->assertSame([['input' => 'ok', 'data' => ['public' => 'x', 'secret' => 'secret']]], DummyChallenge::$verified);
	}

	public function testVerifyLifetime(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'secret']);

		$result = $this->challenges->verify($session, 'ok');

		// the challenge gets its lifetime in seconds, not the
		// absolute expiry timestamp stored in the session
		$this->assertSame($this->challenges->timeout(), $result->timeout());
	}

	public function testVerifyInvalid(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'secret']);

		$this->expectException(PermissionException::class);

		$this->challenges->verify($session, 'nope');
	}

	public function testVerifyInvalidClearsSingleUseChallenge(): void
	{
		DummyChallenge::$singleUse = true;

		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'secret']);

		try {
			$this->challenges->verify($session, 'nope');
			$this->fail('Expected PermissionException');
		} catch (PermissionException) {
			// expected
		}

		// the data for a single-use challenge must
		// not survive a failed attempt
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.data'));
	}

	public function testVerifyInvalidKeepsReusableChallenge(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);
		$session->set('kirby.challenge.data', ['public' => 'x', 'secret' => 'secret']);

		try {
			$this->challenges->verify($session, 'nope');
			$this->fail('Expected PermissionException');
		} catch (PermissionException) {
			// expected
		}

		// code-based challenges (not single-use) stay valid
		// so the user can retry a mistyped code
		$this->assertSame('dummy', $session->get('kirby.challenge.type'));
		$this->assertSame(['public' => 'x', 'secret' => 'secret'], $session->get('kirby.challenge.data'));
	}

	public function testVerifyTimeout(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() - 10);

		$this->expectException(ChallengeTimeoutException::class);

		$this->challenges->verify($session, 'ok');
	}

	public function testVerifyUserNotFound(): void
	{
		$session = $this->session();
		$session->set('kirby.challenge.type', 'dummy');
		$session->set('kirby.challenge.email', 'unknown@example.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 1000);

		$this->expectException(UserNotFoundException::class);

		$this->challenges->verify($session, 'ok');
	}

	public function testVerifyNoChallenge(): void
	{
		$session = $this->session();

		$this->expectException(InvalidArgumentException::class);
		$this->challenges->verify($session, 'ok');
	}

}
