<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPicker::class)]
class UserPickerTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserPicker';

	protected function setUp(): void
	{
		parent::setUp();

		// use a uuid-based role to avoid static permission cache
		// collisions with roles used in other tests
		$uuid = uuid();

		$this->app = $this->app->clone([
			'users' => [
				['email' => 'a@getkirby.com', 'role' => 'editor-' . $uuid],
				['email' => 'b@getkirby.com', 'role' => 'editor-' . $uuid],
				['email' => 'c@getkirby.com', 'role' => 'editor-' . $uuid]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults(): void
	{
		$picker = new UserPicker();

		$this->assertCount(3, $picker->items());
	}

	public function testNonListableUsersAreFiltered(): void
	{
		// use a uuid-based role to avoid static permission cache
		// collisions with roles used in other tests
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'email' => 'a@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'b@getkirby.com',
					'role'  => 'restricted-' . $uuid
				],
				[
					'email' => 'c@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		// the kirby superuser bypasses all blueprint restrictions
		$app->impersonate('kirby');

		$picker = new UserPicker();

		$this->assertCount(3, $picker->items());

		// the editor can only access their own account
		$app->impersonate('a@getkirby.com');

		$picker = new UserPicker();

		// only the listable user is returned
		$this->assertCount(1, $picker->items());
		$this->assertSame('a@getkirby.com', $picker->items()->first()->email());

	}

	public function testQuery(): void
	{
		$picker = new UserPicker([
			'query' => 'kirby.users.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}
}
