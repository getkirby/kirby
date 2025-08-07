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

	public function setUp(): void
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

	public function testFilesWithoutChanges(): void
	{
		$controller = new ChangesDialogController();
		$this->assertSame([], $controller->files());
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

	public function testUsersWithoutChanges(): void
	{
		$controller = new ChangesDialogController();
		$this->assertSame([], $controller->users());
	}
}
