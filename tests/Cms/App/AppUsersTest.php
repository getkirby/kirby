<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Request\Auth\BasicAuth;

class AppUsersTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.AppUsers';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function testImpersonate(): void
	{
		$self = $this;

		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'testtest',
					'email' => 'homer@simpsons.com',
					'role'  => 'admin'
				]
			]
		]);

		// impersonate as kirby
		$user = $app->impersonate('kirby');
		$this->assertIsUser($user, $app->user());
		$this->assertIsUser('kirby', $user);
		$this->assertSame('kirby@getkirby.com', $user->email());
		$this->assertSame('admin', $user->role()->name());
		$this->assertTrue($user->isKirby());
		$this->assertNull($app->user(null, false));

		// impersonate as existing user
		$user = $app->impersonate('homer@simpsons.com');
		$this->assertIsUser($user, $app->user());
		$this->assertSame('homer@simpsons.com', $user->email());
		$user = $app->impersonate('testtest');
		$this->assertIsUser($user, $app->user());
		$this->assertSame('homer@simpsons.com', $user->email());
		$this->assertNull($app->user(null, false));

		// impersonate as nobody
		$user = $app->impersonate('nobody');
		$this->assertIsUser('nobody', $user);
		$this->assertSame('nobody@getkirby.com', $user->email());
		$this->assertSame('nobody', $user->role()->name());
		$this->assertTrue($user->isNobody());
		$this->assertIsUser($user, $app->user());
		$this->assertNull($app->user(null, false));

		// unimpersonate
		$user = $app->impersonate();
		$this->assertNull($user);
		$this->assertNull($app->user());
		$this->assertNull($app->user(null, false));

		// with callback
		$result = $app->impersonate('homer@simpsons.com', function ($user) use ($app, $self) {
			$self->assertIsUser($user, $app->user());
			$self->assertSame('homer@simpsons.com', $user->email());
			$self->assertNull($app->user(null, false));

			return 'test1';
		});
		$this->assertSame('test1', $result);
		$this->assertNull($app->user());
		$this->assertNull($app->user(null, false));

		// with Exception in the callback
		$app->impersonate('kirby');
		$caught = false;
		try {
			$app->impersonate('homer@simpsons.com', function ($user) use ($app, $self) {
				$self->assertIsUser($user, $app->user());
				$self->assertSame('homer@simpsons.com', $user->email());
				$self->assertNull($app->user(null, false));

				throw new Exception('Something bad happened');
			});
		} catch (Exception $e) {
			$caught = true;

			$this->assertSame('Something bad happened', $e->getMessage());

			// the previous user should be restored
			$this->assertSame('kirby@getkirby.com', $app->user()->email());
			$this->assertNull($app->user(null, false));
		}
		$this->assertTrue($caught);
	}

	public function testImpersonateErrorMissingUser(): void
	{
		$this->expectException(NotFoundException::class);
		$this->app->impersonate('homer@simpsons.com');
	}

	public function testRolesSet(): void
	{
		$app = new App([
			'roles' => [
				[
					'name'  => 'editor',
					'title' => 'Editor'
				]
			]
		]);

		$this->assertCount(2, $app->roles());
		$this->assertSame('editor', $app->roles()->last()->name());
	}

	public function testRolesLoad(): void
	{
		$app = new App([
			'roots' => [
				'site' => static::FIXTURES
			]
		]);

		$this->assertCount(2, $app->roles());
		$this->assertSame('editor', $app->roles()->last()->name());
	}

	public function testRoleManual(): void
	{
		$app = new App([
			'roles' => [
				[
					'name'  => 'editor',
					'title' => 'Editor'
				]
			]
		]);

		$this->assertSame('editor', $app->role('editor')->name());
		$this->assertNull($app->role('something'));
	}

	public function testRoleFromUser(): void
	{
		$app = new App([
			'roles' => [
				[
					'name'  => 'editor',
					'title' => 'Editor'
				]
			],
			'users' => [
				[
					'email' => 'user@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->auth()->setUser($app->user('user@getkirby.com'));

		$this->assertSame('editor', $app->role()->name());
		$this->assertSame('editor', $app->role(null, false)->name());
	}

	public function testRoleFromImpersonatedUser(): void
	{
		$app = new App([
			'roles' => [
				[
					'name'  => 'editor',
					'title' => 'Editor'
				]
			],
			'users' => [
				[
					'email' => 'user@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('user@getkirby.com');

		$this->assertSame('editor', $app->role()->name());
		$this->assertNull($app->role(null, false));
	}

	public function testUsersLoad(): void
	{
		$app = $this->app->clone([
			'roots' => [
				'site' => static::FIXTURES
			]
		]);

		$this->assertCount(1, $app->users());
		$this->assertSame('user@getkirby.com', $app->users()->first()->email());
	}

	public function testUsersSet(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'user@getkirby.com'
				]
			]
		]);

		$this->assertCount(1, $app->users());
		$this->assertSame('user@getkirby.com', $app->users()->first()->email());
	}

	public function basicAuthApp()
	{
		return $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth' => true
				]
			],
			'users' => [
				[
					'email'    => 'test@getkirby.com',
					'password' => User::hashPassword('correct-horse-battery-staple')
				]
			],
			'request' => [
				'url' => 'https://getkirby.com/login'
			]
		]);
	}

	public function testUserFromBasicAuth(): void
	{
		$app  = $this->basicAuthApp();
		$auth = new BasicAuth(base64_encode('test@getkirby.com:correct-horse-battery-staple'));
		$user = $app->auth()->currentUserFromBasicAuth($auth);

		$this->assertIsUser($user);
		$this->assertSame('test@getkirby.com', $user->email());
	}

	public function testUserFromBasicAuthDisabled(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is not activated');

		$app = $this->basicAuthApp()->clone([
			'options' => [
				'api' => [
					'basicAuth' => false
				]
			]
		]);

		$auth = new BasicAuth(base64_encode('test@getkirby.com:correct-horse-battery-staple'));
		$user = $app->auth()->currentUserFromBasicAuth($auth);
	}

	public function testUserFromBasicAuthOverHttp(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is only allowed over HTTPS');

		$app = $this->basicAuthApp()->clone([
			'request' => [
				'url' => 'http://getkirby.com/login'
			]
		]);

		$auth = new BasicAuth(base64_encode('test@getkirby.com:correct-horse-battery-staple'));
		$user = $app->auth()->currentUserFromBasicAuth($auth);
	}

	public function testUserFromBasicAuthWithInvalidHeader(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid authorization header');

		$app = $this->basicAuthApp()->clone([
			'request' => [
				'url' => 'http://getkirby.com/login'
			]
		]);

		$user = $app->auth()->currentUserFromBasicAuth();
	}
}
