<?php

namespace Kirby\Auth;

use Kirby\Auth\Challenge\LegacyChallenge;
use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Exception\UserNotFoundException;
use Kirby\Session\Session;
use Kirby\Tests\MockTime;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyLegacyChallenge extends \Kirby\Cms\Auth\Challenge
{
	public static bool $available = true;
	public static string|null $code = 'legacy-code';

	public static function isAvailable(User $user, string $mode): bool
	{
		return static::$available;
	}

	public static function create(User $user, array $options): string|null
	{
		return static::$code;
	}
}

class DummyChallenge extends Challenge
{
	public static bool $available = true;
	public static bool $enabled = true;
	public static array $created  = [];
	public static array $verified = [];
	public static Pending|null $pending = null;

	public static function isEnabled(Auth $auth): bool
	{
		return static::$enabled;
	}

	public function create(): Pending|null
	{
		static::$created[] = func_get_args();

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

#[CoversClass(Challenges::class)]
class ChallengesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Challenges';

	protected Challenges $challenges;
	protected array $original;

	public function setUp(): void
	{
		parent::setUp();

		DummyChallenge::$available = true;
		DummyChallenge::$enabled   = true;
		DummyChallenge::$created   = [];
		DummyChallenge::$verified  = [];
		DummyChallenge::$pending   = null;

		Challenges::$challenges['dummy'] = DummyChallenge::class;

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

	public function tearDown(): void
	{
		parent::tearDown();
		unset(Challenges::$challenges['dummy']);
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

		$this->challenges->clear();

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

	public function testEnabledDefaults(): void
	{
		$app        = new App(['roots' => ['index' => static::TMP]]);
		$challenges = new Challenges($app->auth(), $app);

		$this->assertSame(['totp', 'email'], $challenges->enabled());
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

	/**
	 * @deprecated 6.0.0
	 */
	public function testLegacyChallenge(): void
	{
		Challenges::$challenges = ['foo' => DummyLegacyChallenge::class];

		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['foo']
				]
			]
		]);

		$this->challenges = new Challenges($this->app->auth(), $this->app);
		$session   = $this->app->session();
		$challenge = $this->challenges->create($session, 'marge@simpsons.com', 'login');

		$this->assertInstanceOf(LegacyChallenge::class, $challenge);
		$this->assertSame('foo', $challenge->type());
		$this->assertSame('foo', $session->get('kirby.challenge.type'));

		$data = $session->get('kirby.challenge.data');
		$code = $session->get('kirby.challenge.code');
		$this->assertTrue(password_verify(DummyLegacyChallenge::$code, $data['secret']));
		$this->assertTrue(password_verify(DummyLegacyChallenge::$code, $code));

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');

		$result = $this->challenges->verify($session, DummyLegacyChallenge::$code);
		$this->assertInstanceOf(LegacyChallenge::class, $result);
	}
}
