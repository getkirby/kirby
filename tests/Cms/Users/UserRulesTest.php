<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

class UserRulesTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function app()
	{
		return new App([
			'roots' => [
				'site' => static::FIXTURES
			]
		]);
	}

	public function appWithAdmin()
	{
		return $this->app()->clone([
			'user' => 'admin@domain.com',
			'users' => [
				['email' => 'user@domain.com', 'role' => 'editor'],
				['email' => 'admin@domain.com', 'role' => 'admin']
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

	/**
	 * @dataProvider validDataProvider
	 */
	public function testChangeValid($key, $value)
	{
		$kirby = $this->appWithAdmin();
		$user  = $kirby->user('user@domain.com');

		$this->assertTrue(UserRules::{'change' . $key}($user, $value));
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

	/**
	 * @dataProvider invalidDataProvider
	 */
	public function testChangeInvalid($key, $value, $message)
	{
		$kirby = $this->appWithAdmin();
		$user  = $kirby->user('user@domain.com');

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

	/**
	 * @dataProvider missingPermissionProvider
	 */
	public function testChangeWithoutPermission($key, $value, $message)
	{
		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('__call')->with('change' . $key)->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('permissions')->willReturn($permissions);
		$user->method('username')->willReturn('test');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage($message);

		UserRules::{'change' . $key}($user, $value);
	}

	public function testChangeEmailDuplicate()
	{
		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.user.duplicate');

		$kirby = $this->appWithAdmin();

		UserRules::changeEmail($kirby->user('user@domain.com'), 'admin@domain.com');
	}

	public function testChangeRoleWithoutPermissions()
	{
		$kirby = new App([
			'roots' => [
				'site' => static::FIXTURES,
			],
			'user' => 'admin@domain.com',
			'users' => [
				['email' => 'editor@domain.com', 'role' => 'admin'],
				['email' => 'admin@domain.com', 'role' => 'admin']
			]
		]);

		$kirby->impersonate('admin@domain.com');

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('__call')->with('changeRole')->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($kirby);
		$user->method('permissions')->willReturn($permissions);
		$user->method('username')->willReturn('test');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the role for the user "test"');

		UserRules::changeRole($user, 'admin');
	}

	public function testChangeRoleFromAdminByAdmin()
	{
		$kirby = new App([
			'roots' => [
				'site' => static::FIXTURES,
			],
			'user' => 'admin@domain.com',
			'users' => [
				['email' => 'user@domain.com', 'role' => 'admin'],
				['email' => 'admin@domain.com', 'role' => 'admin']
			]
		]);
		$kirby->impersonate('admin@domain.com');

		$this->assertTrue(UserRules::changeRole($kirby->user('user@domain.com'), 'editor'));
	}

	public function testChangeRoleFromAdminByNonAdmin()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeRole.permission');

		$kirby = new App([
			'roots' => [
				'site' => static::FIXTURES,
			],
			'user' => 'user@domain.com',
			'users' => [
				['email' => 'user@domain.com', 'role' => 'editor'],
				['email' => 'admin@domain.com', 'role' => 'admin'],
				['email' => 'another@domain.com', 'role' => 'admin']
			]
		]);
		$kirby->impersonate('user@domain.com');

		UserRules::changeRole($kirby->user('admin@domain.com'), 'editor');
	}

	public function testChangeRoleToAdminByAdmin()
	{
		$kirby = new App([
			'roots' => [
				'site' => static::FIXTURES,
			],
			'user' => 'user1@domain.com',
			'users' => [
				['email' => 'user1@domain.com', 'role' => 'admin'],
				['email' => 'user2@domain.com', 'role' => 'editor']
			]
		]);
		$kirby->impersonate('user1@domain.com');

		$this->assertTrue(UserRules::changeRole($kirby->user('user2@domain.com'), 'admin'));
	}

	public function testChangeRoleToAdminByNonAdmin()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeRole.toAdmin');

		$kirby = new App([
			'roots' => [
				'site' => static::FIXTURES,
			],
			'user' => 'user1@domain.com',
			'users' => [
				['email' => 'user1@domain.com', 'role' => 'editor'],
				['email' => 'user2@domain.com', 'role' => 'editor']
			]
		]);
		$kirby->impersonate('user1@domain.com');

		UserRules::changeRole($kirby->user('user2@domain.com'), 'admin');
	}

	public function testChangeRoleLastAdmin()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.user.changeRole.lastAdmin');

		$kirby = $this->appWithAdmin();
		$kirby->impersonate('admin@domain.com');

		UserRules::changeRole($kirby->user('admin@domain.com'), 'editor');
	}

	public function testChangeTotp()
	{
		$kirby = $this->app()->clone([
			'users' => [
				['email' => 'user@domain.com', 'role' => 'editor'],
				['email' => 'admin@domain.com', 'role' => 'admin']
			]
		]);

		// as user for themselves
		$kirby->impersonate('user@domain.com');
		$this->assertTrue(UserRules::changeTotp($kirby->user('user@domain.com'), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
		$this->assertTrue(UserRules::changeTotp($kirby->user('user@domain.com'), null));

		// as admin for other users
		$kirby->impersonate('admin@domain.com');
		$this->assertTrue(UserRules::changeTotp($kirby->user('user@domain.com'), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
		$this->assertTrue(UserRules::changeTotp($kirby->user('user@domain.com'), null));
	}

	public function testChangeTotpAsAnotherUser()
	{
		$this->expectException(PermissionException::class);

		$kirby = $this->app()->clone([
			'users' => [
				['email' => 'user1@domain.com', 'role' => 'editor'],
				['email' => 'user2@domain.com', 'role' => 'editor']
			]
		]);
		$kirby->impersonate('user1@domain.com');
		UserRules::changeTotp($kirby->user('user2@domain.com'), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
	}

	public function testChangeTotpInvalidSecret()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		$kirby = $this->app()->clone([
			'users' => [
				['email' => 'user@domain.com', 'role' => 'editor']
			]
		]);
		$kirby->impersonate('user@domain.com');
		UserRules::changeTotp($kirby->user('user@domain.com'), 'foo');
	}

	public function testCreate()
	{
		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'kirby'    => $this->appWithAdmin()
		]);

		$this->assertTrue(UserRules::create($user, $props));
	}

	public function testCreateFirstUserWithoutPassword()
	{
		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '',
			'language' => 'en',
			'kirby'    => $this->app()
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		UserRules::create($user, $props);
	}

	public function testCreateInstallation()
	{
		$app = $this->app()->clone();

		$user = new User(
			$props = [
				'email'    => 'admin@domain.com',
				'password' => '12345678',
				'language' => 'en',
				'role'     => 'admin',
				'kirby'    => $app
			]
		);

		$this->assertTrue(UserRules::create($user, $props));
	}

	public function testCreateAdminAsEditor()
	{
		$app = $this->app()->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$user = new User($props = [
			'email'    => 'new-user@domain.com',
			'password' => '12345678',
			'language' => 'en',
			'role'     => 'admin',
			'kirby'    => $app
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create this user');

		UserRules::create($user, $props);
	}

	public function testCreatePermissions()
	{
		$app = $this->app()->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('__call')->with('create')->willReturn(false);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($app);
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

	public function testCreateInvalidRole()
	{
		$app = $this->app()->clone([
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$permissions = $this->createMock(UserPermissions::class);
		$permissions->method('__call')->with('create')->willReturn(true);

		$user = $this->createMock(User::class);
		$user->method('kirby')->willReturn($app);
		$user->method('permissions')->willReturn($permissions);
		$user->method('id')->willReturn('test');
		$user->method('email')->willReturn('test@getkirby.com');
		$user->method('language')->willReturn('en');

		// no role
		$this->assertTrue(UserRules::create($user, [
			'password' => 12345678
		]));

		// role: nobody
		$this->assertTrue(UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'nobody'
		]));

		// role: default
		$this->assertTrue(UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'default'
		]));

		// invalid role
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid role');

		UserRules::create($user, [
			'password' => 12345678,
			'role'     => 'foo'
		]);
	}

	public function testUpdate()
	{
		$app  = $this->appWithAdmin();
		$user = new User(['email' => 'user@domain.com']);
		$this->assertTrue(UserRules::update($user, $input = [
			'zodiac' => 'lion'
		], $input));
	}

	public function testDelete()
	{
		$app  = $this->appWithAdmin();
		$user = new User(['email' => 'user@domain.com']);
		$this->assertTrue(UserRules::delete($user));
	}

	public function testDeleteLastAdmin()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.user.delete.lastAdmin');

		$kirby = $this->appWithAdmin();
		UserRules::delete($kirby->user('admin@domain.com'));
	}

	public function testDeleteLastUser()
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
		$permissions->method('__call')->with('delete')->willReturn(false);

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

	/**
	 * @dataProvider validIdProvider
	 */
	public function testValidId(string $id)
	{
		$user = new User(['email' => 'test@getkirby.com']);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"' . $id . '" is a reserved word and cannot be used as user id');

		UserRules::validId($user, $id);
	}

	public function testValidIdWhenDuplicateIsFound()
	{
		$app = $this->app()->clone([
			'users' => [
				['id' => 'admin', 'email' => 'admin@getkirby.com', 'role' => 'admin'],
			]
		]);

		$user = new User(['email' => 'test@getkirby.com', 'kirby' => $app]);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A user with this id exists');

		UserRules::validId($user, 'admin');
	}
}
