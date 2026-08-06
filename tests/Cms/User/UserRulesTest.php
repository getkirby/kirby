<?php

namespace Kirby\Cms;

use Kirby\Blueprint\UserBlueprint;
use Kirby\Exception\AbilityException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Guards\UserGuards;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UserRules::class)]
class UserRulesTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserRules';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roots' => [
				'site' => static::FIXTURES
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
			],
			'users' => [
				[
					'id'    => 'admin',
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				],
				[
					'email' => 'another-admin@domain.com',
					'role'  => 'admin'
				],
				[
					'email' => 'user@domain.com',
					'role'  => 'editor'
				],
				[
					'email' => 'another-user@domain.com',
					'role'  => 'editor'
				]
			]
		]);
	}

	public static function validDataProvider(): array
	{
		return [
			['Email', 'editor@domain.com'],
			['Language', 'en'],
			['Password', '12345678'],
			['Role', 'editor']
		];
	}

	#[DataProvider('validDataProvider')]
	public function testChangeValid(string $key, string $value): void
	{
		$this->app->impersonate('admin@domain.com');
		$user = $this->app->user('user@domain.com');

		$this->expectNotToPerformAssertions();

		UserRules::{'change' . $key}($user, $value);
	}

	public static function invalidDataProvider(): array
	{
		return [
			['Email', 'domain.com', 'Please enter a valid email address'],
			['Language', 'english', 'Please enter a valid language'],
			['Password', '1234', 'Please enter a valid password. Passwords must be at least 8 characters long.'],
			['Password', str_repeat('1234', 300), 'Please enter a valid password. Passwords must not be longer than 1000 characters.'],
			['Role', 'rockstar', 'Please enter a valid role']
		];
	}

	#[DataProvider('invalidDataProvider')]
	public function testChangeInvalid(
		string $key,
		string $value,
		string $message
	): void {
		$this->app->impersonate('admin@domain.com');
		$user = $this->app->user('user@domain.com');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage($message);

		$this->assertTrue(UserRules::{'change' . $key}($user, $value));
	}

	public static function missingPermissionProvider(): array
	{
		return [
			['Email', 'test@domain.com', 'You are not allowed to change the email for the user "user@domain.com"'],
			['Language', 'english', 'You are not allowed to change the language for the user "user@domain.com"'],
			['Name', 'Test', 'You are not allowed to change the name for the user "user@domain.com"'],
			['Password', '1234', 'You are not allowed to change the password for the user "user@domain.com"'],
		];
	}

	#[DataProvider('missingPermissionProvider')]
	public function testChangeWithoutPermission(
		string $key,
		string $value,
		string $message
	): void {
		$this->app->impersonate('nobody');
		$user = $this->app->user('user@domain.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage($message);

		UserRules::{'change' . $key}($user, $value);
	}

	public function testChangeEmailDuplicate(): void
	{
		$this->app->impersonate('admin@domain.com');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.user.duplicate');

		$user = $this->app->user('user@domain.com');
		UserRules::changeEmail($user, 'admin@domain.com');
	}

	public function testChangeRoleWithoutPermissions(): void
	{
		$this->app->impersonate('nobody');
		$user = $this->app->user('another-user@domain.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the role for the user "another-user@domain.com"');

		UserRules::changeRole($user, 'editor');
	}

	public function testChangeRoleFromAdminByAdmin(): void
	{
		$this->app->impersonate('admin@domain.com');

		$this->expectNotToPerformAssertions();

		$user = $this->app->user('another-admin@domain.com');
		UserRules::changeRole($user, 'editor');
	}

	public function testChangeRoleFromAdminByNonAdmin(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeRole.demoteAdmin');

		$this->app->impersonate('user@domain.com');

		$user = $this->app->user('admin@domain.com');
		UserRules::changeRole($user, 'editor');
	}

	public function testChangeRoleToRoleThatCannotBeAssigned(): void
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

		$this->app->impersonate('user@domain.com');

		$user = $this->app->user('user@domain.com');

		$this->assertCount(0, $user->roles());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		UserRules::changeRole($user, 'manager');
	}

	public function testChangeRoleToRoleThatCanBeAssigned(): void
	{
		$this->expectNotToPerformAssertions();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'users' => [
							'access'     => true,
							'changeRole' => true,
							'create'     => true
						]
					]
				],
				['name' => 'author']
			]
		]);

		$this->app->impersonate('user@domain.com');

		UserRules::changeRole(
			$this->app->user('another-user@domain.com'),
			'author'
		);
	}

	public function testChangeRoleToAdminByAdmin(): void
	{
		$this->app->impersonate('admin@domain.com');

		$this->expectNotToPerformAssertions();

		$user = $this->app->user('user@domain.com');
		UserRules::changeRole($user, 'admin');
	}

	public function testChangeRoleToAdminByNonAdmin(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeRole.toAdmin');

		$this->app->impersonate('user@domain.com');

		$user = $this->app->user('another-user@domain.com');
		UserRules::changeRole($user, 'admin');
	}

	public function testChangeRoleLastAdmin(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.user.changeRole.lastAdmin');

		$this->app = new App([
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('admin@domain.com');

		$user = $this->app->user('admin@domain.com');
		UserRules::changeRole($user, 'editor');
	}

	public function testChangeSecret(): void
	{
		$this->expectNotToPerformAssertions();

		// as user for themselves
		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('user@domain.com');
		UserRules::changeSecret($user, 'my-secret', 'abcdef');
		UserRules::changeSecret($user, 'my-secret', null);

		// as admin for other users
		$this->app->impersonate('admin@domain.com');
		$user = $this->app->user('user@domain.com');
		UserRules::changeSecret($user, 'my-secret', 'abcdef');
		UserRules::changeSecret($user, 'my-secret', null);
	}

	public function testChangeSecretAsAnotherUser(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeSecret');

		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('another-user@domain.com');
		UserRules::changeSecret($user, 'my-secret', 'abcdef');
	}

	/**
	 * @deprecate 6.0.0
	 */
	public function testChangeTotp(): void
	{
		$this->expectNotToPerformAssertions();

		// as user for themselves
		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('user@domain.com');
		UserRules::changeTotp($user, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		UserRules::changeTotp($user, null);

		// as admin for other users
		$this->app->impersonate('admin@domain.com');
		$user = $this->app->user('user@domain.com');
		UserRules::changeTotp($user, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		UserRules::changeTotp($user, null);
	}

	/**
	 * @deprecate 6.0.0
	 */
	public function testChangeTotpAsAnotherUser(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeSecret');

		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('another-user@domain.com');
		UserRules::changeTotp($user, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
	}

	/**
	 * @deprecate 6.0.0
	 */
	public function testChangeTotpInvalidSecret(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('user@domain.com');
		UserRules::changeTotp($user, 'foo');
	}

	public function testCreate(): void
	{
		$this->app->impersonate('admin@domain.com');

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $this->app
		]);

		$this->expectNotToPerformAssertions();

		UserRules::create($user, $props);
	}

	public function testCreateWithDuplicateEmail(): void
	{
		$this->app->impersonate('admin@domain.com');

		$user = new User($props = [
			'email'    => 'user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $this->app
		]);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.user.duplicate');

		UserRules::create($user, $props);
	}

	public function testCreateWithInvalidEmail(): void
	{
		$this->app->impersonate('admin@domain.com');

		$user = new User($props = [
			'email'    => 'not-an-email',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $this->app
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.email.invalid');

		UserRules::create($user, $props);
	}

	public function testCreateWithInvalidLanguage(): void
	{
		$this->app->impersonate('admin@domain.com');

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'does-not-exist',
			'kirby'    => $this->app
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.language.invalid');

		UserRules::create($user, $props);
	}

	public function testCreateWithReservedId(): void
	{
		$this->app->impersonate('admin@domain.com');

		$user = new User($props = [
			'id'       => 'nobody',
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $this->app
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.id.reserved');

		UserRules::create($user, $props);
	}

	public function testCreateFirstUserWithoutRole(): void
	{
		$app = new App();

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $app
		]);

		$this->expectNotToPerformAssertions();

		UserRules::create($user, $props);
	}

	public function testCreateFirstUserWithoutPassword(): void
	{
		$app = new App();

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '',
			'language' => 'en',
			'kirby'    => $app
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		UserRules::create($user, $props);
	}

	public function testCreateInstallation(): void
	{
		$app  = new App();
		$user = new User(
			$props = [
				'email'    => 'admin@domain.com',
				'password' => '12345678',
				'language' => 'en',
				'role'     => 'admin',
				'kirby'    => $app
			]
		);

		$this->expectNotToPerformAssertions();

		UserRules::create($user, $props);
	}

	public function testCreateAdminAsEditor(): void
	{
		$this->app->impersonate('user@domain.com');

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'role'     => 'admin',
			'kirby'    => $this->app
		]);

		$this->expectException(AbilityException::class);
		$this->expectExceptionMessage('You are not allowed to create admin accounts');

		UserRules::create($user, $props);
	}

	public function testCreatePermissions(): void
	{
		$this->app->impersonate('nobody');

		$user = new User([
			'id'       => 'test',
			'email'    => 'test@getkirby.com',
			'language' => 'en'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create this user');

		UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'editor'
		]);
	}

	public function testCreateInvalidRole(): void
	{
		$this->app->impersonate('user@domain.com');

		$user = new User([
			'id'       => 'test',
			'email'    => 'test@getkirby.com',
			'language' => 'en'
		]);

		// no role
		UserRules::create($user, [
			'password' => 12345678
		]);

		// role: nobody
		UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'nobody'
		]);

		// role: default
		UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'default'
		]);

		// invalid role
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid role');

		UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'foo'
		]);
	}

	public function testCreateAvatar(): void
	{
		$this->expectNotToPerformAssertions();

		$this->app->impersonate('admin@domain.com');
		$user = $this->app->user('user@domain.com');

		UserRules::createAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testCreateAvatarWithoutPermission(): void
	{
		$blueprint = $this->createStub(UserBlueprint::class);
		$blueprint->method('optionForUser')->willReturn(false);

		$user = $this->createStub(User::class);
		$user->method('blueprint')->willReturn($blueprint);
		$user->method('username')->willReturn('test');
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "test"');

		UserRules::createAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testCreateAvatarWhenAvatarExists(): void
	{
		$avatar = $this->createStub(File::class);
		$avatar->method('filename')->willReturn('profile.jpg');

		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn($avatar);
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.duplicate');

		UserRules::createAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testDeleteAvatar(): void
	{
		$this->expectNotToPerformAssertions();

		$avatar = $this->createStub(File::class);

		$blueprint = $this->createStub(UserBlueprint::class);
		$blueprint->method('optionForUser')->willReturn(true);

		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn($avatar);
		$user->method('blueprint')->willReturn($blueprint);
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		UserRules::deleteAvatar($user);
	}

	public function testDeleteAvatarNotFound(): void
	{
		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn(null);
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.notFound');

		UserRules::deleteAvatar($user, '/tmp/avatar.jpg');
	}

	public function testDeleteAvatarWithoutPermission(): void
	{
		$blueprint = $this->createStub(UserBlueprint::class);
		$blueprint->method('optionForUser')->willReturn(false);

		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn($this->createStub(File::class));
		$user->method('blueprint')->willReturn($blueprint);
		$user->method('username')->willReturn('test');
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "test"');

		UserRules::deleteAvatar($user);
	}

	public function testReplaceAvatar(): void
	{
		$this->expectNotToPerformAssertions();

		$avatar = $this->createStub(File::class);

		$blueprint = $this->createStub(UserBlueprint::class);
		$blueprint->method('optionForUser')->willReturn(true);

		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn($avatar);
		$user->method('blueprint')->willReturn($blueprint);
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		UserRules::replaceAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testReplaceAvatarNotFound(): void
	{
		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn(null);
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.notFound');

		UserRules::replaceAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testReplaceAvatarWithoutPermission(): void
	{
		$blueprint = $this->createStub(UserBlueprint::class);
		$blueprint->method('optionForUser')->willReturn(false);

		$user = $this->createStub(User::class);
		$user->method('avatar')->willReturn($this->createStub(File::class));
		$user->method('blueprint')->willReturn($blueprint);
		$user->method('username')->willReturn('test');
		$user->method('guards')->willReturn(new UserGuards(model: $user, user: $user));

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "test"');

		UserRules::replaceAvatar($user, '/tmp/avatar.jpg', 'jpg');
	}

	public function testUpdate(): void
	{
		$this->expectNotToPerformAssertions();

		$this->app->impersonate('admin@domain.com');
		$user = new User(['email' => 'user@domain.com']);

		UserRules::update($user, $input = [
			'zodiac' => 'lion'
		], $input);
	}

	public function testDelete(): void
	{
		$this->expectNotToPerformAssertions();

		$this->app->impersonate('admin@domain.com');
		$user = new User(['email' => 'user@domain.com']);

		UserRules::delete($user);
	}

	public function testDeleteLastAdmin(): void
	{
		$app = new App([
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('admin@domain.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.delete.lastAdmin');

		UserRules::delete($app->user('admin@domain.com'));
	}

	public function testDeleteLastUser(): void
	{
		$app = new App([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				[
					'email' => 'user@domain.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('user@domain.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.delete.lastUser');

		UserRules::delete($app->user('user@domain.com'));
	}

	public function testDeletePermissions(): void
	{
		$this->app->impersonate('nobody');
		$user = $this->app->user('another-user@domain.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the user "another-user@domain.com"');

		UserRules::delete($user);
	}

	public static function validIdProvider(): array
	{
		return [
			['account'],
			['kirby'],
			['nobody']
		];
	}

	public function testValidAvatar(): void
	{
		$this->expectNotToPerformAssertions();

		$user = $this->app->impersonate('user@domain.com');

		UserRules::validAvatar($user, __DIR__ . '/../../Api/Routes/fixtures/avatar.jpg', 'jpg');
	}

	public function testValidAvatarWithInvalidExtension(): void
	{
		$user = $this->app->impersonate('user@domain.com');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid file type: document');

		UserRules::validAvatar($user, '/tmp/file.pdf', 'pdf');
	}

	public function testValidAvatarWithInvalidMime(): void
	{
		$source = static::TMP . '/fake-image.jpg';
		file_put_contents($source, 'this is not an image');

		$user = $this->app->impersonate('user@domain.com');

		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.mime.invalid');

		UserRules::validAvatar($user, $source, 'jpg');
	}

	#[DataProvider('validIdProvider')]
	public function testValidId(string $id): void
	{
		$user = new User(['email' => 'test@getkirby.com']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"' . $id . '" is a reserved word and cannot be used as user id');

		UserRules::validId($user, $id);
	}

	public function testValidIdWhenDuplicateIsFound(): void
	{
		$user = new User(['email' => 'test@getkirby.com']);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A user with this id exists');

		UserRules::validId($user, 'admin');
	}

	public function testValidRole(): void
	{
		$user = new User(['email' => 'test@getkirby.com']);

		$this->expectNotToPerformAssertions();

		UserRules::validRole($user, 'editor');
	}

	public function testValidRoleWithInvalidRole(): void
	{
		$user = new User(['email' => 'test@getkirby.com']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.role.invalid');

		UserRules::validRole($user, 'does-not-exist');
	}
}
