<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChangesDialog::class)]
class ChangesDialogTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testChangesFiltersNonListableUser(): void
	{
		// use uuid-based roles and user IDs to avoid static permission cache collisions
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'users/editor-' . $uuid => [
					'name' => 'editor-' . $uuid,
				],
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
					'id'    => 'editor-' . $uuid,
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'id'    => 'restricted-' . $uuid,
					'email' => 'restricted@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		// kirby sees all users
		$app->impersonate('kirby');
		$changes = (new ChangesDialog())->changes(['users/editor-' . $uuid, 'users/restricted-' . $uuid]);
		$this->assertCount(2, $changes);

		// editor cannot list the restricted user
		$app->impersonate('editor@getkirby.com');
		$changes = (new ChangesDialog())->changes(['users/editor-' . $uuid, 'users/restricted-' . $uuid]);
		$this->assertCount(1, $changes);
		$this->assertSame('editor@getkirby.com', $changes[0]['text']);
	}

	public function testChangesFiltersNonAccessibleUser(): void
	{
		// use uuid-based roles and user IDs to avoid static permission cache collisions
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'users/editor-' . $uuid => [
					'name' => 'editor-' . $uuid,
				],
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['access' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'id'    => 'editor-' . $uuid,
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'id'    => 'restricted-' . $uuid,
					'email' => 'restricted@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		// kirby sees all users
		$app->impersonate('kirby');
		$changes = (new ChangesDialog())->changes(['users/editor-' . $uuid, 'users/restricted-' . $uuid]);
		$this->assertCount(2, $changes);

		// editor cannot access the restricted user (Find::user throws, caught by try-catch)
		$app->impersonate('editor@getkirby.com');
		$changes = (new ChangesDialog())->changes(['users/editor-' . $uuid, 'users/restricted-' . $uuid]);
		$this->assertCount(1, $changes);
		$this->assertSame('editor@getkirby.com', $changes[0]['text']);
	}
}
