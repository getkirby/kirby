<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Service\Webauthn;
use Kirby\Auth\Service\WebauthnTest;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Drawer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserWebauthnDrawerController::class)]
#[CoversClass(UserCredentialDrawerController::class)]
class UserWebauthnDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.UserWebauthnDrawerController';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'urls'  => ['index' => 'https://example.com'],
			'users' => [
				[
					'id'       => 'test',
					'name'     => 'Test User',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('12345678')
				],
				[
					'id'       => 'admin',
					'email'    => 'admin@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('adminpass123')
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'site' => [
				'content' => ['title' => 'Test Site']
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function controller(): UserWebauthnDrawerController
	{
		return new UserWebauthnDrawerController($this->app->user('test'));
	}

	/**
	 * Registers a passkey for the test user and returns the assertion that
	 * re-completes the challenge (the account owner's removal confirmation),
	 * together with the id of a second passkey left in place to remove.
	 *
	 * @return array{0: string, 1: string}
	 */
	protected function passkeyAndConfirm(): array
	{
		$assert = WebauthnTest::assertion(Webauthn::for($this->app->user('test')));

		$this->app->user('test')->changeSecret('webauthn', [
			['id' => $assert['id'], 'publicKey' => $assert['publicKey'], 'counter' => 0],
			['id' => 'bbb', 'name' => 'Key 2']
		]);

		return [json_encode($assert['payload']), $assert['challenge']];
	}

	public function testFactory(): void
	{
		$controller = UserWebauthnDrawerController::factory('test');
		$this->assertInstanceOf(UserWebauthnDrawerController::class, $controller);
	}

	public function testConstructWithoutPermission(): void
	{
		// an editor may not manage another user's passkeys
		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You cannot change user secrets for test@getkirby.com');

		new UserWebauthnDrawerController($this->app->user('test'));
	}

	public function testLoad(): void
	{
		$controller = $this->controller();
		$drawer     = $controller->load();

		$this->assertInstanceOf(Drawer::class, $drawer);
		$this->assertSame('k-user-webauthn-drawer', $drawer->component);
		$this->assertSame('fingerprint', $drawer->icon);
		$this->assertNotNull($drawer->title);

		$props = $drawer->props();
		$this->assertSame('medium', $props['size']);
		$this->assertFalse($props['submitButton']);
		$this->assertSame([], $props['credentials']);

		// an admin managing another user only confirms the action, so no
		// assertion options are handed over
		$this->assertNull($props['assertion']);

		// the relying party is scoped to the site host
		$this->assertSame('example.com', $props['registration']['rpId']);

		// the challenge is persisted for later verification
		$this->assertSame(
			$props['registration']['challenge'],
			$this->app->session()->get('kirby.webauthn.test')
		);
	}

	public function testLoadWithExistingCredentials(): void
	{
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'AQID', 'name' => 'Key 1']
		]);

		$props = $this->controller()->load()->props();

		$this->assertCount(1, $props['credentials']);
		$this->assertSame('Key 1', $props['credentials'][0]['name']);

		// registered credentials are excluded from the new key options
		$this->assertCount(1, $props['registration']['excludeCredentials']);
		$this->assertSame('AQID', $props['registration']['excludeCredentials'][0]['id']);
	}

	public function testLoadForAccount(): void
	{
		// the account owner re-completes a passkey assertion to remove one,
		// so the assertion options are handed to the drawer to complete
		$this->app->impersonate('test@getkirby.com');

		// the owner receives assertion options to complete client-side
		$props = $this->controller()->load()->props();
		$this->assertSame('example.com', $props['assertion']['rpId']);
	}

	public function testSubmitRemove(): void
	{
		// an admin removes another user's passkey with their own password
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'aaa', 'name' => 'Key 1'],
			['id' => 'bbb', 'name' => 'Key 2']
		]);

		$this->setRequest([
			'action'   => 'remove',
			'id'       => 'aaa',
			'password' => 'adminpass123'
		]);
		$this->app->impersonate('admin');

		$result = $this->controller()->submit();

		$this->assertTrue($result);

		$remaining = $this->app->user('test')->secret('webauthn');
		$this->assertCount(1, $remaining);
		$this->assertSame('bbb', $remaining[0]['id']);
	}

	public function testSubmitRemoveLastCredential(): void
	{
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'aaa', 'name' => 'Key 1']
		]);

		$this->setRequest([
			'action'   => 'remove',
			'id'       => 'aaa',
			'password' => 'adminpass123'
		]);
		$this->app->impersonate('admin');

		$result = $this->controller()->submit();

		$this->assertTrue($result);
		// the secret is cleared entirely once the last passkey is gone
		$this->assertNull($this->app->user('test')->secret('webauthn'));
	}

	public function testSubmitRemoveNotFound(): void
	{
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'aaa', 'name' => 'Key 1']
		]);

		$this->setRequest([
			'action'   => 'remove',
			'id'       => 'zzz',
			'password' => 'adminpass123'
		]);
		$this->app->impersonate('admin');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Passkey could not be found');

		$this->controller()->submit();
	}

	public function testSubmitRemoveAsAccount(): void
	{
		// the account owner removes a passkey by re-completing an assertion
		// with any registered passkey, proving they still control the factor
		[$confirm, $challenge] = $this->passkeyAndConfirm();

		$this->setRequest([
			'action'        => 'remove',
			'id'            => 'bbb',
			'authorization' => $confirm
		]);
		$this->app->impersonate('test@getkirby.com');
		$this->app->session()->set('kirby.security.authorize.test', [
			'public' => null,
			'secret' => $challenge
		]);

		$this->assertTrue($this->controller()->submit());

		$remaining = $this->app->user('test')->secret('webauthn');
		$this->assertCount(1, $remaining);
		$this->assertSame('AQIDBA', $remaining[0]['id']);
	}

	public function testSubmitRemoveAsAccountWithInvalidAssertion(): void
	{
		// an invalid assertion blocks the removal so a hijacked session
		// cannot lock the legitimate user out of their passkeys
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'aaa', 'name' => 'Key 1']
		]);

		$this->setRequest([
			'action'        => 'remove',
			'id'            => 'aaa',
			'authorization' => 'not-valid'
		]);
		$this->app->impersonate('test@getkirby.com');
		$this->app->session()->set('kirby.security.authorize.test', ['secret' => 'AQID']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey data');

		$this->controller()->submit();
	}

	public function testSubmitRemoveAsAdminWithWrongPassword(): void
	{
		$this->app->user('test')->changeSecret('webauthn', [
			['id' => 'aaa', 'name' => 'Key 1']
		]);

		$this->setRequest([
			'action'   => 'remove',
			'id'       => 'aaa',
			'password' => 'wrong-password'
		]);
		$this->app->impersonate('admin');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$this->controller()->submit();
	}

	public function testSubmitCreate(): void
	{
		['payload' => $credential, 'challenge' => $challenge] = WebauthnTest::attestation(
			Webauthn::for($this->app->user('test'))
		);

		$this->setRequest([
			'action'     => 'create',
			'credential' => $credential,
			'name'       => 'My Laptop'
		]);
		$this->app->impersonate('test');
		$this->app->session()->set('kirby.webauthn.test', $challenge);

		$this->assertTrue($this->controller()->submit());

		// the verified passkey is stored under the provided name
		$stored = $this->app->user('test')->secret('webauthn');
		$this->assertCount(1, $stored);
		$this->assertSame('My Laptop', $stored[0]['name']);
		$this->assertSame('AQIDBA', $stored[0]['id']);
		$this->assertSame('none', $stored[0]['attestation']);
	}

	public function testSubmitCreateForOtherUser(): void
	{
		// an admin must not register a passkey for another user: the
		// credential would live on the admin's device and let them log
		// in as that user
		$this->setRequest([
			'action'     => 'create',
			'credential' => 'anything',
			'name'       => 'My Laptop'
		]);
		$this->app->impersonate('admin');

		try {
			$this->controller()->submit();
			$this->fail('Expected PermissionException was not thrown');
		} catch (PermissionException) {
			// the target user's account must be left untouched
			$this->assertNull($this->app->user('test')->secret('webauthn'));
		}
	}

	public function testSubmitCreateWithoutName(): void
	{
		['payload' => $credential, 'challenge' => $challenge] = WebauthnTest::attestation(
			Webauthn::for($this->app->user('test'))
		);

		// no name provided → an incremental fallback name is generated
		$this->setRequest([
			'action'     => 'create',
			'credential' => $credential
		]);
		$this->app->impersonate('test');
		$this->app->session()->set('kirby.webauthn.test', $challenge);

		$this->controller()->submit();

		$stored = $this->app->user('test')->secret('webauthn');
		$this->assertSame('#1', $stored[0]['name']);
	}

	public function testSubmitCreateWithInvalidCredential(): void
	{
		// a malformed registration payload is rejected before anything
		// is stored (the happy path needs a real authenticator attestation)
		$this->setRequest(['action' => 'create', 'credential' => 123]);
		$this->app->impersonate('test');
		$this->app->session()->set('kirby.webauthn.test', 'challenge');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey data');

		$this->controller()->submit();
	}

	public function testSubmitWithInvalidAction(): void
	{
		$this->setRequest(['action' => 'nope']);
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid passkey action');

		$this->controller()->submit();
	}
}
