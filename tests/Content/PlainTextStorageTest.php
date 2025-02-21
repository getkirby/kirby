<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass \Kirby\Content\PlainTextStorage
 * @covers ::__construct
 */
class PlainTextStorageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.PlainTextStorage';

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

	/**
	 * @covers ::create
	 */
	public function testCreateChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/_changes/article.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.en.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.txt'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertContentFileDoesNotExist($language, $versionId);

		// test idempotency
		$this->storage->delete($versionId, $language);

		$this->assertContentFileDoesNotExist($language, $versionId);

		// The page directory should not be deleted
		$this->assertDirectoryExists($this->model->root());
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesMultiLang()
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

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/article.txt');
		touch($this->model->root() . '/_changes/article.txt');

		$this->storage->delete(VersionId::changes(), Language::single());
		$this->assertFileDoesNotExist($this->model->root() . '/_changes/article.txt');
		$this->assertDirectoryDoesNotExist($this->model->root() . '/_changes');
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.en.txt');
		touch($this->model->root() . '/article.en.txt');

		$this->storage->delete(VersionId::latest(), $this->app->language('en'));
		$this->assertFileDoesNotExist($this->model->root() . '/article.en.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.txt');
		touch($this->model->root() . '/article.txt');

		$this->storage->delete(VersionId::latest(), Language::single());
		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('en')));
		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('de')));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSomeExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.en.txt', $modified = 1234567890);

		$this->assertSame($modified, $this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSomeExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		Dir::make(static::TMP . '/content/a-page/_changes');
		touch(static::TMP . '/content/a-page/_changes/article.txt', $modified = 1234567890);

		$this->assertSame($modified, $this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	/**
	 * @coversNothing
	 */
	public function testMove()
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

	/**
	 * @coversNothing
	 */
	public function testMoveNonExistingContentFile()
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

	/**
	 * @covers ::read
	 */
	public function testReadChangesMultiLang()
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

	/**
	 * @covers ::read
	 */
	public function testReadChangesSingleLang()
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

	/**
	 * @covers ::read
	 */
	public function testReadLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::latest(), $this->app->language('en')));
	}

	/**
	 * @covers ::read
	 */
	public function testReadLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::latest(), Language::single()));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesMultiLang()
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

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesSingleLang()
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

	/**
	 * @covers ::touch
	 */
	public function testTouchLatestMultiLang()
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

	/**
	 * @covers ::touch
	 */
	public function testTouchLatestSingleLang()
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

	/**
	 * @covers ::update
	 */
	public function testUpdateChangesMultiLang()
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

	/**
	 * @covers ::update
	 */
	public function testUpdateChangesSingleLang()
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

	/**
	 * @covers ::update
	 */
	public function testUpdateLatestMultiLang()
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

	/**
	 * @covers ::update
	 */
	public function testUpdateLatestSingleLang()
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

	public function testUpdateForFileWithMetaData()
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

	public function testUpdateForFileWithoutMetaData()
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

	public function testUpdateForFileWithRemovedMetaFile()
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

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileProviderMultiLang
	 */
	public function testContentFileMultiLang(string $type, VersionId $id, string $language, string $expected)
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

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileProviderSingleLang
	 */
	public function testContentFileSingleLang(string $type, VersionId $id, string $expected)
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

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileDraft()
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

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$this->assertSame([
			$this->model->root() . '/_changes/article.en.txt',
			$this->model->root() . '/_changes/article.de.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$this->assertSame([
			$this->model->root() . '/_changes/article.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		$this->assertSame([
			$this->model->root() . '/article.en.txt',
			$this->model->root() . '/article.de.txt'
		], $this->storage->contentFiles(VersionId::latest()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		$this->assertSame([
			$this->model->root() . '/article.txt'
		], $this->storage->contentFiles(VersionId::latest()));
	}
}
