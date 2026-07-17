<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserAbilities::class)]
class UserAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserAbilities';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);
	}

	protected function abilities(string $email): UserAbilities
	{
		return new UserAbilities($this->app->user($email));
	}

	public function testChangeRoleForAdminAsAdmin(): void
	{
		// add a second admin to make sure the target is not the last admin
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'another-admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('another-admin@getkirby.com');

		$this->assertTrue($this->abilities('admin@getkirby.com')->changeRole());
	}

	public function testChangeRoleForAdminAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertFalse($this->abilities('admin@getkirby.com')->changeRole());
	}

	public function testChangeRoleForEditor(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertTrue($this->abilities('editor@getkirby.com')->changeRole());
	}

	public function testChangeRoleForLastAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertFalse($this->abilities('admin@getkirby.com')->changeRole());
	}

	public function testChangeSecretForSelf(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertTrue($this->abilities('editor@getkirby.com')->changeSecret());
	}

	public function testChangeSecretForOtherUserAsAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertTrue($this->abilities('editor@getkirby.com')->changeSecret());
	}

	public function testChangeSecretForOtherUserAsEditor(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor'],
				['email' => 'another-editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->assertFalse($this->abilities('another-editor@getkirby.com')->changeSecret());
	}

	public function testCreateAdminAsAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertTrue($this->abilities('admin@getkirby.com')->create());
	}

	public function testCreateAdminAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertFalse($this->abilities('admin@getkirby.com')->create());
	}

	public function testCreateEditorAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertTrue($this->abilities('editor@getkirby.com')->create());
	}

	public function testDeleteAdmin(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'another-admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('admin@getkirby.com');

		$this->assertTrue($this->abilities('admin@getkirby.com')->delete());
	}

	public function testDeleteEditor(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertTrue($this->abilities('editor@getkirby.com')->delete());
	}

	public function testDeleteLastAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertFalse($this->abilities('admin@getkirby.com')->delete());
	}

	public function testDeleteLastUser(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->assertFalse($this->abilities('editor@getkirby.com')->delete());
	}

	public function testInheritedAbilities(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$abilities = $this->abilities('editor@getkirby.com');

		$this->assertTrue($abilities->changeEmail());
		$this->assertTrue($abilities->update());
	}
}
