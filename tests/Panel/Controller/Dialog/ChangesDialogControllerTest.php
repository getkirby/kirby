<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\App;
use Kirby\Cms\Pages;
use Kirby\Cms\User;
use Kirby\Content\Changes;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use Kirby\Uuid\Uuids;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChangesDialogController::class)]
class ChangesDialogControllerTest extends TestCase
{
	protected Changes $changes;

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('12345678')
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->changes = new Changes();
	}

	public function setUpModels(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'uuid' => 'test'
						],
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => [
									'uuid' => 'test'
								],
							]
						]
					]
				]
			],
			'users' => [
				[
					'id' => 'test',
				]
			]
		]);

		$this->app->impersonate('kirby');

		Uuids::populate();
	}

	public function testFiles(): void
	{
		$this->setUpModels();

		$this->app->file('file://test')->version('latest')->save([
			'alt' => 'Test'
		]);

		$this->app->file('file://test')->version('changes')->save([
			'alt' => 'Test'
		]);

		$controller = new ChangesDialogController();
		$files      = $controller->files();

		$this->assertCount(1, $files);
		$this->assertSame('test.jpg', $files[0]['text']);
		$this->assertSame('/pages/test/files/test.jpg', $files[0]['link']);
	}

	public function testFilesWithNonListableFile(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'files/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'test',
						'content' => ['uuid' => 'page-test'],
						'files'   => [
							[
								'filename' => 'public.jpg',
								'content'  => ['uuid' => 'file-public']
							],
							[
								'filename' => 'secret.jpg',
								'template' => 'secret-' . $uuid,
								'content'  => ['uuid' => 'file-secret']
							]
						]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			]
		]);

		$this->app->impersonate('kirby');
		Uuids::populate();

		$this->app->file('file://file-public')->version('latest')->save(['alt' => 'Public']);
		$this->app->file('file://file-public')->version('changes')->save(['alt' => 'Public']);
		$this->app->file('file://file-secret')->version('latest')->save(['alt' => 'Secret']);
		$this->app->file('file://file-secret')->version('changes')->save(['alt' => 'Secret']);

		// kirby sees all files
		$files = (new ChangesDialogController())->files();
		$this->assertCount(2, $files);

		// editor cannot list the secret file
		$this->app->impersonate('editor@getkirby.com');
		$files = (new ChangesDialogController())->files();
		$this->assertCount(1, $files);
		$this->assertSame('public.jpg', $files[0]['text']);
	}

	public function testFilesWithoutChanges(): void
	{
		$controller = new ChangesDialogController();
		$this->assertSame([], $controller->files());
	}

	public function testItem(): void
	{
		$this->setUpModels();
		$page = $this->app->page('page://test');
		$page->version('latest')->save([]);
		$page->version('changes')->save([]);

		$controller = new ChangesDialogController();
		$item       = $controller->item($page);

		$this->assertSame('test', $item['text']);
		$this->assertSame('/pages/test', $item['link']);
	}

	public function testItems(): void
	{
		$this->setUpModels();
		$page = $this->app->page('page://test');
		$page->version('latest')->save([]);
		$page->version('changes')->save([]);

		$controller = new ChangesDialogController();
		$pages      = new Pages([$page]);
		$items      = $controller->items($pages);

		$this->assertCount(1, $items);

		$this->assertSame('test', $items[0]['text']);
		$this->assertSame('/pages/test', $items[0]['link']);
	}

	public function testLoad(): void
	{
		$controller = new ChangesDialogController();
		$dialog     = $controller->load();

		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-changes-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame([], $props['files']);
		$this->assertSame([], $props['pages']);
		$this->assertSame([], $props['users']);
	}

	public function testPages(): void
	{
		$this->setUpModels();

		$this->app->page('page://test')->version('latest')->save([]);
		$this->app->page('page://test')->version('changes')->save([]);

		$controller = new ChangesDialogController();
		$pages      = $controller->pages();

		$this->assertCount(1, $pages);
		$this->assertSame('test', $pages[0]['text']);
		$this->assertSame('/pages/test', $pages[0]['link']);
	}

	public function testPagesWithNonListablePage(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'public',
						'content' => ['uuid' => 'page-public']
					],
					[
						'slug'     => 'secret',
						'template' => 'secret-' . $uuid,
						'content'  => ['uuid' => 'page-secret']
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			]
		]);

		$this->app->impersonate('kirby');
		Uuids::populate();

		$this->app->page('page://page-public')->version('latest')->save([]);
		$this->app->page('page://page-public')->version('changes')->save([]);
		$this->app->page('page://page-secret')->version('latest')->save([]);
		$this->app->page('page://page-secret')->version('changes')->save([]);

		// kirby sees all pages
		$pages = (new ChangesDialogController())->pages();
		$this->assertCount(2, $pages);

		// editor cannot list the secret page
		$this->app->impersonate('editor@getkirby.com');
		$pages = (new ChangesDialogController())->pages();
		$this->assertCount(1, $pages);
		$this->assertSame('public', $pages[0]['text']);
	}

	public function testPagesWithoutChanges(): void
	{
		$controller = new ChangesDialogController();
		$this->assertSame([], $controller->pages());
	}

	public function testUsers(): void
	{
		$this->setUpModels();

		$this->app->user('user://test')->version('latest')->save([]);
		$this->app->user('user://test')->version('changes')->save([]);

		$controller = new ChangesDialogController();
		$users      = $controller->users();

		$this->assertCount(1, $users);
		$this->assertSame('test@getkirby.com', $users[0]['text']);
		$this->assertSame('/users/test', $users[0]['link']);
	}

	public function testUsersWithNonListableUser(): void
	{
		// use uuid-based roles to avoid static permission cache collisions
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
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
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'id'    => 'restricted',
					'email' => 'restricted@getkirby.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		$this->app->impersonate('kirby');
		Uuids::populate();

		$this->app->user('user://editor')->version('latest')->save([]);
		$this->app->user('user://editor')->version('changes')->save([]);
		$this->app->user('user://restricted')->version('latest')->save([]);
		$this->app->user('user://restricted')->version('changes')->save([]);

		// kirby sees all users
		$users = (new ChangesDialogController())->users();
		$this->assertCount(2, $users);

		// editor cannot list the restricted user
		$this->app->impersonate('editor@getkirby.com');
		$users = (new ChangesDialogController())->users();
		$this->assertCount(1, $users);
		$this->assertSame('editor@getkirby.com', $users[0]['text']);
	}

	public function testUsersWithoutChanges(): void
	{
		$controller = new ChangesDialogController();
		$this->assertSame([], $controller->users());
	}
}
