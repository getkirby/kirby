<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

class UsersTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Users';

	public function testAddUser(): void
	{
		$users = Users::factory([
			['email' => 'a@getkirby.com']
		]);

		$user = new User([
			'email' => 'b@getkirby.com'
		]);

		$result = $users->add($user);

		$this->assertCount(2, $result);
		$this->assertSame('a@getkirby.com', $result->nth(0)->email());
		$this->assertSame('b@getkirby.com', $result->nth(1)->email());
	}

	public function testAddCollection(): void
	{
		$a = Users::factory([
			['email' => 'a@getkirby.com']
		]);

		$b = Users::factory([
			['email' => 'b@getkirby.com'],
			['email' => 'c@getkirby.com']
		]);

		$c = $a->add($b);

		$this->assertCount(3, $c);
		$this->assertSame('a@getkirby.com', $c->nth(0)->email());
		$this->assertSame('b@getkirby.com', $c->nth(1)->email());
		$this->assertSame('c@getkirby.com', $c->nth(2)->email());
	}

	public function testAddById(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com'],
				['email' => 'c@getkirby.com'],
			]
		]);


		$users = $app->users()->limit(2);

		$this->assertCount(2, $users);

		$users = $users->add('c@getkirby.com');

		$this->assertCount(3, $users);
		$this->assertSame('a@getkirby.com', $users->nth(0)->email());
		$this->assertSame('b@getkirby.com', $users->nth(1)->email());
		$this->assertSame('c@getkirby.com', $users->nth(2)->email());
	}

	public function testAddNull(): void
	{
		$users = new Users();
		$this->assertCount(0, $users);

		$users->add(null);

		$this->assertCount(0, $users);
	}

	public function testAddFalse(): void
	{
		$users = new Users();
		$this->assertCount(0, $users);

		$users->add(false);

		$this->assertCount(0, $users);
	}

	public function testAddInvalidObject(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You must pass a Users or User object or an ID of an existing user to the Users collection');

		$site  = new Site();
		$users = new Users();
		$users->add($site);
	}

	public function testFiles(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'a@getkirby.com',
					'files' => [['filename' => 'a.jpg']]
				],
				[
					'email' => 'b@getkirby.com',
					'files' => [['filename' => 'b.jpg']]
				],
				[
					'id'	=> 'user-c',
					'email' => 'c@getkirby.com',
					'files' => [['filename' => 'c.jpg']]
				],
			]
		]);

		$files = $app->users()->files();
		$this->assertCount(3, $files);
		$this->assertSame('a.jpg', $files->first()->filename());
		$this->assertSame('c.jpg', $files->find('user-c/c.jpg')->filename());
	}

	public function testFind(): void
	{
		$users = new Users([
			new User(['email' => 'a@getkirby.com']),
			new User(['email' => 'B@getKirby.com']),
		]);

		$first = $users->first();
		$last  = $users->last();

		$this->assertSame($first, $users->find($first->id()));
		$this->assertSame($last, $users->find($last->id()));
		$this->assertSame($first, $users->find($first->email()));
		$this->assertSame($last, $users->find($last->email()));
		$this->assertNull($users->find('c@getkirby.com'));
		$this->assertNull($users->find(false));
	}

	public function testFindByEmail(): void
	{
		$users = new Users([
			new User(['email' => 'a@getkirby.com']),
			new User(['email' => 'B@getKirby.com']),
		]);

		$this->assertSame('a@getkirby.com', $users->find('a@getkirby.com')->email());
		$this->assertSame('a@getkirby.com', $users->find('A@getkirby.com')->email());
		$this->assertSame('b@getkirby.com', $users->find('B@getkirby.com')->email());
		$this->assertSame('b@getkirby.com', $users->find('b@getkirby.com')->email());
		$this->assertNull($users->find('c@getkirby.com'));
		$this->assertNull($users->find(false));
	}

	public function testFindByUuid(): void
	{
		$app = new App([
			'users' => [
				['id' => 'homer', 'email' => $a = 'a@getkirby.com'],
				['id' => 'foo', 'email' => $b = 'b@getkirby.com']
			]
		]);

		$users = $app->users();
		$this->assertSame($a, $users->find('user://homer')->email());
		$this->assertSame($b, $users->find('user://foo')->email());
		$this->assertNull($users->find('user://bar'));
		$this->assertNull($users->find(false));

		$this->assertSame($a, $app->user('user://homer')->email());
		$this->assertSame($b, $app->user('user://foo')->email());
		$this->assertNull($app->user('user://bar'));
	}

	public function testFindInFilesystem(): void
	{
		$app = new App([
			'roots' => [
				'accounts' => static::TMP . '/accounts',
				'index'    => '/dev/null'
			]
		]);

		$app->impersonate('kirby');

		$app->users()->create(['id' => 'homer', 'email' => 'a@getkirby.com', 'password' => '12345678']);
		$app->users()->create(['id' => 'foo', 'email' => 'B@getKirby.com']);

		// initialize a new fresh app instance to start with an empty collection
		$app   = $app->clone();
		$users = $app->users();

		// test invalid key first in an uninitialized collection
		$this->assertNull($users->find(false));

		$this->assertSame('a@getkirby.com', $app->user('a@getkirby.com')->email());
		$this->assertSame('a@getkirby.com', $app->user('homer')->email());
		$this->assertSame('a@getkirby.com', $app->user('user://homer')->email());
		$this->assertSame('a@getkirby.com', $users->find('a@getkirby.com')->email());
		$this->assertSame('a@getkirby.com', $users->find('homer')->email());
		$this->assertSame('a@getkirby.com', $users->find('user://homer')->email());

		$this->assertSame('b@getkirby.com', $app->user('b@getkirby.com')->email());
		$this->assertSame('b@getkirby.com', $app->user('foo')->email());
		$this->assertSame('b@getkirby.com', $app->user('user://foo')->email());
		$this->assertSame('b@getkirby.com', $users->find('b@getkirby.com')->email());
		$this->assertSame('b@getkirby.com', $users->find('foo')->email());
		$this->assertSame('b@getkirby.com', $users->find('user://foo')->email());

		$this->assertNull($app->user('c@getkirby.com'));
		$this->assertNull($app->user('bar'));
		$this->assertNull($app->user('user://bar'));
		$this->assertNull($users->find('c@getkirby.com'));
		$this->assertNull($users->find('bar'));
		$this->assertNull($users->find('user://bar'));
	}

	public function testCustomMethods(): void
	{
		Users::$methods = [
			'test' => function () {
				$i = 0;
				foreach ($this as $user) {
					$i++;
				}
				return $i;
			}
		];

		$users = new Users([
			new User(['email' => 'a@getkirby.com']),
			new User(['email' => 'B@getKirby.com']),
		]);

		$this->assertSame(2, $users->test());

		Users::$methods = [];
	}

	public function testRoles(): void
	{
		$app = new App([
			'users' => [
				['email' => 'a@getkirby.com', 'role' => 'admin'],
				['email' => 'b@getkirby.com', 'role' => 'editor'],
				['email' => 'c@getkirby.com', 'role' => 'editor'],
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$this->assertCount(1, $app->users()->role('admin'));
		$this->assertCount(2, $app->users()->role('editor'));
	}
}
