<?php

namespace Kirby\Guards;

use Kirby\Cms\App;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserValidators::class)]
class UserValidatorsTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/../Api/Routes/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Guards.UserValidators';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'admin', 'email' => 'admin@getkirby.com', 'role' => 'admin'],
				['id' => 'editor', 'email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('admin@getkirby.com');
	}

	/**
	 * Creates a fresh installation without any users.
	 * `App::clone()` merges props recursively and could
	 * not remove the users of the default setup.
	 */
	protected function appWithoutUsers(): App
	{
		return $this->app = new App([
			'roots' => ['index' => static::TMP],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);
	}

	public function testAssignableRole(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateAssignableRole('editor'));
	}

	public function testAssignableRoleWithUnassignableRole(): void
	{
		// a role blueprint without a `permissions` key grants every
		// permission, so it must never be assignable by a user who
		// is not allowed to create it
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor', 'permissions' => ['users' => false]],
				['name' => 'manager']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$model = $this->app->user('editor@getkirby.com');

		$this->assertCount(0, $model->roles());

		$validators = new UserValidators(
			model: $model,
			user: $this->app->user()
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->validateAssignableRole('manager');
	}

	public function testAssignableRoleWithInvalidRole(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->validateAssignableRole('rockstar');
	}

	public function testAvatar(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->validateAvatar(static::FIXTURES . '/avatar.jpg', 'jpg')
		);
	}

	public function testAvatarMime(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->validateAvatarMime(static::FIXTURES . '/avatar.jpg')
		);
	}

	public function testAvatarMimeWithInvalidMime(): void
	{
		$source = static::TMP . '/fake-image.jpg';
		file_put_contents($source, 'this is not an image');

		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.mime.invalid');

		$validators->validateAvatarMime($source);
	}

	public function testAvatarType(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateAvatarType('jpg'));
	}

	public function testAvatarTypeWithInvalidType(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.invalid');

		$validators->validateAvatarType('pdf');
	}

	public function testCreatableRole(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateCreatableRole('editor'));
	}

	public function testCreatableRoleForFirstUser(): void
	{
		$this->appWithoutUsers();

		$validators = new UserValidators(
			model: new User(['email' => 'new@getkirby.com']),
			user: User::nobody()
		);

		$this->assertNull($validators->validateCreatableRole('admin'));
	}

	public function testCreatableRoleForFirstUserWithNonAdminRole(): void
	{
		$this->appWithoutUsers();

		$validators = new UserValidators(
			model: new User(['email' => 'new@getkirby.com']),
			user: User::nobody()
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->validateCreatableRole('editor');
	}

	public function testCreatableRoleWithUncheckedRole(): void
	{
		$validators = $this->validators();

		// these are resolved later on and must not be
		// checked against the creatable roles
		$this->assertNull($validators->validateCreatableRole(''));
		$this->assertNull($validators->validateCreatableRole('default'));
		$this->assertNull($validators->validateCreatableRole('nobody'));
	}

	public function testCreatableRoleWithInvalidRole(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->validateCreatableRole('rockstar');
	}

	public function testEmail(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->validateEmail('new@getkirby.com', strict: true)
		);
	}

	public function testEmailWithInvalidEmail(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.email.invalid');

		$validators->validateEmail('not-an-email');
	}

	public function testEmailWithOwnEmail(): void
	{
		$validators = $this->validators();

		// the user itself is ignored unless the check is strict
		$this->assertNull($validators->validateEmail('editor@getkirby.com'));
	}

	public function testEmailWithOwnEmailInStrictMode(): void
	{
		$validators = $this->validators();

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.user.duplicate');

		$validators->validateEmail('editor@getkirby.com', strict: true);
	}

	public function testEmailWithTakenEmail(): void
	{
		$validators = $this->validators();

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.user.duplicate');

		$validators->validateEmail('admin@getkirby.com');
	}

	public function testEnsureChangeSecret(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->ensure('changeSecret', 'totp', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567')
		);
	}

	public function testEnsureChangeSecretWithInvalidTotpSecret(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		$validators->ensure('changeSecret', 'totp', 'foo');
	}

	public function testEnsureChangeSecretWithOtherSecret(): void
	{
		$validators = $this->validators();

		// only TOTP secrets have a length to validate
		$this->assertNull(
			$validators->ensure('changeSecret', 'webauthn', ['credential'])
		);
	}

	public function testEnsureCreateAvatar(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->ensure('createAvatar', static::FIXTURES . '/avatar.jpg', 'jpg')
		);
	}

	public function testEnsureCreateAvatarWithInvalidType(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.invalid');

		$validators->ensure('createAvatar', static::FIXTURES . '/avatar.jpg', 'pdf');
	}

	public function testEnsureCreateFirstUserWithInvalidRole(): void
	{
		$this->appWithoutUsers();

		$validators = new UserValidators(
			model: new User([
				'email' => 'new@getkirby.com',
				'role'  => 'editor'
			]),
			user: User::nobody()
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->ensure('createFirstUser', [
			'password' => 'super-secure-password'
		]);
	}

	public function testEnsureReplaceAvatar(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->ensure('replaceAvatar', static::FIXTURES . '/avatar.jpg', 'jpg')
		);
	}

	public function testEnsureReplaceAvatarWithInvalidType(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.file.type.invalid');

		$validators->ensure('replaceAvatar', static::FIXTURES . '/avatar.jpg', 'pdf');
	}

	public function testId(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateId('new-user'));
	}

	public function testIdWithReservedId(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.id.reserved');

		$validators->validateId('kirby');
	}

	public function testIdWithExistingId(): void
	{
		$validators = $this->validators();

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A user with this id exists');

		$validators->validateId('admin');
	}

	public function testLanguage(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateLanguage('en'));
	}

	public function testLanguageWithInvalidLanguage(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.language.invalid');

		$validators->validateLanguage('english');
	}

	public function testNewPassword(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateNewPassword('12345678'));
	}

	public function testNewPasswordWithAllowedEmptyPassword(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateNewPassword('', allowEmpty: true));
	}

	public function testNewPasswordWithEmptyPassword(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.invalid');

		$validators->validateNewPassword('');
	}

	public function testNewPasswordWithInvalidPassword(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.invalid');

		$validators->validateNewPassword('1234');
	}

	public function testRole(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateRole('editor'));
	}

	public function testRoleWithInvalidRole(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		$validators->validateRole('rockstar');
	}

	public function testTotp(): void
	{
		$validators = $this->validators();

		$this->assertNull(
			$validators->validateTotp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567')
		);
	}

	public function testTotpWithNull(): void
	{
		$validators = $this->validators();

		$this->assertNull($validators->validateTotp(null));
	}

	public function testTotpWithInvalidSecret(): void
	{
		$validators = $this->validators();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		$validators->validateTotp('foo');
	}

	protected function user(): User
	{
		return $this->app->user('admin@getkirby.com');
	}

	protected function validators(): UserValidators
	{
		return new UserValidators(
			model: $this->app->user('editor@getkirby.com'),
			user: $this->user()
		);
	}
}
