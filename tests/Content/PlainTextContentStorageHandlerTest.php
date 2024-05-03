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

		$this->storage->create(VersionId::CHANGES, 'en', $fields);
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

		$this->storage->create(VersionId::CHANGES, 'default', $fields);
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

		$this->storage->create(VersionId::PUBLISHED, 'en', $fields);
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

		$this->storage->create(VersionId::PUBLISHED, 'default', $fields);
		$this->assertSame($fields, Data::read(static::TMP . '/article.txt'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		// test idempotency
		$this->storage->delete(VersionId::PUBLISHED, 'default');
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

		$this->storage->delete(VersionId::CHANGES, 'en');
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

		$this->storage->delete(VersionId::CHANGES, 'default');
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

		$this->storage->delete(VersionId::PUBLISHED, 'en');
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

		$this->storage->delete(VersionId::PUBLISHED, 'default');
		$this->assertFileDoesNotExist(static::TMP . '/article.txt');
		$this->assertDirectoryExists(static::TMP);
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsNoneExisting(VersionId $id, string|null $language)
	{
		$this->assertFalse($this->storage->exists($id, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingMultiLang(VersionId $id, string|null $language, array $expected)
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.txt');
		touch(static::TMP . '/_changes/article.en.txt');

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

		$this->assertSame($expected[0], $this->storage->exists($id, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingSingleLang(VersionId $id, string|null $language, array $expected)
	{
		Dir::make(static::TMP . '/_changes');
		touch(static::TMP . '/article.txt');
		touch(static::TMP . '/_changes/article.en.txt');

		$this->assertSame($expected[1], $this->storage->exists($id, $language));
	}

	public static function existsProvider(): array
	{
		return [
			[VersionId::CHANGES, null, [true, false]],
			[VersionId::CHANGES, 'default', [false, false]],
			[VersionId::CHANGES, 'en', [true, true]],
			[VersionId::PUBLISHED, null, [false, true]],
			[VersionId::PUBLISHED, 'default', [true, true]],
			[VersionId::PUBLISHED, 'en', [false, false]]
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
			[VersionId::CHANGES, 'default', null],
			[VersionId::CHANGES, 'en', 1234567890],
			[VersionId::PUBLISHED, 'default', 1234567890],
			[VersionId::PUBLISHED, 'en', null]
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

		$this->assertSame($fields, $this->storage->read(VersionId::CHANGES, 'en'));
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

		$this->assertSame($fields, $this->storage->read(VersionId::CHANGES, 'default'));
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

		$this->assertSame($fields, $this->storage->read(VersionId::PUBLISHED, 'en'));
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

		$this->assertSame($fields, $this->storage->read(VersionId::PUBLISHED, 'default'));
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

		$this->storage->touch(VersionId::CHANGES, 'en');

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

		$this->storage->touch(VersionId::CHANGES, 'default');

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

		$this->storage->touch(VersionId::PUBLISHED, 'en');

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

		$this->storage->touch(VersionId::PUBLISHED, 'default');

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

		$this->storage->update(VersionId::CHANGES, 'en', $fields);
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

		$this->storage->update(VersionId::CHANGES, 'default', $fields);
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

		$this->storage->update(VersionId::PUBLISHED, 'en', $fields);
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

		$this->storage->update(VersionId::PUBLISHED, 'default', $fields);
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
			['file', VersionId::CHANGES, 'default', 'content/_changes/image.jpg.txt'],
			['file', VersionId::CHANGES, 'en', 'content/_changes/image.jpg.en.txt'],
			['file', VersionId::PUBLISHED, 'default', 'content/image.jpg.txt'],
			['file', VersionId::PUBLISHED, 'en', 'content/image.jpg.en.txt'],
			['page', VersionId::CHANGES, 'default', 'content/a-page/_changes/article.txt'],
			['page', VersionId::CHANGES, 'en', 'content/a-page/_changes/article.en.txt'],
			['page', VersionId::PUBLISHED, 'default', 'content/a-page/article.txt'],
			['page', VersionId::PUBLISHED, 'en', 'content/a-page/article.en.txt'],
			['site', VersionId::CHANGES, 'default', 'content/_changes/site.txt'],
			['site', VersionId::CHANGES, 'en', 'content/_changes/site.en.txt'],
			['site', VersionId::PUBLISHED, 'default', 'content/site.txt'],
			['site', VersionId::PUBLISHED, 'en', 'content/site.en.txt'],
			['user', VersionId::CHANGES, 'default', 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', VersionId::CHANGES, 'en', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', VersionId::PUBLISHED, 'default', 'site/accounts/abcdefgh/user.txt'],
			['user', VersionId::PUBLISHED, 'en', 'site/accounts/abcdefgh/user.en.txt'],
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
		$this->assertSame(static::TMP . '/' . $expected, $storage->contentFile(VersionId::CHANGES, $language));
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
		$storage->contentFile(VersionId::PUBLISHED, $language);
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
		], $this->storage->contentFiles(VersionId::CHANGES));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesSingleLang()
	{
		$this->assertSame([
			static::TMP . '/_changes/article.txt'
		], $this->storage->contentFiles(VersionId::CHANGES));
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
		], $this->storage->contentFiles(VersionId::PUBLISHED));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedSingleLang()
	{
		$this->assertSame([
			static::TMP . '/article.txt'
		], $this->storage->contentFiles(VersionId::PUBLISHED));
	}
}
