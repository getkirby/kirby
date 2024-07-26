<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass Kirby\Content\PlainTextContentStorageHandler
 * @covers ::__construct
 */
class PlainTextContentStorageHandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.PlainTextContentStorage';

	protected $storage;

	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		parent::setUpMultiLanguage(site: $site);

		$this->storage = new PlainTextContentStorageHandler($this->model);
	}

	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		parent::setUpSingleLanguage(site: $site);

		$this->storage = new PlainTextContentStorageHandler($this->model);
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
	public function testCreatePublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::published(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.en.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreatePublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::published(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.txt'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		$this->setUpSingleLanguage();

		// test idempotency
		$this->storage->delete(VersionId::published(), Language::single());
		$this->assertDirectoryDoesNotExist($this->model->root());
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
	public function testDeletePublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.en.txt');
		touch($this->model->root() . '/article.en.txt');

		$this->storage->delete(VersionId::published(), $this->app->language('en'));
		$this->assertFileDoesNotExist($this->model->root() . '/article.en.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		Dir::make($this->model->root() . '/_changes');
		touch($this->model->root() . '/_changes/article.txt');
		touch($this->model->root() . '/article.txt');

		$this->storage->delete(VersionId::published(), Language::single());
		$this->assertFileDoesNotExist($this->model->root() . '/article.txt');
		$this->assertDirectoryExists($this->model->root());
	}

	public function testExistsForFile()
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug'  => 'test',
					'files' => [
						[
							'filename' => 'test.jpg'
						]
					]
				]
			]
		]);

		$this->model   = $this->app->file('test/test.jpg');
		$this->storage = $this->model->storage();

		// the file does not exist on disk, which also indicates that the version does not exist yet
		$this->assertFalse($this->storage->exists(VersionId::published(), Language::single()));

		// create a dummy file to pretend it exists
		F::write($this->model->root(), '');

		$this->assertTrue($this->storage->exists(VersionId::published(), Language::single()));
	}

	public function testExistsForPage()
	{
		$this->setUpSingleLanguage();

		// remove the page folder after the setUp method to check for non-existing storage
		Dir::remove($this->model->root());

		// the page does not exist on disk, which also indicates that the version does not exist yet
		$this->assertFalse($this->storage->exists(VersionId::published(), Language::single()));

		// create a dummy folder to pretend it exists
		Dir::make($this->model->root());

		$this->assertTrue($this->storage->exists(VersionId::published(), Language::single()));
	}

	public function testExistsForSite()
	{
		$this->setUpSingleLanguage();

		$this->model   = $this->app->site();
		$this->storage = $this->model->storage();

		// remove the content folder after the setUp method to check for non-existing storage
		Dir::remove($this->model->root());

		// the content folder does not exist on disk, which also indicates that the version does not exist yet
		$this->assertFalse($this->storage->exists(VersionId::published(), Language::single()));

		// create a dummy content folder to pretend it exists
		Dir::make($this->model->root());

		$this->assertTrue($this->storage->exists(VersionId::published(), Language::single()));
	}

	public function testExistsForUser()
	{
		$this->setUpSingleLanguage();

		$this->app = $this->app->clone([
			'users' => [
				['id' => 'test']
			]
		]);

		$this->model   = $this->app->user('test');
		$this->storage = $this->model->storage();

		// the account folder does not exist on disk, which also indicates that the version does not exist yet
		$this->assertFalse($this->storage->exists(VersionId::published(), Language::single()));

		// create a dummy account folder to pretend it exists
		Dir::make($this->model->root());

		$this->assertTrue($this->storage->exists(VersionId::published(), Language::single()));
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
		$this->assertNull($this->storage->modified(VersionId::published(), $this->app->language('en')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::published(), Language::single()));
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
		$this->assertNull($this->storage->modified(VersionId::published(), $this->app->language('en')));
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
		$this->assertNull($this->storage->modified(VersionId::published(), Language::single()));
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
	public function testReadDefaultsForFile()
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug'  => 'test',
					'files' => [
						[
							'filename' => 'test.jpg'
						]
					]
				]
			]
		]);

		$this->model   = $this->app->file('test/test.jpg');
		$this->storage = $this->model->storage();

		// create a dummy file to pretend it exists
		F::write($this->model->root(), '');

		$this->assertSame([], $this->storage->read(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::read
	 */
	public function testReadDefaultsForPage()
	{
		$this->setUpSingleLanguage();

		$expected = [
			'title' => 'a-page'
		];

		$this->assertSame($expected, $this->storage->read(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::read
	 */
	public function testReadDefaultsForSite()
	{
		$this->setUpSingleLanguage();

		$this->model   = $this->app->site();
		$this->storage = $this->model->storage();

		// create a dummy content folder to pretend it exists
		Dir::make($this->model->root());

		$this->assertSame([], $this->storage->read(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::read
	 */
	public function testReadDefaultsForUser()
	{
		$this->setUpSingleLanguage();

		$this->app = $this->app->clone([
			'users' => [
				['id' => 'test']
			]
		]);

		$this->model   = $this->app->user('test');
		$this->storage = $this->model->storage();

		// create a dummy account folder to pretend it exists
		Dir::make($this->model->root());

		$this->assertSame([], $this->storage->read(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::read
	 */
	public function testReadPublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::published(), $this->app->language('en')));
	}

	/**
	 * @covers ::read
	 */
	public function testReadPublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::read
	 */
	public function testReadWhenMissing()
	{
		$this->setUpSingleLanguage();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "changes" does not already exist');

		$this->storage->read(VersionId::changes(), Language::single());
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
	public function testTouchPublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$root = $this->model->root() . '/article.en.txt';

		touch($root, 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$this->storage->touch(VersionId::published(), $this->app->language('en'));

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($root));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchPublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$root = $this->model->root() . '/article.txt';

		touch($root, 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$this->storage->touch(VersionId::published(), Language::single());

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
	public function testUpdatePublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.en.txt', $fields);

		$this->storage->update(VersionId::published(), $this->app->language('en'), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.en.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdatePublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->model->root() . '/article.txt', $fields);

		$this->storage->update(VersionId::published(), Language::single(), $fields);
		$this->assertSame($fields, Data::read($this->model->root() . '/article.txt'));
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

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile($id, $this->app->language($language)));
	}

	public static function contentFileProviderMultiLang(): array
	{
		return [
			['file', VersionId::changes(), 'en', 'content/_changes/image.jpg.en.txt'],
			['file', VersionId::published(), 'en', 'content/image.jpg.en.txt'],
			['page', VersionId::changes(), 'en', 'content/a-page/_changes/article.en.txt'],
			['page', VersionId::published(), 'en', 'content/a-page/article.en.txt'],
			['site', VersionId::changes(), 'en', 'content/_changes/site.en.txt'],
			['site', VersionId::published(), 'en', 'content/site.en.txt'],
			['user', VersionId::changes(), 'en', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', VersionId::published(), 'en', 'site/accounts/abcdefgh/user.en.txt'],
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

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile($id, Language::single()));
	}

	public static function contentFileProviderSingleLang(): array
	{
		return [
			['file', VersionId::changes(), 'content/_changes/image.jpg.txt'],
			['file', VersionId::published(), 'content/image.jpg.txt'],
			['page', VersionId::changes(), 'content/a-page/_changes/article.txt'],
			['page', VersionId::published(), 'content/a-page/article.txt'],
			['site', VersionId::changes(), 'content/_changes/site.txt'],
			['site', VersionId::published(), 'content/site.txt'],
			['user', VersionId::changes(), 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', VersionId::published(), 'site/accounts/abcdefgh/user.txt'],
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

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame(static::TMP . '/content/_drafts/a-page/article.txt', $storage->contentFile(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileDraftPublished()
	{
		$this->setUpSingleLanguage();

		$model = new Page([
			'kirby' => $this->app,
			'root' => static::TMP,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);

		$storage = new PlainTextContentStorageHandler($model);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Drafts cannot have a published content file');
		$storage->contentFile(VersionId::published(), Language::single());
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
	public function testContentFilesPublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$this->assertSame([
			$this->model->root() . '/article.en.txt',
			$this->model->root() . '/article.de.txt'
		], $this->storage->contentFiles(VersionId::published()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$this->assertSame([
			$this->model->root() . '/article.txt'
		], $this->storage->contentFiles(VersionId::published()));
	}
}
