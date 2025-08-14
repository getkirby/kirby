<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PlainTextStorage::class)]
class PlainTextStorageTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Content.PlainTextStorage';

	protected $storage;

	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		parent::setUpMultiLanguage(site: $site);

		$this->storage = new PlainTextStorage($this->model);
	}

	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		parent::setUpSingleLanguage(site: $site);

		$this->storage = new PlainTextStorage($this->model);
	}

	public function testCreateChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.en.txt'));
	}

	public function testCreateChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.txt'));
	}

	public function testCreateLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.en.txt'));
	}

	public function testCreateLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.txt'));
	}

	public function testDeleteNonExisting(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertContentFileDoesNotExist($language, $versionId);

		// test idempotency
		$this->storage->delete($versionId, $language);

		$this->assertContentFileDoesNotExist($language, $versionId);
	}

	public function testDeleteChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/article.en.txt');
		touch($this->model->root() . '/_changes/article.en.txt');

		$this->storage->delete(VersionId::changes(), $this->app->language('en'));
		$this->assertFileDoesNotExist($this->model->root() . '/_changes/article.en.txt');
		$this->assertDirectoryDoesNotExist($this->model->root() . '/_changes');
		$this->assertDirectoryExists($this->model->root());
	}

	public function testDeleteChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/article.txt');
		touch($this->model->root() . '/_changes/article.txt');

		$this->storage->delete(VersionId::changes(), Language::single());
		$this->assertFileDoesNotExist($this->model->root() . '/_changes/article.txt');
		$this->assertDirectoryDoesNotExist($this->model->root() . '/_changes');
	}

	public function testDeleteLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.en.txt');
		touch($this->model->root() . '/article.en.txt');

		$this->storage->delete(VersionId::latest(), $this->app->language('en'));
		$this->assertFileDoesNotExist($this->model->root() . '/article.en.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	public function testDeleteLatestMultiLangAndCleanUp(): void
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root());
		touch($this->model->root() . '/article.en.txt');
		touch($this->model->root() . '/article.de.txt');

		$this->storage->delete(VersionId::latest(), $this->app->language('en'));
		$this->assertFileDoesNotExist($this->model->root() . '/article.en.txt');
		$this->assertDirectoryExists($this->model->root());

		$this->storage->delete(VersionId::latest(), $this->app->language('de'));
		$this->assertDirectoryDoesNotExist($this->model->root());
	}

	public function testDeleteDraftMultiLangAndCleanUp(): void
	{
		$this->setUpMultiLanguage([
			'children' => [
				[
					'slug'     => 'a-page',
					'template' => 'article',
					'draft'    => true
				]
			]
		]);

		Dir::make($this->model->root());
		touch($this->model->root() . '/article.en.txt');

		$this->model->storage()->delete(VersionId::latest(), $this->app->language('en'));

		$this->assertDirectoryDoesNotExist($this->model->root());
		$this->assertDirectoryDoesNotExist(dirname($this->model->root()));
	}

	public function testDeleteLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.txt');
		touch($this->model->root() . '/article.txt');

		$this->storage->delete(VersionId::latest(), Language::single());
		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	public function testDeleteLatestSingleLangAndCleanUp(): void
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root());
		touch($this->model->root() . '/article.txt');

		$this->storage->delete(VersionId::latest(), Language::single());
		$this->assertDirectoryDoesNotExist($this->model->root());
	}

	public function testDeleteDraftSingleLangAndCleanUp(): void
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug'     => 'a-page',
					'template' => 'article',
					'draft'    => true
				]
			]
		]);

		Dir::make($this->model->root());

		touch($this->model->root() . '/article.txt');

		$this->model->storage()->delete(VersionId::latest(), Language::single());

		$this->assertDirectoryDoesNotExist($this->model->root());
		$this->assertDirectoryDoesNotExist(dirname($this->model->root()));
	}

	public function testExistsNoneExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('en')));
		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('de')));
	}

	public function testExistsNoneExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), Language::single()));
	}

	public function testModifiedNoneExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	public function testModifiedNoneExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	public function testModifiedSomeExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.en.txt', $modified = 1234567890);

		$this->assertSame($modified, $this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	public function testModifiedSomeExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		Dir::make(static::TMP . '/content/a-page/_changes');
		touch(static::TMP . '/content/a-page/_changes/article.txt', $modified = 1234567890);

		$this->assertSame($modified, $this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	public function testMove(): void
	{
		$this->setUpSingleLanguage();

		// create some content to move
		$this->createContentSingleLanguage();

		// the source file should exist now
		$this->assertFileExists($this->model->root() . '/article.txt');

		// but the destination file should not be there yet
		$this->assertFileDoesNotExist($this->model->root() . '/_changes/article.txt');

		$this->storage->move(
			VersionId::latest(),
			Language::single(),
			VersionId::changes()
		);

		// the source file should no longer exist
		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');

		// but the destination file should be there
		$this->assertFileExists($this->model->root() . '/_changes/article.txt');
	}

	public function testMoveNonExistingContentFile(): void
	{
		$this->setUpSingleLanguage();

		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');

		$this->storage->move(
			VersionId::latest(),
			Language::single(),
			VersionId::changes()
		);

		// the source file should still not exist
		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');

		// but the destination file should now be there
		$this->assertFileExists($this->model->root() . '/_changes/article.txt');
	}

	public function testMoveToTheSameStorageLocation(): void
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertFileExists($this->model->root() . '/article.txt', 'The source file should exist now');

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language
		);

		$this->assertFileExists($this->model->root() . '/article.txt', 'The source file should still exist');

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language), 'The content should still be the same');
	}

	public function testMoveToTheSameStorageLocationWithAnotherStorageInstance(): void
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();
		$storage   = new PlainTextStorage($this->model);

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertFileExists($this->model->root() . '/article.txt', 'The source file should exist now');

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language,
			$storage
		);

		$this->assertFileExists($this->model->root() . '/article.txt', 'The source file should still exist at the same location');

		// The old storage entry still points to the same file.
		// This is different to the memory handler for example, where
		// entries are always stored with unique cache keys for each
		// handler instance. We can't do the same on the file system.
		// A database handler would also still point to the same row
		// in this case.
		$this->assertTrue($this->storage->exists($versionId, $language), 'The old storage entry still exists, since the location did not change');
		$this->assertSame($content, $this->storage->read($versionId, $language), 'The old entry also still points to the same content');

		$this->assertTrue($storage->exists($versionId, $language));
		$this->assertSame($content, $storage->read($versionId, $language));
	}

	public function testReadChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->model->root() . '/_changes');
		Data::write($this->model->root() . '/_changes/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::changes(), $this->app->language('en')));
	}

	public function testReadChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->model->root() . '/_changes');
		Data::write($this->model->root() . '/_changes/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::changes(), Language::single()));
	}

	public function testReadLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::latest(), $this->app->language('en')));
	}

	public function testReadLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::latest(), Language::single()));
	}

	public function testTouchChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$root = $this->model->root() . '/_changes';

		Dir::make($root);
		touch($root . '/article.en.txt', 123456);
		$this->assertSame(123456, filemtime($root . '/article.en.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::changes(), $this->app->language('en'));

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($root . '/article.en.txt'));
	}

	public function testTouchChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$root = $this->model->root() . '/_changes';

		Dir::make($root);
		touch($root . '/article.txt', 123456);
		$this->assertSame(123456, filemtime($root . '/article.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::changes(), Language::single());

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($root . '/article.txt'));
	}

	public function testTouchLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$root = $this->model->root() . '/article.en.txt';

		touch($root, 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$this->storage->touch(VersionId::latest(), $this->app->language('en'));

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($root));
	}

	public function testTouchLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$root = $this->model->root() . '/article.txt';

		touch($root, 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$this->storage->touch(VersionId::latest(), Language::single());

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($root));
	}

	public function testUpdateChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->model->root() . '/_changes');
		Data::write($this->model->root() . '/_changes/article.en.txt', $fields);

		$this->storage->update(VersionId::changes(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.en.txt'));
	}

	public function testUpdateChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->model->root() . '/_changes');
		Data::write($this->model->root() . '/_changes/article.txt', $fields);

		$this->storage->update(VersionId::changes(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.txt'));
	}

	public function testUpdateLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.en.txt', $fields);

		$this->storage->update(VersionId::latest(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.en.txt'));
	}

	public function testUpdateLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.txt', $fields);

		$this->storage->update(VersionId::latest(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.txt'));
	}

	public function testUpdateForFileWithMetaData(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new PlainTextStorage($file);

		$storage->update(VersionId::latest(), Language::single(), $content = [
			'alt' => 'Test'
		]);

		$this->assertSame($content, Data::read($file->parent()->root() . '/image.jpg.txt'));
	}

	public function testUpdateForFileWithoutMetaData(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new PlainTextStorage($file);
		$storage->update(VersionId::latest(), Language::single(), []);

		$this->assertFileDoesNotExist($file->parent()->root() . '/image.jpg.txt');
	}

	public function testUpdateForFileWithoutMetaDataAndFilteredNullValues(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new PlainTextStorage($file);
		$storage->update(VersionId::latest(), Language::single(), [
			'a' => null,
			'b' => null
		]);

		$this->assertFileDoesNotExist($file->parent()->root() . '/image.jpg.txt');
	}

	public function testUpdateForFileWithRemovedMetaFile(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new PlainTextStorage($file);

		$storage->update(VersionId::latest(), Language::single(), [
			'alt' => 'Test'
		]);

		$this->assertFileExists($file->parent()->root() . '/image.jpg.txt');

		$storage->update(VersionId::latest(), Language::single(), []);

		$this->assertFileDoesNotExist($file->parent()->root() . '/image.jpg.txt');
	}

	#[DataProvider('contentFileProviderMultiLang')]
	public function testContentFileMultiLang(string $type, VersionId $id, string $language, string $expected): void
	{
		$this->setUpMultiLanguage();

		$site = $this->app->site();

		$model = match ($type) {
			'file' => new File([
				'parent'   => $site,
				'filename' => 'image.jpg'
			]),
			'page' => new Page([
				'kirby'    => $this->app,
				'slug'     => 'a-page',
				'template' => 'article'
			]),
			'site' => $site,
			'user' => new User([
				'kirby' => $this->app,
				'id'    => 'abcdefgh'
			])
		};

		$storage = new PlainTextStorage($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile($id, $this->app->language($language)));
	}

	public static function contentFileProviderMultiLang(): array
	{
		return [
			['file', VersionId::changes(), 'en', 'content/_changes/image.jpg.en.txt'],
			['file', VersionId::latest(), 'en', 'content/image.jpg.en.txt'],
			['page', VersionId::changes(), 'en', 'content/a-page/_changes/article.en.txt'],
			['page', VersionId::latest(), 'en', 'content/a-page/article.en.txt'],
			['site', VersionId::changes(), 'en', 'content/_changes/site.en.txt'],
			['site', VersionId::latest(), 'en', 'content/site.en.txt'],
			['user', VersionId::changes(), 'en', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', VersionId::latest(), 'en', 'site/accounts/abcdefgh/user.en.txt'],
		];
	}

	#[DataProvider('contentFileProviderSingleLang')]
	public function testContentFileSingleLang(string $type, VersionId $id, string $expected): void
	{
		$this->setUpSingleLanguage();

		$site = $this->app->site();

		$model = match ($type) {
			'file' => new File([
				'parent'   => $site,
				'filename' => 'image.jpg'
			]),
			'page' => new Page([
				'kirby'    => $this->app,
				'slug'     => 'a-page',
				'template' => 'article'
			]),
			'site' => $site,
			'user' => new User([
				'kirby' => $this->app,
				'id'    => 'abcdefgh'
			])
		};

		$storage = new PlainTextStorage($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile($id, Language::single()));
	}

	public static function contentFileProviderSingleLang(): array
	{
		return [
			['file', VersionId::changes(), 'content/_changes/image.jpg.txt'],
			['file', VersionId::latest(), 'content/image.jpg.txt'],
			['page', VersionId::changes(), 'content/a-page/_changes/article.txt'],
			['page', VersionId::latest(), 'content/a-page/article.txt'],
			['site', VersionId::changes(), 'content/_changes/site.txt'],
			['site', VersionId::latest(), 'content/site.txt'],
			['user', VersionId::changes(), 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', VersionId::latest(), 'site/accounts/abcdefgh/user.txt'],
		];
	}

	public function testContentFileDraft(): void
	{
		$this->setUpSingleLanguage();

		$model = new Page([
			'kirby' => $this->app,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);

		$storage = new PlainTextStorage($model);
		$this->assertSame(static::TMP . '/content/_drafts/a-page/_changes/article.txt', $storage->contentFile(VersionId::changes(), Language::single()));
	}

	public function testContentFilesChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$this->assertSame([
			$this->model->root() . '/_changes/article.en.txt',
			$this->model->root() . '/_changes/article.de.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	public function testContentFilesChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$this->assertSame([
			$this->model->root() . '/_changes/article.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	public function testContentFilesLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$this->assertSame([
			$this->model->root() . '/article.en.txt',
			$this->model->root() . '/article.de.txt'
		], $this->storage->contentFiles(VersionId::latest()));
	}

	public function testContentFilesLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$this->assertSame([
			$this->model->root() . '/article.txt'
		], $this->storage->contentFiles(VersionId::latest()));
	}
}
