<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\PlainTextContentStorageHandler
 * @covers ::__construct
 */
class PlainTextContentStorageHandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.PlainTextContentStorage';

	protected $model;
	protected $storage;

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->model = new Page([
			'kirby'    => new App(),
			'root'     => static::TMP,
			'slug'     => 'a-page',
			'template' => 'article'
		]);

		$this->storage = new PlainTextContentStorageHandler($this->model);
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateChangesMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), 'en', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateChangesSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), 'default', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/_changes/article.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreatePublishedMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::published(), 'en', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/article.en.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreatePublishedSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::published(), 'default', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/article.txt'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		// test idempotency
		$this->storage->delete(VersionId::published(), 'default');
		$this->assertDirectoryDoesNotExist(static::TMP);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesMultiLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.en.txt');
		touch(static::TMP . '/_changes/article.en.txt');

		$this->storage->delete(VersionId::changes(), 'en');
		$this->assertFileDoesNotExist(static::TMP . '/_changes/article.en.txt');
		$this->assertDirectoryDoesNotExist(static::TMP . '/_changes');
		$this->assertDirectoryExists(static::TMP);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesSingleLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.txt');
		touch(static::TMP . '/_changes/article.txt');

		$this->storage->delete(VersionId::changes(), 'default');
		$this->assertFileDoesNotExist(static::TMP . '/_changes/article.txt');
		$this->assertDirectoryDoesNotExist(static::TMP . '/_changes');
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedMultiLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.en.txt');
		touch(static::TMP . '/_changes/article.en.txt');

		$this->storage->delete(VersionId::published(), 'en');
		$this->assertFileDoesNotExist(static::TMP . '/article.en.txt');
		$this->assertDirectoryExists(static::TMP);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedSingleLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.txt');
		touch(static::TMP . '/_changes/article.txt');

		$this->storage->delete(VersionId::published(), 'default');
		$this->assertFileDoesNotExist(static::TMP . '/article.txt');
		$this->assertDirectoryExists(static::TMP);
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsNoneExisting(VersionId $id, string $language)
	{
		$this->assertFalse($this->storage->exists($id, $language));
	}

	public static function existsProvider(): array
	{
		return [
			[VersionId::changes(), 'default', [false, false]],
			[VersionId::changes(), 'en', [true, true]],
			[VersionId::published(), 'default', [true, true]],
			[VersionId::published(), 'en', [false, false]]
		];
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedNoneExisting(VersionId $id, string $language)
	{
		$this->assertNull($this->storage->modified($id, $language));
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedSomeExisting(VersionId $id, string $language, int|null $expected)
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.txt', 1234567890);
		touch(static::TMP . '/_changes/article.en.txt', 1234567890);

		$this->assertSame($expected, $this->storage->modified($id, $language));
	}

	public static function modifiedProvider(): array
	{
		return [
			[VersionId::changes(), 'default', null],
			[VersionId::changes(), 'en', 1234567890],
			[VersionId::published(), 'default', 1234567890],
			[VersionId::published(), 'en', null]
		];
	}

	/**
	 * @covers ::read
	 */
	public function testReadChangesMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make(static::TMP . '/_changes');
		Data::write(static::TMP . '/_changes/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::changes(), 'en'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadChangesSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make(static::TMP . '/_changes');
		Data::write(static::TMP . '/_changes/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::changes(), 'default'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadPublishedMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write(static::TMP . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::published(), 'en'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadPublishedSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write(static::TMP . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(VersionId::published(), 'default'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesMultiLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/_changes/article.en.txt', 123456);
		$this->assertSame(123456, filemtime(static::TMP . '/_changes/article.en.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::changes(), 'en');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime(static::TMP . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesSingleLang()
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/_changes/article.txt', 123456);
		$this->assertSame(123456, filemtime(static::TMP . '/_changes/article.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::changes(), 'default');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime(static::TMP . '/_changes/article.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchPublishedMultiLang()
	{
		touch(static::TMP . '/article.en.txt', 123456);
		$this->assertSame(123456, filemtime(static::TMP . '/article.en.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::published(), 'en');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime(static::TMP . '/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchPublishedSingleLang()
	{
		touch(static::TMP . '/article.txt', 123456);
		$this->assertSame(123456, filemtime(static::TMP . '/article.txt'));

		$minTime = time();

		$this->storage->touch(VersionId::published(), 'default');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime(static::TMP . '/article.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateChangesMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make(static::TMP . '/_changes');
		Data::write(static::TMP . '/_changes/article.en.txt', $fields);

		$this->storage->update(VersionId::changes(), 'en', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateChangesSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make(static::TMP . '/_changes');
		Data::write(static::TMP . '/_changes/article.txt', $fields);

		$this->storage->update(VersionId::changes(), 'default', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/_changes/article.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdatePublishedMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write(static::TMP . '/article.en.txt', $fields);

		$this->storage->update(VersionId::published(), 'en', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/article.en.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdatePublishedSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write(static::TMP . '/article.txt', $fields);

		$this->storage->update(VersionId::published(), 'default', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/article.txt'));
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileProvider
	 */
	public function testContentFile(string $type, VersionId $id, string $language, string $expected)
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$site = $app->site();

		$model = match ($type) {
			'file' => new File([
				'parent'   => $site,
				'filename' => 'image.jpg'
			]),
			'page' => new Page([
				'kirby'    => $app,
				'slug'     => 'a-page',
				'template' => 'article'
			]),
			'site' => $site,
			'user' => new User([
				'kirby' => $app,
				'id'    => 'abcdefgh'
			])
		};

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile($id, $language));
	}

	public static function contentFileProvider(): array
	{
		return [
			['file', VersionId::changes(), 'default', 'content/_changes/image.jpg.txt'],
			['file', VersionId::changes(), 'en', 'content/_changes/image.jpg.en.txt'],
			['file', VersionId::published(), 'default', 'content/image.jpg.txt'],
			['file', VersionId::published(), 'en', 'content/image.jpg.en.txt'],
			['page', VersionId::changes(), 'default', 'content/a-page/_changes/article.txt'],
			['page', VersionId::changes(), 'en', 'content/a-page/_changes/article.en.txt'],
			['page', VersionId::published(), 'default', 'content/a-page/article.txt'],
			['page', VersionId::published(), 'en', 'content/a-page/article.en.txt'],
			['site', VersionId::changes(), 'default', 'content/_changes/site.txt'],
			['site', VersionId::changes(), 'en', 'content/_changes/site.en.txt'],
			['site', VersionId::published(), 'default', 'content/site.txt'],
			['site', VersionId::published(), 'en', 'content/site.en.txt'],
			['user', VersionId::changes(), 'default', 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', VersionId::changes(), 'en', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', VersionId::published(), 'default', 'site/accounts/abcdefgh/user.txt'],
			['user', VersionId::published(), 'en', 'site/accounts/abcdefgh/user.en.txt'],
		];
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileDraftProvider
	 */
	public function testContentFileDraft(string $language, string $expected)
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$model = new Page([
			'kirby' => $app,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile(VersionId::changes(), $language));
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileDraftProvider
	 */
	public function testContentFileDraftPublished(string $language, string $expected)
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$model = new Page([
			'kirby' => $app,
			'root' => static::TMP,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);

		$storage = new PlainTextContentStorageHandler($model);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Drafts cannot have a published content file');
		$storage->contentFile(VersionId::published(), $language);
	}

	public static function contentFileDraftProvider(): array
	{
		return [
			['default', 'content/_drafts/a-page/article.txt'],
			['en', 'content/_drafts/a-page/article.en.txt'],
		];
	}
	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesMultiLang()
	{
		new App([
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$this->assertSame([
			static::TMP . '/_changes/article.en.txt',
			static::TMP . '/_changes/article.de.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesSingleLang()
	{
		$this->assertSame([
			static::TMP . '/_changes/article.txt'
		], $this->storage->contentFiles(VersionId::changes()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedMultiLang()
	{
		new App([
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$this->assertSame([
			static::TMP . '/article.en.txt',
			static::TMP . '/article.de.txt'
		], $this->storage->contentFiles(VersionId::published()));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedSingleLang()
	{
		$this->assertSame([
			static::TMP . '/article.txt'
		], $this->storage->contentFiles(VersionId::published()));
	}
}
