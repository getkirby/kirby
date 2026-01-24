<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Auth\Service\Webauthn;
use Kirby\Auth\Service\WebauthnTest;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\UserNotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WebauthnMethod::class)]
class WebauthnMethodTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Method.Webauthn';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots'   => ['index' => static::TMP],
			'urls'    => ['index' => 'https://example.com'],
			'users'   => [
				['id' => 'marge', 'email' => 'marge@simpsons.com'],
				['id' => 'homer', 'email' => 'homer@simpsons.com']
			],
			'site'    => ['content' => ['title' => 'Example Site']],
			// surface the real underlying error instead of the generic
			// fallback so failure tests can assert the actual cause;
			// masking behaviour is covered separately with debug off
			'options' => ['auth' => ['debug' => true]]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
		App::destroy();
	}

	protected function method(): WebauthnMethod
	{
		return new WebauthnMethod(auth: $this->app->auth());
	}

	/**
	 * Registers a real passkey for the user and returns the matching
	 * assertion payload (JSON, as the browser would post it). The stored
	 * challenge is written to the login session so authenticate() can
	 * verify it. A signCount of 0 keeps verifyLogin's counter null (the
	 * common platform-authenticator case).
	 *
	 * @return string the serialized assertion payload
	 */
	protected function register(
		string $userId,
		int $signCount = 0,
		bool $withHandle = true
	): string {
		$webauthn = Webauthn::for($this->app->user($userId));
		$assert   = WebauthnTest::assertion($webauthn, $signCount);

		// store the credential in the user's secrets on disk
		F::write(
			static::TMP . '/site/accounts/' . $userId . '/.htpasswd',
			"\n" . json_encode([
				'webauthn' => [[
					'id'        => $assert['id'],
					'publicKey' => $assert['publicKey'],
					'counter'   => 0
				]]
			])
		);

		// persist the challenge for the login verification
		$this->app->session()->set('kirby.webauthn.login', $assert['challenge']);

		$payload = $assert['payload'];

		// discoverable passkeys carry the user id as their user handle
		if ($withHandle === true) {
			$payload['user'] = $webauthn->encode($userId);
		}

		return json_encode($payload);
	}

	public function testType(): void
	{
		$this->assertSame('webauthn', WebauthnMethod::type());
	}

	public function testIcon(): void
	{
		$this->assertSame('fingerprint', WebauthnMethod::icon());
	}

	public function testSettings(): void
	{
		$settings = WebauthnMethod::settings($this->app->user('marge'));

		$this->assertCount(1, $settings);
		$this->assertInstanceOf(Button::class, $settings[0]);
		$this->assertStringContainsString(
			'/security/method/webauthn',
			$settings[0]->render()['props']['drawer']
		);
	}

	public function testForm(): void
	{
		$method = $this->method();
		$form   = $method->form();

		$this->assertInstanceOf(Component::class, $form);

		$rendered = $form->render();
		$this->assertSame('k-login-webauthn-method-form', $rendered['component']);
		$this->assertSame('example.com', $rendered['props']['publicKey']['rpId']);

		// the challenge is persisted for the follow-up verification
		$this->assertSame(
			$rendered['props']['publicKey']['challenge'],
			$this->app->session()->get('kirby.webauthn.login')
		);
	}

	public function testAuthenticate(): void
	{
		$payload = $this->register('marge');
		$result  = $this->method()->authenticate(null, $payload);

		$this->assertSame('marge', $result->id());
		$this->assertTrue($result->isLoggedIn());
	}

	public function testAuthenticateWithoutUserHandle(): void
	{
		// no user handle → we no longer scan all accounts, so the
		// assertion cannot be tied to a user and login is rejected
		$payload = $this->register('marge', withHandle: false);

		$this->expectException(UserNotFoundException::class);

		$this->method()->authenticate(null, $payload);
	}

	public function testAuthenticateUpdatesCounter(): void
	{
		// a non-zero signature counter is written back to the credential;
		// impersonate an admin so the secret write is permitted
		$payload = $this->register('marge', signCount: 7);
		$this->app->impersonate('kirby');

		$this->method()->authenticate(null, $payload);

		$this->assertSame(7, $this->app->user('marge')->secret('webauthn')[0]['counter']);
	}

	public function testAuthenticateFailsVerification(): void
	{
		$payload = $this->register('marge');
		$this->app->session()->set('kirby.webauthn.login', 'wrong-challenge');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The passkey could not be verified');

		$this->method()->authenticate(null, $payload);
	}

	public function testAuthenticateUnknownUser(): void
	{
		$this->expectException(UserNotFoundException::class);
		$this->method()->authenticate(null, '{"id":"unknown"}');
	}

	public function testAuthenticateUnknownHandle(): void
	{
		$webauthn = Webauthn::site($this->app);
		$payload  = json_encode(['user' => $webauthn->encode('ghost')]);

		$this->expectException(UserNotFoundException::class);
		$this->method()->authenticate(null, $payload);
	}

	public function testAuthenticateMasksAndTracksFailure(): void
	{
		// with debug off, a failed attempt is reported with the generic
		// fallback (no oracle) and is counted towards the rate limit
		$this->app = $this->app->clone([
			'options' => ['auth' => ['debug' => false]]
		]);

		$thrown = false;

		try {
			$this->method()->authenticate(null, '{"id":"unknown"}');
		} catch (LoginNotPermittedException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);

		// the failed attempt bumped the IP-based counter
		$log = $this->app->auth()->limits()->log();
		$this->assertSame(1, array_values($log['by-ip'])[0]['trials']);
	}

	public function testAuthenticateBlockedByRateLimit(): void
	{
		$limits = $this->app->auth()->limits();

		// exhaust the IP-based trial budget (default: 10)
		for ($i = 0; $i < 10; $i++) {
			$limits->track(null, triggerHook: false);
		}

		// a blocked IP is rejected before any verification runs
		$this->expectException(RateLimitException::class);
		$this->method()->authenticate(null, $this->register('marge'));
	}
}
