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
	public static $languageDefault;
	public static $languageEn;
	protected $tmp = __DIR__ . '/tmp';
	protected $model;
	protected $storage;

	public static function setUpBeforeClass(): void
	{
		static::$languageDefault = new Language(['code' => 'default']);
		static::$languageEn      = new Language(['code' => 'en']);
	}

	public static function tearDownAfterClass(): void
	{
		static::$languageDefault = null;
		static::$languageEn      = null;
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

		$this->storage->create('changes', static::$languageEn, $fields);
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

		$this->storage->create('changes', static::$languageDefault, $fields);
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

		$this->storage->create('published', static::$languageEn, $fields);
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

		$this->storage->create('published', static::$languageDefault, $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateInvalidType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->create('invalid', static::$languageDefault, []);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		// test idempotency
		$this->storage->delete('published', static::$languageDefault);
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

		$this->storage->delete('changes', static::$languageEn);
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

		$this->storage->delete('changes', static::$languageDefault);
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

		$this->storage->delete('published', static::$languageEn);
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

		$this->storage->delete('published', static::$languageDefault);
		$this->assertFileDoesNotExist($this->tmp . '/article.txt');
		$this->assertDirectoryExists($this->tmp);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->delete('invalid', static::$languageDefault);
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsNoneExisting(string $id, string|null $language)
	{
		if ($language !== null) {
			$language = static::$$language;
		}

		$this->assertFalse($this->storage->exists($id, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingMultiLang(string $id, string|null $language, array $expected)
	{
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

		$this->assertSame($expected[0], $this->storage->exists($id, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingSingleLang(string $id, string|null $language, array $expected)
	{
		if ($language !== null) {
			$language = static::$$language;
		}

		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.en.txt');

		$this->assertSame($expected[1], $this->storage->exists($id, $language));
	}

	public function existsProvider(): array
	{
		return [
			['changes', null, [true, false]],
			['changes', 'languageDefault', [false, false]],
			['changes', 'languageEn', [true, true]],
			['published', null, [false, true]],
			['published', 'languageDefault', [true, true]],
			['published', 'languageEn', [false, false]]
		];
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->exists('invalid', static::$languageDefault);
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedNoneExisting(string $id, string $language)
	{
		$language = static::$$language;

		$this->assertNull($this->storage->modified($id, $language));
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedSomeExisting(string $id, string $language, int|null $expected)
	{
		$language = static::$$language;

		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt', 1234567890);
		touch($this->tmp . '/_changes/article.en.txt', 1234567890);

		$this->assertSame($expected, $this->storage->modified($id, $language));
	}

	public function modifiedProvider(): array
	{
		return [
			['changes', 'languageDefault', null],
			['changes', 'languageEn', 1234567890],
			['published', 'languageDefault', 1234567890],
			['published', 'languageEn', null]
		];
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->modified('invalid', static::$languageDefault);
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

		$this->assertSame($fields, $this->storage->read('changes', static::$languageEn));
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

		$this->assertSame($fields, $this->storage->read('changes', static::$languageDefault));
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

		$this->assertSame($fields, $this->storage->read('published', static::$languageEn));
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

		$this->assertSame($fields, $this->storage->read('published', static::$languageDefault));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (en)" does not already exist');

		$this->storage->read('published', static::$languageEn);
	}

	/**
	 * @covers ::read
	 */
	public function testReadInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->read('invalid', static::$languageDefault);
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

		$this->storage->touch('changes', static::$languageEn);

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

		$this->storage->touch('changes', static::$languageDefault);

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

		$this->storage->touch('published', static::$languageEn);

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

		$this->storage->touch('published', static::$languageDefault);

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

		$this->storage->touch('published', static::$languageEn);
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->touch('invalid', static::$languageDefault);
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

		$this->storage->update('changes', static::$languageEn, $fields);
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

		$this->storage->update('changes', static::$languageDefault, $fields);
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

		$this->storage->update('published', static::$languageEn, $fields);
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

		$this->storage->update('published', static::$languageDefault, $fields);
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

		$this->storage->update('published', static::$languageEn, $fields);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->update('invalid', static::$languageDefault, []);
	}

	/**
	 * @covers ::contentFile
	 * @dataProvider contentFileProvider
	 */
	public function testContentFile(string $type, string $id, string $language, string $expected)
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
		$language = static::$$language;

		$storage = new PlainTextContentStorage($model);
		$this->assertSame($this->tmp . '/' . $expected, $storage->contentFile($id, $language));
	}

	public function contentFileProvider(): array
	{
		return [
			['file', 'changes', 'languageDefault', 'content/_changes/image.jpg.txt'],
			['file', 'changes', 'languageEn', 'content/_changes/image.jpg.en.txt'],
			['file', 'published', 'languageDefault', 'content/image.jpg.txt'],
			['file', 'published', 'languageEn', 'content/image.jpg.en.txt'],
			['page', 'changes', 'languageDefault', 'content/a-page/_changes/article.txt'],
			['page', 'changes', 'languageEn', 'content/a-page/_changes/article.en.txt'],
			['page', 'published', 'languageDefault', 'content/a-page/article.txt'],
			['page', 'published', 'languageEn', 'content/a-page/article.en.txt'],
			['site', 'changes', 'languageDefault', 'content/_changes/site.txt'],
			['site', 'changes', 'languageEn', 'content/_changes/site.en.txt'],
			['site', 'published', 'languageDefault', 'content/site.txt'],
			['site', 'published', 'languageEn', 'content/site.en.txt'],
			['user', 'changes', 'languageDefault', 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', 'changes', 'languageEn', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', 'published', 'languageDefault', 'site/accounts/abcdefgh/user.txt'],
			['user', 'published', 'languageEn', 'site/accounts/abcdefgh/user.en.txt'],
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
		$this->assertSame($this->tmp . '/' . $expected, $storage->contentFile('changes', $language));
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
		$storage->contentFile('published', $language);
	}

	public function contentFileDraftProvider(): array
	{
		return [
			['languageDefault', 'content/_drafts/a-page/article.txt'],
			['languageEn', 'content/_drafts/a-page/article.en.txt'],
		];
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->contentFile('invalid', static::$languageDefault);
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
		], $this->storage->contentFiles('changes'));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesChangesSingleLang()
	{
		$this->assertSame([
			$this->tmp . '/_changes/article.txt'
		], $this->storage->contentFiles('changes'));
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
		], $this->storage->contentFiles('published'));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesPublishedSingleLang()
	{
		$this->assertSame([
			$this->tmp . '/article.txt'
		], $this->storage->contentFiles('published'));
	}

	/**
	 * @covers ::contentFiles
	 */
	public function testContentFilesInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->contentFiles('invalid');
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
