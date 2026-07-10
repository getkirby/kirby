<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Auth\Service\Webauthn;
use Kirby\Auth\Service\WebauthnTest;
use Kirby\Auth\TestCase;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Panel\Ui\Component;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Challenge::class)]
#[CoversClass(WebauthnChallenge::class)]
class WebauthnChallengeTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.WebauthnChallenge';

	protected User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'marge@simpsons.com',
					'id'    => 'marge',
				],
			]
		]);

		$this->user = $this->app->user('marge');
	}

	protected function writeCredentials(mixed $webauthn): void
	{
		F::write(
			static::TMP . '/site/accounts/marge/.htpasswd',
			"\n" . json_encode(['webauthn' => $webauthn])
		);
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	protected function assertion(int $signCount): array
	{
		$assert = WebauthnTest::assertion(
			Webauthn::for($this->user),
			$signCount,
			'getkirby.com'
		);

		$this->writeCredentials([[
			'id'        => $assert['id'],
			'publicKey' => $assert['publicKey'],
			'counter'   => 0
		]]);

		return [json_encode($assert['payload']), $assert['challenge']];
	}

	public function testCreate(): void
	{
		$this->writeCredentials([['id' => 'AQID']]);

		$challenge = new WebauthnChallenge($this->user, 'login', 600);
		$pending   = $challenge->create();

		$this->assertInstanceOf(Pending::class, $pending);

		$public = $pending->public();
		$this->assertIsString($public['challenge']);
		$this->assertArrayHasKey('allowCredentials', $public);
		// the challenge is stored as the secret for later verification
		$this->assertSame($public['challenge'], $pending->secret());
	}

	public function testForm(): void
	{
		$challenge = new WebauthnChallenge($this->user, 'login', 600);
		$pending   = new Pending();
		$form      = $challenge->form($pending);

		$this->assertInstanceOf(Component::class, $form);

		$rendered = $form->render();
		$this->assertSame('k-login-webauthn-challenge-form', $rendered['component']);
		$this->assertSame('fingerprint', $rendered['props']['submit']['icon']);
		$this->assertSame('Login with passkey', $rendered['props']['submit']['label']);
		$this->assertSame('marge@simpsons.com', $rendered['props']['user']);
	}

	public function testIsAvailable(): void
	{
		// at least one registered passkey
		$this->writeCredentials([['id' => 'AQID']]);
		$this->assertTrue(WebauthnChallenge::isAvailable($this->user, 'login'));

		// an empty passkey list (e.g. after removing all passkeys)
		$this->writeCredentials([]);
		$this->assertFalse(WebauthnChallenge::isAvailable($this->user, 'login'));

		// no webauthn secret at all
		F::write(static::TMP . '/site/accounts/marge/.htpasswd', '');
		$this->assertFalse(WebauthnChallenge::isAvailable($this->user, 'login'));
	}

	public function testIsSingleUse(): void
	{
		$challenge = new WebauthnChallenge($this->user, 'login', 600);
		$this->assertTrue($challenge->isSingleUse());
	}

	public function testMode(): void
	{
		$challenge = new WebauthnChallenge($this->user, '2fa', 300);
		$this->assertSame('2fa', $challenge->mode());
	}

	public function testSettings(): void
	{
		$settings = WebauthnChallenge::settings($this->user);
		$this->assertCount(1, $settings);

		$button = $settings[0]->render()['props'];
		$this->assertSame('fingerprint', $button['icon']);
		$this->assertSame('Passkeys', $button['text']);
	}

	public function testTimeout(): void
	{
		$challenge = new WebauthnChallenge($this->user, '2fa', 300);
		$this->assertSame(300, $challenge->timeout());
	}

	public function testType(): void
	{
		$challenge = new WebauthnChallenge($this->user, '2fa', 300);
		$this->assertSame('webauthn', $challenge->type());
	}

	public function testUser(): void
	{
		$challenge = new WebauthnChallenge($this->user, '2fa', 300);
		$this->assertSame($this->user, $challenge->user());
	}

	public function testVerify(): void
	{
		$challenge = new WebauthnChallenge($this->user, 'login', 600);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey data');

		$challenge->verify('not-valid-json', new Pending(secret: 'AQID'));
	}

	public function testVerifySucceeds(): void
	{
		[$payload, $secret] = $this->assertion(signCount: 9);

		$this->app->impersonate('kirby');

		$challenge = new WebauthnChallenge($this->user, 'login', 600);
		$result    = $challenge->verify($payload, new Pending(secret: $secret));

		$this->assertTrue($result);

		// the authenticator's new counter is persisted on the credential
		$this->assertSame(
			9,
			$this->app->user('marge')->secret('webauthn')[0]['counter']
		);
	}
}
