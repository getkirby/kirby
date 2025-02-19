<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\ContentTranslation;
use Kirby\Content\VersionId;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageDuplicateTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageDuplicateTest';

	public function testDuplicate()
	{
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		// check UUID exists
		$oldUuid = $page->content()->get('uuid')->value();
		$this->assertIsString($oldUuid);

		$drafts = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$copy = $page->duplicate('test-copy');

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));

		// check UUID got updated
		$newUuid = $copy->content()->get('uuid')->value();
		$this->assertIsString($newUuid);
		$this->assertNotSame($oldUuid, $newUuid);
	}

	public function testDuplicateInMultiLanguageMode()
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		new ContentTranslation([
			'parent' => $page,
			'code'   => 'en',
		]);

		$versionId = VersionId::latest();

		$this->assertFileExists($page->version($versionId)->contentFile('en'));

		$drafts = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$copy = $page->duplicate('test-copy');

		$this->assertFileExists($copy->version($versionId)->contentFile('en'));
		$this->assertFileDoesNotExist($copy->version($versionId)->contentFile('de'));

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));
	}

	public function testDuplicateMultiLangSlug()
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		$page = $page->update([
			'slug'  => 'test-de'
		], 'de');

		$versionId = VersionId::latest();

		$this->assertFileExists($page->version($versionId)->contentFile('en'));
		$this->assertFileExists($page->version($versionId)->contentFile('de'));

		$this->assertSame('test', $page->slug());
		$this->assertSame('test-de', $page->slug('de'));

		$copy = $page->duplicate('test-copy');

		$this->assertSame('test-copy', $copy->slug());
		$this->assertSame('test-copy', $copy->slug('de'));
	}

	public function testDuplicateFiles()
	{
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
			'files' => [
				['filename' => 'foo.jpg'],
			]
		]);

		F::write(static::TMP . '/content/_drafts/test/foo.jpg', '');

		$copy = $page->duplicate('test-copy', ['files' => true]);

		$origFile = $page->file('foo.jpg');
		$copyFile = $copy->file('foo.jpg');

		$this->assertNotSame($origFile->uuid()->id(), $copyFile->uuid()->id());
	}

	public function testDuplicateFilesMultiLang()
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
			'files' => [
				['filename' => 'foo.jpg'],
			]
		]);

		F::write(static::TMP . '/content/_drafts/test/foo.jpg', '');

		$page = $this->app->call('de/test');
		$page->duplicate('test-copy', ['files' => true]);
		$copy = $this->app->call('de/test-copy');

		$origFile = $page->file('foo.jpg');
		$copyFile = $copy->file('foo.jpg');

		$this->assertNotSame($origFile->uuid()->id(), $copyFile->uuid()->id());

		// check if the files collection has been properly updated
		$this->assertSame($copy->files()->find('foo.jpg')->uuid()->id(), $copyFile->uuid()->id());
	}

	public function testDuplicateChildren()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$page->createChild(['slug' => 'foo', 'template' => 'default']);

		$page = $this->app->page('test');
		$copy = $page->duplicate('test-copy', ['children' => true]);

		$this->assertNotSame($page->uuid()->id(), $copy->uuid()->id());
		$this->assertNotSame($this->app->page('test/foo')->uuid()->id(), $this->app->page('test-copy/foo')->uuid()->id());
	}

	public function testDuplicateChildrenMultiLang()
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		$page->createChild(['slug' => 'foo', 'template' => 'default']);

		new ContentTranslation([
			'parent' => $page,
			'code'   => 'en'
		]);

		$versionId = VersionId::latest();

		$copy = $page->duplicate('test-copy', ['children' => true]);

		$this->assertFileExists($copy->version($versionId)->contentFile('en'));
		$this->assertFileDoesNotExist($copy->version($versionId)->contentFile('de'));

		$this->assertNotSame($page->uuid()->id(), $copy->uuid()->id());
		$this->assertNotSame($this->app->page('test/foo')->uuid()->id(), $this->app->page('test-copy/foo')->uuid()->id());
	}

	public function testDuplicateChildrenFiles()
	{
		$app = $this->app->clone();
		$app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		$page->createChild([
			'slug' => 'foo',
			'template' => 'default',
			'files' => [
				['filename' => 'foo.jpg'],
			]
		]);

		F::write(static::TMP . '/content/_drafts/test/_drafts/foo/foo.jpg', '');

		$page = $this->app->page('test');
		$copy = $page->duplicate('test-copy', [
			'children' => true,
			'files' => true
		]);

		$this->assertNotSame($page->uuid()->id(), $copy->uuid()->id());

		$origFile = $this->app->page('test/foo')->file('foo.jpg');
		$copyFile = $this->app->page('test-copy/foo')->file('foo.jpg');

		$this->assertNotSame($origFile->uuid()->id(), $copyFile->uuid()->id());
	}
}
