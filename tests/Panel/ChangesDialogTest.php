<?php

namespace Kirby\Panel;

use Kirby\Cms\Pages;
use Kirby\Content\Changes;
use Kirby\Content\VersionId;
use Kirby\Panel\Areas\AreaTestCase;
use Kirby\Uuid\Uuids;

/**
 * @coversDefaultClass \Kirby\Panel\ChangesDialog
 * @covers ::__construct
 */
class ChangesDialogTest extends AreaTestCase
{
	protected Changes $changes;

	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();

		$this->changes = new Changes();
	}

	public function setUpModels(): void
	{
		$this->app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
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

	/**
	 * @covers ::files
	 */
	public function testFiles(): void
	{
		$this->setUpModels();

		$this->app->file('file://test')->version(VersionId::latest())->save([
			'alt' => 'Test'
		]);

		$this->app->file('file://test')->version(VersionId::changes())->save([
			'alt' => 'Test'
		]);

		$dialog = new ChangesDialog();
		$files  = $dialog->files();

		$this->assertCount(1, $files);
		$this->assertSame('test.jpg', $files[0]['text']);
		$this->assertSame('/pages/test/files/test.jpg', $files[0]['link']);
	}

	/**
	 * @covers ::files
	 */
	public function testFilesWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->files());
	}

	/**
	 * @covers ::items
	 */
	public function testItems(): void
	{
		$this->setUpModels();
		$page = $this->app->page('page://test');
		$page->version(VersionId::latest())->save([]);
		$page->version(VersionId::changes())->save([]);

		$dialog = new ChangesDialog();
		$pages  = new Pages([$page]);
		$items  = $dialog->items($pages);

		$this->assertCount(1, $items);

		$this->assertSame('test', $items[0]['text']);
		$this->assertSame('/pages/test', $items[0]['link']);
	}

	/**
	 * @covers ::load
	 */
	public function testLoad(): void
	{
		$dialog = new ChangesDialog();

		$expected = [
			'component' => 'k-changes-dialog',
			'props' => [
				'files' => [],
				'pages' => [],
				'users' => [],
			]
		];

		$this->assertSame($expected, $dialog->load());
	}

	/**
	 * @covers ::pages
	 */
	public function testPages(): void
	{
		$this->setUpModels();

		$this->app->page('page://test')->version(VersionId::latest())->save([]);
		$this->app->page('page://test')->version(VersionId::changes())->save([]);

		$dialog = new ChangesDialog();
		$pages  = $dialog->pages();

		$this->assertCount(1, $pages);
		$this->assertSame('test', $pages[0]['text']);
		$this->assertSame('/pages/test', $pages[0]['link']);
	}

	/**
	 * @covers ::pages
	 */
	public function testPagesWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->pages());
	}

	/**
	 * @covers ::users
	 */
	public function testUsers(): void
	{
		$this->setUpModels();

		$this->app->user('user://test')->version(VersionId::latest())->save([]);
		$this->app->user('user://test')->version(VersionId::changes())->save([]);

		$dialog = new ChangesDialog();
		$users  = $dialog->users();

		$this->assertCount(1, $users);
		$this->assertSame('test@getkirby.com', $users[0]['text']);
		$this->assertSame('/users/test', $users[0]['link']);
	}

	/**
	 * @covers ::users
	 */
	public function testUsersWithoutChanges(): void
	{
		$dialog = new ChangesDialog();
		$this->assertSame([], $dialog->users());
	}
}
