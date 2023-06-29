<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Content\PlainTextContentStorage
 * @covers ::__construct
 */
class PlainTextContentStorageTest extends TestCase
{
	public static $identifierChanges;
	public static $identifierPublished;
	public static $templateChanges;
	public static $templatePublished;
	public static $languageDefault;
	public static $languageEn;
	protected $tmp = __DIR__ . '/tmp';
	protected $model;
	protected $storage;

	public static function setUpBeforeClass(): void
	{
		static::$identifierChanges   = new VersionIdentifier('changes');
		static::$identifierPublished = new VersionIdentifier('published');
		static::$templateChanges     = new VersionTemplate('changes');
		static::$templatePublished   = new VersionTemplate('published');
		static::$languageDefault     = new Language(['code' => 'default']);
		static::$languageEn          = new Language(['code' => 'en']);
	}

	public static function tearDownAfterClass(): void
	{
		static::$identifierChanges   = null;
		static::$identifierPublished = null;
		static::$templateChanges     = null;
		static::$templatePublished   = null;
		static::$languageDefault     = null;
		static::$languageEn          = null;
	}

	public function setUp(): void
	{
		Dir::make($this->tmp);

		$this->model = new Page([
			'kirby' => new App(),
			'root' => $this->tmp,
			'slug' => 'a-page',
			'template' => 'article'
		]);
		$this->storage = new PlainTextContentStorage($this->model);
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove($this->tmp);
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

		$identifier = $this->storage->create(static::$templateChanges, static::$languageEn, $fields);
		$this->assertSame('changes', $identifier->type());
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.en.txt'));
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

		$identifier = $this->storage->create(static::$templateChanges, static::$languageDefault, $fields);
		$this->assertSame('changes', $identifier->type());
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.txt'));
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

		$identifier = $this->storage->create(static::$templatePublished, static::$languageEn, $fields);
		$this->assertSame('published', $identifier->type());
		$this->assertSame($fields, Data::read($this->tmp . '/article.en.txt'));
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

		$identifier = $this->storage->create(static::$templatePublished, static::$languageDefault, $fields);
		$this->assertSame('published', $identifier->type());
		$this->assertSame($fields, Data::read($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		// test idempotency
		$this->storage->delete(static::$identifierPublished, static::$languageDefault);
		$this->assertDirectoryDoesNotExist($this->tmp);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesMultiLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.en.txt');
		touch($this->tmp . '/_changes/article.en.txt');

		$this->storage->delete(static::$identifierChanges, static::$languageEn);
		$this->assertFileDoesNotExist($this->tmp . '/_changes/article.en.txt');
		$this->assertDirectoryDoesNotExist($this->tmp . '/_changes');
		$this->assertDirectoryExists($this->tmp);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesSingleLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.txt');

		$this->storage->delete(static::$identifierChanges, static::$languageDefault);
		$this->assertFileDoesNotExist($this->tmp . '/_changes/article.txt');
		$this->assertDirectoryDoesNotExist($this->tmp . '/_changes');
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedMultiLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.en.txt');
		touch($this->tmp . '/_changes/article.en.txt');

		$this->storage->delete(static::$identifierPublished, static::$languageEn);
		$this->assertFileDoesNotExist($this->tmp . '/article.en.txt');
		$this->assertDirectoryExists($this->tmp);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedSingleLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.txt');

		$this->storage->delete(static::$identifierPublished, static::$languageDefault);
		$this->assertFileDoesNotExist($this->tmp . '/article.txt');
		$this->assertDirectoryExists($this->tmp);
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsNoneExisting(string $identifier, string|null $language)
	{
		$identifier = static::$$identifier;
		if ($language !== null) {
			$language = static::$$language;
		}

		$this->assertFalse($this->storage->exists($identifier, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingMultiLang(string $identifier, string|null $language, array $expected)
	{
		$identifier = static::$$identifier;
		if ($language !== null) {
			$language = static::$$language;
		}

		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.en.txt');

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

		$this->assertSame($expected[0], $this->storage->exists($identifier, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingSingleLang(string $identifier, string|null $language, array $expected)
	{
		$identifier = static::$$identifier;
		if ($language !== null) {
			$language = static::$$language;
		}

		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.en.txt');

		$this->assertSame($expected[1], $this->storage->exists($identifier, $language));
	}

	public function existsProvider(): array
	{
		return [
			['identifierChanges', null, [true, false]],
			['identifierChanges', 'languageDefault', [false, false]],
			['identifierChanges', 'languageEn', [true, true]],
			['identifierPublished', null, [false, true]],
			['identifierPublished', 'languageDefault', [true, true]],
			['identifierPublished', 'languageEn', [false, false]]
		];
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedNoneExisting(string $identifier, string $language)
	{
		$identifier = static::$$identifier;
		$language = static::$$language;

		$this->assertNull($this->storage->modified($identifier, $language));
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedSomeExisting(string $identifier, string $language, int|null $expected)
	{
		$identifier = static::$$identifier;
		$language = static::$$language;

		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt', 1234567890);
		touch($this->tmp . '/_changes/article.en.txt', 1234567890);

		$this->assertSame($expected, $this->storage->modified($identifier, $language));
	}

	public function modifiedProvider(): array
	{
		return [
			['identifierChanges', 'languageDefault', null],
			['identifierChanges', 'languageEn', 1234567890],
			['identifierPublished', 'languageDefault', 1234567890],
			['identifierPublished', 'languageEn', null]
		];
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadChangesMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(static::$identifierChanges, static::$languageEn));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadChangesSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(static::$identifierChanges, static::$languageDefault));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadPublishedMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->tmp . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read(static::$identifierPublished, static::$languageEn));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadPublishedSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->tmp . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read(static::$identifierPublished, static::$languageDefault));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (en)" does not already exist');

		$this->storage->read(static::$identifierPublished, static::$languageEn);
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchChangesMultiLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/_changes/article.en.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/_changes/article.en.txt'));

		$minTime = time();

		$this->storage->touch(static::$identifierChanges, static::$languageEn);

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchChangesSingleLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/_changes/article.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/_changes/article.txt'));

		$minTime = time();

		$this->storage->touch(static::$identifierChanges, static::$languageDefault);

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/_changes/article.txt'));
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchPublishedMultiLang()
	{
		touch($this->tmp . '/article.en.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/article.en.txt'));

		$minTime = time();

		$this->storage->touch(static::$identifierPublished, static::$languageEn);

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchPublishedSingleLang()
	{
		touch($this->tmp . '/article.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/article.txt'));

		$minTime = time();

		$this->storage->touch(static::$identifierPublished, static::$languageDefault);

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (en)" does not already exist');

		$this->storage->touch(static::$identifierPublished, static::$languageEn);
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdateChangesMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.en.txt', $fields);

		$this->storage->update(static::$identifierChanges, static::$languageEn, $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdateChangesSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.txt', $fields);

		$this->storage->update(static::$identifierChanges, static::$languageDefault, $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.txt'));
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdatePublishedMultiLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->tmp . '/article.en.txt', $fields);

		$this->storage->update(static::$identifierPublished, static::$languageEn, $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.en.txt'));
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdatePublishedSingleLang()
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		Data::write($this->tmp . '/article.txt', $fields);

		$this->storage->update(static::$identifierPublished, static::$languageDefault, $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdateDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (en)" does not already exist');

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->update(static::$identifierPublished, static::$languageEn, $fields);
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileProvider
	 */
	public function testContentFile(string $type, string $identifier, string $language, string $expected)
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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
		$identifier = static::$$identifier;
		$language = static::$$language;

		$storage = new PlainTextContentStorage($model);
		$this->assertSame($this->tmp . '/' . $expected, $storage->contentFile($identifier, $language));
	}

	public function contentFileProvider(): array
	{
		return [
			['file', 'identifierChanges', 'languageDefault', 'content/_changes/image.jpg.txt'],
			['file', 'identifierChanges', 'languageEn', 'content/_changes/image.jpg.en.txt'],
			['file', 'identifierPublished', 'languageDefault', 'content/image.jpg.txt'],
			['file', 'identifierPublished', 'languageEn', 'content/image.jpg.en.txt'],
			['page', 'identifierChanges', 'languageDefault', 'content/a-page/_changes/article.txt'],
			['page', 'identifierChanges', 'languageEn', 'content/a-page/_changes/article.en.txt'],
			['page', 'identifierPublished', 'languageDefault', 'content/a-page/article.txt'],
			['page', 'identifierPublished', 'languageEn', 'content/a-page/article.en.txt'],
			['site', 'identifierChanges', 'languageDefault', 'content/_changes/site.txt'],
			['site', 'identifierChanges', 'languageEn', 'content/_changes/site.en.txt'],
			['site', 'identifierPublished', 'languageDefault', 'content/site.txt'],
			['site', 'identifierPublished', 'languageEn', 'content/site.en.txt'],
			['user', 'identifierChanges', 'languageDefault', 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', 'identifierChanges', 'languageEn', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', 'identifierPublished', 'languageDefault', 'site/accounts/abcdefgh/user.txt'],
			['user', 'identifierPublished', 'languageEn', 'site/accounts/abcdefgh/user.en.txt'],
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
				'index' => $this->tmp
			]
		]);

		$model = new Page([
			'kirby' => $app,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);
		$language = static::$$language;

		$storage = new PlainTextContentStorage($model);
		$this->assertSame($this->tmp . '/' . $expected, $storage->contentFile(static::$identifierChanges, $language));
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileDraftProvider
	 */
	public function testContentFileDraftPublished(string $language, string $expected)
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);

		$model = new Page([
			'kirby' => $app,
			'root' => $this->tmp,
			'isDraft' => true,
			'slug' => 'a-page',
			'template' => 'article'
		]);
		$language = static::$$language;

		$storage = new PlainTextContentStorage($model);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Drafts cannot have a published content file');
		$storage->contentFile(static::$identifierPublished, $language);
	}

	public function contentFileDraftProvider(): array
	{
		return [
			['languageDefault', 'content/_drafts/a-page/article.txt'],
			['languageEn', 'content/_drafts/a-page/article.en.txt'],
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
			$this->tmp . '/_changes/article.en.txt',
			$this->tmp . '/_changes/article.de.txt'
		], $this->storage->contentFiles(static::$identifierChanges));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesSingleLang()
	{
		$this->assertSame([
			$this->tmp . '/_changes/article.txt'
		], $this->storage->contentFiles(static::$identifierChanges));
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
			$this->tmp . '/article.en.txt',
			$this->tmp . '/article.de.txt'
		], $this->storage->contentFiles(static::$identifierPublished));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedSingleLang()
	{
		$this->assertSame([
			$this->tmp . '/article.txt'
		], $this->storage->contentFiles(static::$identifierPublished));
	}

	/**
	 * @covers ::language
	 * @dataProvider languageProvider
	 */
	public function testLanguageMultiLang(string|null $languageCode, bool $force, array $expectedCodes, bool $expectedOriginal)
	{
		$app = new App([
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

		$language = $this->storage->language($languageCode, $force);
		$this->assertSame($expectedCodes[0], $language->code());
		if ($expectedOriginal === true) {
			$this->assertSame($app->language($expectedCodes[0]), $language);
		} else {
			$this->assertNotSame($app->language($expectedCodes[0]), $language);
		}
	}

	/**
	 * @covers ::language
	 */
	public function testLanguageMultiLangInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$app = new App([
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

		$this->storage->language('fr', false);
	}

	/**
	 * @covers ::language
	 * @dataProvider languageProvider
	 */
	public function testLanguageSingleLang(string|null $languageCode, bool $force, array $expectedCodes)
	{
		$language = $this->storage->language($languageCode, $force);
		$this->assertSame($expectedCodes[1], $language->code());
	}

	/**
	 * @covers ::language
	 */
	public function testLanguageSingleLangInvalid()
	{
		$language = $this->storage->language('fr', false);
		$this->assertSame('default', $language->code());
	}

	public function languageProvider(): array
	{
		return [
			[null, false, ['en', 'default'], true],
			[null, true, ['en', 'default'], true],
			['en', false, ['en', 'default'], true],
			['en', true, ['en', 'en'], true],
			['de', false, ['de', 'default'], true],
			['de', true, ['de', 'de'], true],
			['fr', true, ['fr', 'fr'], false],
		];
	}
}
