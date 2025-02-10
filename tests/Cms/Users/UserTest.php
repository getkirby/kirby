<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;

class UserTestModel extends User
{
}

#[CoversClass(User::class)]
class UserTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.User';

	public function testAvatar()
	{
		$user = new User([
			'email' => 'user@domain.com'
		]);

		$this->assertNull($user->avatar());
	}

	public function testDefaultSiblings()
	{
		$user = new User(['email' => 'user@domain.com']);
		$this->assertInstanceOf(Users::class, $user->siblings());
	}

	public function testContent()
	{
		$user = new User([
			'email'   => 'user@domain.com',
			'content' => $content = ['name' => 'Test']
		]);

		$this->assertSame($content, $user->content()->toArray());
	}

	public function testInvalidContent()
	{
		$this->expectException(TypeError::class);

		$user = new User(['email' => 'user@domain.com', 'content' => 'something']);
	}

	public function testDefaultContent()
	{
		$user = new User(['email' => 'user@domain.com']);
		$this->assertInstanceOf(Content::class, $user->content());
	}

	public function testEmail()
	{
		$user = new User([
			'email' => $email = 'user@domain.com',
		]);

		$this->assertSame($email, $user->email());
	}

	public function testInvalidEmail()
	{
		$this->expectException(TypeError::class);
		new User(['email' => []]);
	}

	public function testIsAdmin()
	{
		$user = new User([
			'email' => 'test@getkirby.com',
			'role'  => 'admin'
		]);

		$this->assertTrue($user->isAdmin());

		$user = new User([
			'email' => 'test@getkirby.com',
			'role'  => 'editor'
		]);

		$this->assertFalse($user->isAdmin());
	}

	public function testIsKirby()
	{
		$user = new User([
			'id'   => 'kirby',
			'role' => 'admin'
		]);
		$this->assertTrue($user->isKirby());

		$user = new User([
			'role' => 'admin'
		]);
		$this->assertFalse($user->isKirby());

		$user = new User([
			'id'   => 'kirby',
		]);
		$this->assertFalse($user->isKirby());

		$user = new User([
			'emai' => 'kirby@getkirby.com',
		]);
		$this->assertFalse($user->isKirby());
	}

	public function testIsLoggedIn()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com']
			],
		]);

		$a = $app->user('a@getkirby.com');
		$b = $app->user('b@getkirby.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$app->impersonate('a@getkirby.com');

		$this->assertTrue($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$app->impersonate('b@getkirby.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertTrue($b->isLoggedIn());
	}

	public function testIsNobody()
	{
		$user = new User([
			'id'   => 'nobody',
			'role' => 'nobody'
		]);
		$this->assertTrue($user->isNobody());

		$user = new User([
			'role' => 'nobody'
		]);
		$this->assertFalse($user->isNobody());

		$user = new User([
			'id' => 'nobody',
		]);
		$this->assertTrue($user->isNobody());
	}

	public function testLoginPasswordlessKirby()
	{
		$user = new User(['id' => 'kirby']);
		$this->expectException(PermissionException::class);
		$user->loginPasswordless();
	}


	public function testName()
	{
		$user = new User([
			'name' => $name = 'Homer Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->name());
		$this->assertSame($name, $user->name()->value());
	}

	public function testNameSanitized()
	{
		$user = new User([
			'name' => '<strong>Homer</strong> Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->name());
		$this->assertSame('Homer Simpson', $user->name()->value());
	}

	public function testNameOrEmail()
	{
		$user = new User([
			'email' => $email = 'homer@simpsons.com',
			'name'  => $name = 'Homer Simpson',
		]);

		$this->assertInstanceOf(Field::class, $user->nameOrEmail());
		$this->assertSame($name, $user->nameOrEmail()->value());
		$this->assertSame('name', $user->nameOrEmail()->key());

		$user = new User([
			'email' => $email = 'homer@simpsons.com',
			'name'  => ''
		]);

		$this->assertInstanceOf(Field::class, $user->nameOrEmail());
		$this->assertSame($email, $user->nameOrEmail()->value());
		$this->assertSame('email', $user->nameOrEmail()->key());
	}

	public function testToString()
	{
		$user = new User([
			'email' => 'test@getkirby.com'
		]);

		$this->assertSame('test@getkirby.com', $user->toString());
	}

	public function testToStringWithTemplate()
	{
		$user = new User([
			'email' => 'test@getkirby.com'
		]);

		$this->assertSame('Email: test@getkirby.com', $user->toString('Email: {{ user.email }}'));
	}

	public function testModified()
	{
		$app = new App([
			'roots' => [
				'index'    => static::TMP,
				'accounts' => static::TMP
			]
		]);

		// create a user file
		F::write($file = static::TMP . '/test/index.php', '<?php return [];');

		$modified = filemtime($file);
		$user     = $app->user('test');

		$this->assertSame((string)$modified, $user->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $user->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $user->modified($format, 'strftime'));
	}

	public function testModifiedSpecifyingLanguage()
	{
		$app = new App([
			'roots' => [
				'index'    => static::TMP,
				'accounts' => static::TMP
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		// create a user file
		F::write($file = static::TMP . '/test/index.php', '<?php return [];');

		// create the english page
		F::write($file = static::TMP . '/test/user.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german page
		F::write($file = static::TMP . '/test/user.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$user = $app->user('test');

		$this->assertSame((string)$modifiedEnContent, $user->modified('U', null, 'en'));
		$this->assertSame((string)$modifiedDeContent, $user->modified('U', null, 'de'));
	}

	public function testPasswordTimestamp()
	{
		$app = new App([
			'roots' => [
				'index'    => static::TMP,
				'accounts' => static::TMP
			]
		]);

		// create a user file
		F::write(static::TMP . '/test/index.php', '<?php return [];');

		$user = $app->user('test');
		$this->assertNull($user->passwordTimestamp());

		// create a password file
		F::write(static::TMP . '/test/.htpasswd', 'a very secure hash');
		touch(static::TMP . '/test/.htpasswd', 1337000000);

		$this->assertSame(1337000000, $user->passwordTimestamp());

		// timestamp is not cached
		touch(static::TMP . '/test/.htpasswd', 1338000000);
		$this->assertSame(1338000000, $user->passwordTimestamp());
	}

	public static function passwordProvider(): array
	{
		return [
			[null, false],
			['', false],
			['short', false],
			[str_repeat('long', 300), false],
			['invalid-password', false],
			['correct-horse-battery-staple', true],
		];
	}

	public function testRoles(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			],
		]);

		// unauthenticated, should only have the current role
		$user  = $app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor'], $roles);

		// user on another normal user should not have admin as option
		$app->impersonate('editor@getkirby.com');
		$user  = $app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// user on themselves should not have admin as option
		$app->impersonate('editor@getkirby.com');
		$user  = $app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// current user is admin, user can also have admin option
		$app->impersonate('admin@getkirby.com');
		$user  = $app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['admin', 'editor', 'guest'], $roles);

		// last admin has only admin role as option
		$user  = $app->user('admin@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['admin'], $roles);
	}

	public function testRolesWithPermissions(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => [
							'changeRole' => true
						],
						'users' => [
							'changeRole' => false
						]
					]
				],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		// user has permission to change their own role
		$user  = $app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// user has no permission to change someone else's role
		$user  = $app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['guest'], $roles);
	}

	public function testRolesWithOptions(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'manager'],
				['name' => 'editor'],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			],
			'blueprints' => [
				'users/manager' => [
					'options' => [
						'create' => [
							'editor' => false
						]
					]
				],
				'users/editor' => [
					'permissions' => [
						'user' => [
							'changeRole' => true
						],
						'users' => [
							'changeRole' => true
						]
					]
				],
				'users/guest' => [
					'options' => [
						'changeRole' => [
							'editor' => false
						]
					]
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		// blueprint option disallows changing role for guest role
		$user  = $app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['guest'], $roles);

		// blueprint `create` option limits available roles
		$user  = $app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);
	}

	public function testSecret()
	{
		$app = new App([
			'roots' => [
				'index'    => static::TMP,
				'accounts' => static::TMP
			]
		]);

		F::write(static::TMP . '/test/index.php', '<?php return [];');
		$user = $app->user('test');

		// no secrets file
		$this->assertNull($user->secret('password'));
		$this->assertNull($user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// just a password hash
		F::write(static::TMP . '/test/.htpasswd', 'a very secure hash');
		$this->assertSame('a very secure hash', $user->secret('password'));
		$this->assertNull($user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// extra secrets
		F::write(static::TMP . '/test/.htpasswd', 'a very secure hash' . "\n" . '{"totp":"foo"}');
		$this->assertSame('a very secure hash', $user->secret('password'));
		$this->assertSame('foo', $user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// just extra secrets
		F::write(static::TMP . '/test/.htpasswd', "\n" . '{"totp":"foo"}');
		$this->assertNull($user->secret('password'));
		$this->assertSame('foo', $user->secret('totp'));
		$this->assertNull($user->secret('invalid'));

		// invalid JSON
		F::write(static::TMP . '/test/.htpasswd', "\n" . 'this is not JSON');
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('JSON string is invalid');
		$user->secret('totp');
	}

	#[DataProvider('passwordProvider')]
	public function testValidatePassword(string|null $input, bool $valid)
	{
		$user = new User([
			'email'    => 'test@getkirby.com',
			'password' => User::hashPassword('correct-horse-battery-staple')
		]);

		if ($valid === false) {
			$this->expectException(InvalidArgumentException::class);
			$user->validatePassword($input);
		} else {
			$this->assertTrue($user->validatePassword($input));
		}
	}

	public function testValidatePasswordHttpCode()
	{
		$user = new User([
			'email'    => 'test@getkirby.com',
			'password' => User::hashPassword('correct-horse-battery-staple')
		]);

		$caught = 0;

		try {
			$user->validatePassword('short');
		} catch (\Kirby\Exception\InvalidArgumentException $e) {
			$this->assertSame(
				'Please enter a valid password. Passwords must be at least 8 characters long.',
				$e->getMessage()
			);
			$this->assertSame(400, $e->getHttpCode());
			$caught++;
		}

		try {
			$user->validatePassword(str_repeat('long', 300));
		} catch (\Kirby\Exception\InvalidArgumentException $e) {
			$this->assertSame(
				'Please enter a valid password. Passwords must not be longer than 1000 characters.',
				$e->getMessage()
			);
			$this->assertSame(400, $e->getHttpCode());
			$caught++;
		}

		try {
			$user->validatePassword('longbutinvalid');
		} catch (\Kirby\Exception\InvalidArgumentException $e) {
			$this->assertSame('Wrong password', $e->getMessage());
			$this->assertSame(401, $e->getHttpCode());
			$caught++;
		}

		$this->assertSame(3, $caught);
	}

	public function testValidateUndefinedPassword()
	{
		$user = new User([
			'email' => 'test@getkirby.com',
		]);

		$this->expectException(NotFoundException::class);
		$user->validatePassword('test');
	}


	public function testQuery()
	{
		$user = new User([
			'email' => 'test@getkirby.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('Test User', $user->query('user.name')->value());
		$this->assertSame('test@getkirby.com', $user->query('user.email'));

		// also test with `model` key
		$this->assertSame('Test User', $user->query('model.name')->value());
		$this->assertSame('test@getkirby.com', $user->query('model.email'));
	}

	public function testUserMethods()
	{
		User::$methods = [
			'test' => fn () => 'homer'
		];

		$user = new User([
			'email' => 'test@getkirby.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('homer', $user->test());

		User::$methods = [];
	}

	public function testUserModel()
	{
		User::$models = [
			'dummy' => UserTestModel::class
		];

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(UserTestModel::class, $user);

		User::$models = [];
	}
}
