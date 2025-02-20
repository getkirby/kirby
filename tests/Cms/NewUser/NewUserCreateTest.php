<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserCreateTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserCreateTest';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@getkirby.com'
		]);
	}

	public function testCreateAdmin(): void
	{
		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'admin',
		]);

		$this->assertTrue($user->exists());
		$this->assertSame('new@domain.com', $user->email());
		$this->assertInstanceOf(Role::class, $user->role());
		$this->assertSame('admin', $user->role()->name());
	}

	public function testCreateUserWithUnicodeEmail(): void
	{
		// with Unicode email
		$user = User::create([
			'email' => 'test@exämple.com',
			'role'  => 'admin',
		]);

		$this->assertTrue($user->exists());
		$this->assertSame('test@exämple.com', $user->email());
		$this->assertSame('admin', $user->role()->name());

		// with Punycode email
		$user = User::create([
			'email' => 'test@xn--tst-qla.com',
			'role'  => 'admin',
		]);

		$this->assertTrue($user->exists());
		$this->assertSame('test@täst.com', $user->email());
		$this->assertSame('admin', $user->role()->name());
	}

	public function testCreateEditor(): void
	{
		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'editor',
		]);

		$this->assertTrue($user->exists());
		$this->assertSame('new@domain.com', $user->email());
		$this->assertInstanceOf(Role::class, $user->role());
		$this->assertSame('editor', $user->role()->name());
	}

	public function testCreateWithContent(): void
	{
		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'editor',
			'content' => [
				'a' => 'Custom A'
			],
		]);

		$this->assertSame('Custom A', $user->a()->value());
	}

	public function testCreateWithDefaults(): void
	{
		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'editor',
			'blueprint' => [
				'name' => 'editor',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('A', $user->a()->value());
		$this->assertSame('B', $user->b()->value());
	}

	public function testCreateWithDefaultsAndContent(): void
	{
		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'editor',
			'content' => [
				'a' => 'Custom A'
			],
			'blueprint' => [
				'name' => 'editor',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('Custom A', $user->a()->value());
		$this->assertSame('B', $user->b()->value());
	}

	public function testCreateWithContentMultilang(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				]
			]
		]);

		$user = User::create([
			'email' => 'new@domain.com',
			'role'  => 'editor',
			'content' => [
				'a' => 'a',
				'b' => 'b',
			],
		]);

		$this->assertTrue($user->exists());

		$this->assertSame('a', $user->a()->value());
		$this->assertSame('b', $user->b()->value());
	}

	public function testCreateWithPassword(): void
	{
		$user = User::create([
			'email'    => 'new@domain.com',
			'password' => 'topsecret2018',
		]);

		$this->assertNotSame($user->password(), 'topsecret2018', 'The password should be hashed');
	}

	public function testCreateHooks(): void
	{
		$calls = 0;
		$phpunit = $this;
		$userInput = [
			'email' => 'new@domain.com',
			'role'  => 'admin',
			'model' => 'admin',
		];

		$this->app = $this->app->clone([
			'hooks' => [
				'user.create:before' => function (User $user, $input) use ($phpunit, $userInput, &$calls) {
					$phpunit->assertSame('new@domain.com', $user->email());
					$phpunit->assertSame('admin', $user->role()->name());
					$phpunit->assertSame($userInput, $input);
					$calls++;
				},
				'user.create:after' => function (User $user) use ($phpunit, &$calls) {
					$phpunit->assertSame('new@domain.com', $user->email());
					$phpunit->assertSame('admin', $user->role()->name());
					$calls++;
				}
			]
		]);

		User::create($userInput);

		$this->assertSame(2, $calls);
	}

	public function testCreateWithTranslations()
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				]
			]
		]);

		User::create([
			'email' => 'test@getkirby.com',
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'title' => 'Title EN',
					]
				],
				[
					'code' => 'de',
					'content' => [
						'title' => 'Title DE',
					]
				],
			],
		]);

		$user = $this->app->user('test@getkirby.com');

		$this->assertSame('Title EN', $user->content('en')->title()->value());
		$this->assertSame('Title DE', $user->content('de')->title()->value());
	}

	public function testCreateWithTranslationsAndContent()
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				]
			]
		]);

		User::create([
			'email' => 'test@getkirby.com',
			'content' => [
				'title' => 'Title EN',
			],
			'translations' => [
				[
					'code' => 'de',
					'content' => [
						'title' => 'Title DE',
					]
				],
			],
		]);

		$user = $this->app->user('test@getkirby.com');

		$this->assertSame('Title EN', $user->content('en')->title()->value());
		$this->assertSame('Title DE', $user->content('de')->title()->value());
	}

}
