<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Pages;
use Kirby\Content\Changes;
use Kirby\Panel\Ui\TestCase;
use Kirby\Uuid\Uuids;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChangesDialog::class)]
class ChangesDialogTest extends TestCase
{
	protected Changes $changes;

	public function setUp(): void
	{
		parent::setUp();

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
			]
		]);

		Uuids::populate();

		$this->changes = new Changes();
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

		$dialog = new ChangesDialog();
		$files  = $dialog->files();

		$this->assertCount(1, $files);
		$this->assertSame('test.jpg', $files[0]['text']);
		$this->assertSame('/pages/test/files/test.jpg', $files[0]['link']);
	}

	public function testFilesWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->files());
	}

	public function testItems(): void
	{
		$page = $this->app->page('page://test');
		$page->version('latest')->save([]);
		$page->version('changes')->save([]);

		$dialog = new ChangesDialog();
		$pages  = new Pages([$page]);
		$items  = $dialog->items($pages);

		$this->assertCount(1, $items);

		$this->assertSame('test', $items[0]['text']);
		$this->assertSame('/pages/test', $items[0]['link']);
	}

	public function testPages(): void
	{
		$this->setUpModels();

		$this->app->page('page://test')->version('latest')->save([]);
		$this->app->page('page://test')->version('changes')->save([]);

		$dialog = new ChangesDialog();
		$pages  = $dialog->pages();

		$this->assertCount(1, $pages);
		$this->assertSame('test', $pages[0]['text']);
		$this->assertSame('/pages/test', $pages[0]['link']);
	}

	public function testPagesWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->pages());
	}

	public function testProps(): void
	{
		$dialog = new ChangesDialog();
		$props  = $dialog->props();
		$this->assertArrayHasKey('files', $props);
		$this->assertArrayHasKey('pages', $props);
		$this->assertArrayHasKey('users', $props);
	}

	public function testRender(): void
	{
		$dialog = new ChangesDialog();
		$result = $dialog->render();
		$this->assertSame('k-changes-dialog', $result['component']);
		$this->assertSame([
			'files' => [],
			'pages' => [],
			'users' => [],
		], $result['props']);
	}

	public function testUsers(): void
	{
		$this->setUpModels();

		$this->app->user('user://test')->version('latest')->save([]);
		$this->app->user('user://test')->version('changes')->save([]);

		$dialog = new ChangesDialog();
		$users  = $dialog->users();

		$this->assertCount(1, $users);
		$this->assertSame('test@getkirby.com', $users[0]['text']);
		$this->assertSame('/account', $users[0]['link']);
	}

	public function testUsersWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->users());
	}
}
