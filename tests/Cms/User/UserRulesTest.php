<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UserRules::class)]
class UserRulesTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserRules';

	public function setUp(): void
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
			['Email', 'domain.com', 'You are not allowed to change the email for the user "test"'],
			['Language', 'english', 'You are not allowed to change the language for the user "test"'],
			['Name', 'Test', 'You are not allowed to change the name for the user "test"'],
			['Password', '1234', 'You are not allowed to change the password for the user "test"'],
		];
	}

	#[DataProvider('missingPermissionProvider')]
	public function testChangeWithoutPermission(
		string $key,
		string $value,
		string $message
	): void {
		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('can')->with('change' . $key)->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('permissions')->willReturn($permissions);
		$user->method('username')->willReturn('test');

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
		$this->app->impersonate('admin@domain.com');

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('can')->with('changeRole')->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($this->app);
		$user->method('permissions')->willReturn($permissions);
		$user->method('username')->willReturn('test');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the role for the user "test"');

		UserRules::changeRole($user, 'admin');
	}

	public function testChangeRoleFromAdminByAdmin(): void
	{
		$this->app->impersonate('admin@domain.com');

		$this->expectNotToPerformAssertions();

		$user = $this->app->user('another-admin@domain.com');
		UserRules::changeRole($user, 'editor');
	}

	public function testChangeRoleFromAdminByNonAdmin()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeRole.permission');

		$this->app->impersonate('user@domain.com');

		$user = $this->app->user('admin@domain.com');
		UserRules::changeRole($user, 'editor');
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
		$this->expectException(PermissionException::class);
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

	public function testChangeTotpAsAnotherUser(): void
	{
		$this->expectException(PermissionException::class);

		$this->app->impersonate('user@domain.com');
		$user = $this->app->user('another-user@domain.com');
		UserRules::changeTotp($user, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
	}

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

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create this user');

		UserRules::create($user, $props);
	}

	public function testCreatePermissions(): void
	{
		$this->app->impersonate('user@domain.com');

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('can')->with('create')->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($this->app);
		$user->method('permissions')->willReturn($permissions);
		$user->method('id')->willReturn('test');
		$user->method('email')->willReturn('test@getkirby.com');
		$user->method('language')->willReturn('en');

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

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('can')->with('create')->willReturn(true);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($this->app);
		$user->method('permissions')->willReturn($permissions);
		$user->method('id')->willReturn('test');
		$user->method('email')->willReturn('test@getkirby.com');
		$user->method('language')->willReturn('en');

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

	public function testUpdate(): void
	{
		$this->expectNotToPerformAssertions();

		$this->app->impersonate('admin@domain.com');
		$user = new User(['email' => 'user@domain.com']);

		UserRules::update($user, $input = [
			'zodiac' => 'lion'
		], $input);
	}

	public function testDelete()
	{
		$this->expectNotToPerformAssertions();

		$this->app->impersonate('admin@domain.com');
		$user = new User(['email' => 'user@domain.com']);

		UserRules::delete($user);
	}

	public function testDeleteLastAdmin(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.user.delete.lastAdmin');

		$app = new App([
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			]
		]);

		UserRules::delete($app->user('admin@domain.com'));
	}

	public function testDeleteLastUser(): void
	{
		$user = $this->createMock(User::class);
		$user->method('isLastAdmin')->willReturn(false);
		$user->method('isLastUser')->willReturn(true);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The last user cannot be deleted');

		UserRules::delete($user);
	}

	public function testDeletePermissions()
	{
		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('can')->with('delete')->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('permissions')->willReturn($permissions);
		$user->method('isLastAdmin')->willReturn(false);
		$user->method('isLastUser')->willReturn(false);
		$user->method('username')->willReturn('test');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the user "test"');

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
}
