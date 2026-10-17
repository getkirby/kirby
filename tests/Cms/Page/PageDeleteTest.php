<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageDeleteTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageDelete';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app->impersonate('kirby');
	}

	public function site(): Site
	{
		return $this->app->site();
	}

	public function testDeleteDraft(): void
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$this->assertTrue($page->exists());
		$this->assertTrue($page->parentModel()->drafts()->has($page));

		$page->delete();

		$this->assertFalse($page->exists());
		$this->assertFalse($page->parentModel()->drafts()->has($page));
	}

	public function testDeleteHooks(): void
	{
		$calls = 0;
		$phpunit  = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.delete:before' => function ($page, $force) use ($phpunit, &$calls) {
					$phpunit->assertIsPage($page);
					$phpunit->assertFalse($force);
					$phpunit->assertFileExists($page->root());
					$calls++;
				},
				'page.delete:after' => function ($status, $page) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertIsPage($page);
					$phpunit->assertFileDoesNotExist($page->root());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$page->delete();

		$this->assertSame(2, $calls);
	}

	public function testDeletePage(): void
	{
		$parent = Page::create(['slug' => 'test']);
		$listed = Page::create([
			'slug'   => 'child-a',
			'num'    => 1,
			'parent' => $parent
		]);

		$unlisted = Page::create([
			'slug'   => 'child-b',
			'draft'  => false,
			'parent' => $parent
		]);

		$this->assertTrue($listed->exists());
		$this->assertTrue($parent->children()->has($listed));
		$this->assertTrue($unlisted->exists());
		$this->assertTrue($parent->children()->has($unlisted));

		$listed->delete();

		$this->assertFalse($listed->exists());
		$this->assertFalse($parent->children()->has($listed));
		$this->assertTrue($unlisted->exists());
		$this->assertTrue($parent->children()->has($unlisted));
	}

	public function testDeleteMultipleSortedPages(): void
	{
		$range = range(1, 10);
		$site  = $this->site();

		foreach ($range as $num) {
			$page = Page::create([
				'slug' => Str::random(),
				'num'  => $num
			]);

			$this->assertTrue($page->exists());
			$this->assertTrue($site->children()->has($page));
		}

		foreach ($site->children() as $page) {
			$page->delete();

			$this->assertFalse($page->exists());
			$this->assertFalse($site->children()->has($page));
		}

		$this->assertCount(0, $site->children());
	}

	public function testDeletePageWithChildrenAndDrafts(): void
	{
		$page = Page::create([
			'slug' => 'test',
			'num'  => 1
		]);

		$page->createChild([
			'slug'  => 'child-a',
			'draft' => true
		]);

		$page->createChild([
			'slug'  => 'child-b',
			'draft' => false
		]);

		$page->delete(force: true);

		$this->assertFalse($page->exists());
	}

	public function testDeletePageWithSortedChildren(): void
	{
		$page = Page::create([
			'slug'  => 'test',
			'draft' => false
		]);

		$num = 1;

		foreach (['a', 'b', 'c', 'd'] as $slug) {
			$child = $page->createChild([
				'slug'  => 'child-' . $slug,
				'draft' => false
			]);

			$child->changeNum($num);
			$num++;
		}

		$page->delete(force: true);

		$this->assertFalse($page->exists());
		$this->assertDirectoryDoesNotExist($page->root());
	}

	public function testDeletePageWithUndeletableChild(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/undeletable' => ['options' => ['delete' => false]]
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('kirby');

		F::write($source = static::TMP . '/source.md', '');

		$page = Page::create([
			'slug'    => 'test',
			'content' => ['title' => 'Test']
		]);

		$file = $page->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$a = $page->createChild([
			'slug'  => 'a',
			'num'   => 1,
			'draft' => false
		]);

		$b = $page->createChild([
			'slug'     => 'b',
			'template' => 'undeletable',
			'draft'    => false
		]);

		$this->app->impersonate('admin@getkirby.com');

		try {
			$page->delete(force: true);
			$this->fail('The page should not have been deleted');
		} catch (PermissionException $e) {
			$this->assertSame('You are not allowed to delete "b"', $e->getMessage());
		}

		// nothing has been touched
		$this->assertStringContainsString('Title: Test', F::read($page->root() . '/default.txt'));
		$this->assertFileExists($file->root());
		$this->assertDirectoryExists($a->root());
		$this->assertDirectoryExists($b->root());
	}

	public function testDeletePageWithFailingFileDelete(): void
	{
		$this->app = $this->app->clone([
			'hooks' => [
				'file.delete:before' => function () {
					throw new Exception('The file could not be deleted');
				}
			]
		]);

		$this->app->impersonate('kirby');

		F::write($source = static::TMP . '/source.md', '');

		$page = Page::create([
			'slug'    => 'test',
			'content' => ['title' => 'Test']
		]);

		$file = $page->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		try {
			$page->delete();
			$this->fail('The page should not have been deleted');
		} catch (Exception $e) {
			$this->assertSame('The file could not be deleted', $e->getMessage());
		}

		// the page content survives a failure during the deletion
		$this->assertStringContainsString('Title: Test', F::read($page->root() . '/default.txt'));
		$this->assertFileExists($file->root());
	}

	public function testDeleteHookWithUUIDAccess(): void
	{
		$phpunit = $this;
		$uuid    = null;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.delete:after' => function ($status, Page $page) use ($phpunit, &$uuid) {
					$phpunit->assertSame($uuid, $page->uuid()->id());
				}
			]
		]);

		$this->app->impersonate('kirby');

		$page        = Page::create(['slug' => 'test']);
		$uuid        = $page->uuid()->id();
		$contentFile = $page->root() . '/default.txt';

		$this->assertFileExists($contentFile);

		$page->delete();

		$this->assertFileDoesNotExist($contentFile);
	}
}
