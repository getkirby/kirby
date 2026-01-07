<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Auth\TestCase;
use Kirby\Cms\User;
use Kirby\Email\Email;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Challenge::class)]
#[CoversClass(EmailChallenge::class)]
class EmailChallengeTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/../fixtures';
	public const string TMP = KIRBY_TMP_DIR . '/Auth.EmailChallenge';

	protected User $user;

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'content' => [
					'title' => 'Test Site'
				]
			],
			'users' => [
				[
					'email' => 'marge@simpsons.com',
					'id'    => 'marge',
				],
				[
					'email'    => 'bart@simpsons.com',
					'language' => 'de'
				]
			]
		]);

		$this->user = $this->app->user('marge');
	}

	public function testCreate(): void
	{
		$challenge = new EmailChallenge($this->user, 'login', 7.3 * 60);
		$pending   = $challenge->create();

		$this->assertCount(1, Email::$emails);

		$email = Email::$emails[0];

		$this->assertSame('noreply@getkirby.com', $email->from());
		$this->assertSame('Test Site', $email->fromName());
		$this->assertSame(['marge@simpsons.com' => ''], $email->to());
		$this->assertSame('Your login code', $email->subject());

		$body  = $email->body()->text();
		preg_match('/[0-9]{3} [0-9]{3}/', $body, $code);
		$this->assertNotEmpty($code[0]);
		$code = str_replace(' ', '', $code[0]);
		$this->assertTrue(password_verify($code, $pending->secret()));

		$this->assertStringContainsString('login code', $body);
		$this->assertStringContainsString('7 minutes', $body);

		$pending2 = $challenge->create();
		$this->assertNotSame($pending->secret(), $pending2->secret());
	}

	public function testCreate2FA(): void
	{
		$challenge = new EmailChallenge($this->user, '2fa', 7.3 * 60);
		$pending   = $challenge->create();

		$this->assertCount(1, Email::$emails);

		$email = Email::$emails[0];

		$this->assertSame('Your login code', $email->subject());

		$body  = $email->body()->text();
		preg_match('/[0-9]{3} [0-9]{3}/', $body, $code);
		$this->assertNotEmpty($code[0]);
		$code = str_replace(' ', '', $code[0]);
		$this->assertTrue(password_verify($code, $pending->secret()));

		$this->assertStringContainsString('login code', $body);
		$this->assertStringContainsString('7 minutes', $body);
	}

	public function testCreatePasswordReset(): void
	{
		$challenge = new EmailChallenge($this->user, 'password-reset', 7.3 * 60);
		$pending   = $challenge->create();

		$this->assertCount(1, Email::$emails);

		$email = Email::$emails[0];

		$this->assertSame('Your password reset code', $email->subject());

		$body  = $email->body()->text();
		preg_match('/[0-9]{3} [0-9]{3}/', $body, $code);
		$this->assertNotEmpty($code[0]);
		$code = str_replace(' ', '', $code[0]);
		$this->assertTrue(password_verify($code, $pending->secret()));

		$this->assertStringContainsString('password reset code', $body);
		$this->assertStringContainsString('7 minutes', $body);
	}

	public function testCreateResetUserLanguage(): void
	{
		$user = $this->app->user('bart@simpsons.com');
		$challenge = new EmailChallenge($user, 'password-reset', 7.3 * 60);
		$challenge->create();

		$email = Email::$emails[0];
		$this->assertSame('Dein Anmeldecode', $email->subject());
	}

	public function testCreateCustom(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth.challenge.email.from'     => 'test@example.com',
				'auth.challenge.email.fromName' => 'Test',
				'auth.challenge.email.subject'  => 'Custom subject'
			],
			'templates' => [
				'emails/auth/login' => static::FIXTURES . '/auth.email.text.php'
			]
		]);

		$user = $this->app->user('bart@simpsons.com');
		$challenge = new EmailChallenge($user, 'login', 7.3 * 60);
		$challenge->create();

		$email = Email::$emails[0];

		$this->assertSame('test@example.com', $email->from());
		$this->assertSame('Test', $email->fromName());
		$this->assertSame('Custom subject', $email->subject());
		$this->assertStringContainsString(
			"bart@simpsons.com\nTest Site\n7\n",
			$email->body()->text()
		);
	}

	public function testCreateCustomHtml(): void
	{
		$this->app = $this->app->clone([
			'templates' => [
				'emails/auth/login'      => static::FIXTURES . '/auth.email.text.php',
				'emails/auth/login.html' => static::FIXTURES . '/auth.email.html.php'
			]
		]);

		$user = $this->app->user('marge@simpsons.com');
		$challenge = new EmailChallenge($user, 'login', 7.3 * 60);
		$challenge->create();

		$email = Email::$emails[0];

		$this->assertSame('noreply@getkirby.com', $email->from());
		$this->assertSame('Test Site', $email->fromName());
		$this->assertSame('Your login code', $email->subject());
		$this->assertStringContainsString(
			"marge@simpsons.com\nTest Site\n7\n",
			$email->body()->text()
		);
		$this->assertStringContainsString(
			"HTML: marge@simpsons.com\nTest Site\n7\n",
			$email->body()->html()
		);
	}

	public function testIsAvailable(): void
	{
		$this->assertTrue(EmailChallenge::isAvailable($this->user, 'login'));
		$this->assertTrue(EmailChallenge::isAvailable($this->user, '2fa'));
	}

	public function testIsEnabled(): void
	{
		$this->assertTrue(EmailChallenge::isEnabled($this->app->auth()));
	}

	public function testMode(): void
	{
		$challenge = new EmailChallenge($this->user, 'password-reset', 900);
		$this->assertSame('password-reset', $challenge->mode());
	}

	public function testTimeout(): void
	{
		$challenge = new EmailChallenge($this->user, 'password-reset', 900);
		$this->assertSame(900, $challenge->timeout());
	}

	public function testType(): void
	{
		$challenge = new EmailChallenge($this->user, 'password-reset', 900);
		$this->assertSame('email', $challenge->type());
	}

	public function testUser(): void
	{
		$challenge = new EmailChallenge($this->user, 'password-reset', 900);
		$this->assertSame($this->user, $challenge->user());
	}

	public function testVerify(): void
	{
		$hash      = password_hash('123456', PASSWORD_DEFAULT);
		$challenge = new EmailChallenge($this->user, 'login', 600);
		$data      = new Pending(secret: $hash);

		$this->assertTrue($challenge->verify('123456', $data));
		$this->assertTrue($challenge->verify('123 456', $data));
	}

	public function testVerifyMismatch(): void
	{
		$hash      = password_hash('123456', PASSWORD_DEFAULT);
		$challenge = new EmailChallenge($this->user, 'login', 600);
		$data      = new Pending(secret: $hash);

		$this->assertFalse($challenge->verify('654 321', $data));
	}
}
