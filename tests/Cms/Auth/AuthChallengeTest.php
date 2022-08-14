<?php

namespace Kirby\Cms;

use Kirby\Email\Email;
use Kirby\Filesystem\Dir;
use Throwable;

require_once __DIR__ . '/../mocks.php';

/**
 * @coversDefaultClass \Kirby\Cms\Auth
 */
class AuthChallengeTest extends TestCase
{
	public $failedEmail;

	protected $app;
	protected $auth;
	protected $fixtures;

	public function setUp(): void
	{
		Email::$debug = true;
		Email::$emails = [];
		$_SERVER['SERVER_NAME'] = 'kirby.test';

		$self = $this;

		$this->app = new App([
			'hooks' => [
				'user.login:failed' => function ($email) use ($self) {
					$self->failedEmail = $email;
				}
			],
			'options' => [
				'auth.trials' => 2,
				'debug' => true
			],
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/AuthTest'
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email' => 'test@exämple.com'
				]
			]
		]);
		Dir::make($this->fixtures . '/site/accounts');

		$this->auth = new Auth($this->app);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove($this->fixtures);

		Email::$debug = false;
		Email::$emails = [];
		unset($_SERVER['SERVER_NAME']);
		$this->failedEmail = null;
	}

	/**
	 * @covers ::createChallenge
	 * @covers ::status
	 */
	public function testCreateChallenge()
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => false
			]
		]);
		$auth    = $this->app->auth();
		$session = $this->app->session();

		$this->app->visitor()->ip('10.1.123.234');

		// existing user
		$status = $auth->createChallenge('marge@simpsons.com');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'marge@simpsons.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(1800, $session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(str_replace(' ', '', $codeMatches[0]), $session->get('kirby.challenge.code')));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
		$session->clear();

		// non-existing user
		$status = $auth->createChallenge('invalid@example.com');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'invalid@example.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame('invalid@example.com', $session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertSame('invalid@example.com', $this->failedEmail);

		// verify rate-limiting log
		$data = [
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'time'   => MockTime::$time,
					'trials' => 2
				]
			],
			'by-email' => [
				'marge@simpsons.com' => [
					'time'   => MockTime::$time,
					'trials' => 1
				]
			]
		];
		$this->assertSame($data, $auth->log());

		// cannot create challenge when rate-limited
		$this->expectException('Kirby\Exception\PermissionException');
		$this->expectExceptionMessage('Invalid login');
		$auth->createChallenge('marge@simpsons.com');
	}

	/**
	 * @covers ::createChallenge
	 */
	public function testCreateChallengeDebugNotFound()
	{
		$this->expectException('Kirby\Exception\NotFoundException');
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');

		$this->auth->createChallenge('invalid@example.com');
	}

	/**
	 * @covers ::createChallenge
	 */
	public function testCreateChallengeDebugRateLimit()
	{
		$auth = $this->app->auth();

		$auth->createChallenge('marge@simpsons.com');
		$auth->createChallenge('marge@simpsons.com');

		$this->expectException('Kirby\Exception\PermissionException');
		$this->expectExceptionMessage('Rate limit exceeded');
		$auth->createChallenge('marge@simpsons.com');
	}

	/**
	 * @covers ::createChallenge
	 * @covers ::status
	 */
	public function testCreateChallengeCustomTimeout()
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth.challenge.timeout' => 10,
				'debug' => false
			]
		]);
		$auth    = $this->app->auth();
		$session = $this->app->session();

		// existing user
		$status = $auth->createChallenge('marge@simpsons.com');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'marge@simpsons.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(MockTime::$time + 10, $session->get('kirby.challenge.timeout'));
		$session->clear();

		// non-existing user
		$status = $auth->createChallenge('invalid@example.com');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'invalid@example.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame(MockTime::$time + 10, $session->get('kirby.challenge.timeout'));
	}

	/**
	 * @covers ::createChallenge
	 * @covers ::status
	 */
	public function testCreateChallengeLong()
	{
		$session = $this->app->session();

		$status = $this->auth->createChallenge('marge@simpsons.com', true);
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'marge@simpsons.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));

		$this->assertFalse($session->timeout());
	}

	/**
	 * @covers ::createChallenge
	 * @covers ::status
	 */
	public function testCreateChallengeWithPunycodeEmail()
	{
		$session = $this->app->session();

		$status = $this->auth->createChallenge('test@xn--exmple-cua.com');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'test@exämple.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame('test@exämple.com', $session->get('kirby.challenge.email'));
	}

	/**
	 * @covers ::enabledChallenges
	 */
	public function testEnabledChallenges()
	{
		$this->assertSame(['email'], $this->auth->enabledChallenges());

		// a single challenge
		$app = $this->app->clone([
			'options' => [
				'auth.challenges' => 'totp'
			]
		]);
		$this->assertSame(['totp'], $app->auth()->enabledChallenges());

		// multiple challenges
		$app = $this->app->clone([
			'options' => [
				'auth.challenges' => ['totp', 'sms']
			]
		]);
		$this->assertSame(['totp', 'sms'], $app->auth()->enabledChallenges());
	}

	/**
	 * @covers ::login2fa
	 * @covers ::status
	 */
	public function testLogin2fa()
	{
		$session = $this->app->session();

		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123');
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'marge@simpsons.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(1800, $session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(str_replace(' ', '', $codeMatches[0]), $session->get('kirby.challenge.code')));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
	}

	/**
	 * @covers ::login2fa
	 * @covers ::status
	 */
	public function testLogin2faLong()
	{
		$session = $this->app->session();

		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123', true);
		$this->assertSame([
			'challenge' => 'email',
			'email'     => 'marge@simpsons.com',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertFalse($session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(str_replace(' ', '', $codeMatches[0]), $session->get('kirby.challenge.code')));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
	}

	/**
	 * @covers ::login2fa
	 */
	public function testLogin2faInvalidUser()
	{
		$session = $this->app->session();

		$this->expectException('Kirby\Exception\NotFoundException');
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');
		$this->auth->login2fa('invalid@example.com', 'springfield123');
	}

	/**
	 * @covers ::login2fa
	 */
	public function testLogin2faInvalidPassword()
	{
		$session = $this->app->session();

		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('Wrong password');
		$this->auth->login2fa('marge@simpsons.com', 'springfield456');
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallenge()
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertSame(
			$this->app->user('marge@simpsons.com'),
			$this->auth->verifyChallenge('123456')
		);
		$this->assertSame(['kirby.userId' => 'marge'], $session->data()->get());
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeNoChallenge1()
	{
		try {
			$this->auth->verifyChallenge('123456');

			$this->fail('No InvalidArgumentException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\InvalidArgumentException', $e);
			$this->assertSame('No authentication challenge is active', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeNoChallenge2()
	{
		try {
			$this->app->session()->set('kirby.challenge.email', 'marge@simpsons.com');
			$this->auth->verifyChallenge('123456');

			$this->fail('No InvalidArgumentException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\InvalidArgumentException', $e);
			$this->assertSame('No authentication challenge is active', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => false], $e->getDetails());
		}
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeNoChallengeNoDebug1()
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => false
			]
		]);
		$auth = $this->app->auth();

		try {
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\PermissionException', $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeNoChallengeNoDebug2()
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => false
			]
		]);
		$auth = $this->app->auth();

		try {
			$this->app->session()->set('kirby.challenge.email', 'marge@simpsons.com');
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\PermissionException', $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => false], $e->getDetails());
		}
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeInvalidEmail()
	{
		$this->expectException('Kirby\Exception\NotFoundException');
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');

		$this->app->session()->set('kirby.challenge.email', 'invalid@example.com');
		$this->app->session()->set('kirby.challenge.type', 'email');
		$this->auth->verifyChallenge('123456');
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeRateLimited()
	{
		$this->expectException('Kirby\Exception\PermissionException');
		$this->expectExceptionMessage('Rate limit exceeded');

		$session = $this->app->session();

		$this->auth->track('marge@simpsons.com');
		$this->auth->track('homer@simpsons.com');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'email');

		$this->auth->verifyChallenge('123456');
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeTimeLimited()
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time - 1);

		try {
			$this->auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\PermissionException', $e);
			$this->assertSame('Authentication challenge timeout', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}

		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.code'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.timeout'));
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeTimeLimitedNoDebug()
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => false
			]
		]);
		$auth = $this->app->auth();
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time - 1);

		try {
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf('Kirby\Exception\PermissionException', $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}

		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.code'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.timeout'));
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeInvalidCode()
	{
		$this->expectException('Kirby\Exception\PermissionException');
		$this->expectExceptionMessage('Invalid code');

		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->auth->verifyChallenge('654321');
	}

	/**
	 * @covers ::verifyChallenge
	 */
	public function testVerifyChallengeInvalidChallenge()
	{
		$this->expectException('Kirby\Exception\LogicException');
		$this->expectExceptionMessage('Invalid authentication challenge: test');

		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$session->set('kirby.challenge.type', 'test');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->auth->verifyChallenge('123456');
	}
}
