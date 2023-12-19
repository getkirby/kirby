<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Content\PlainTextContentStorageHandler
 * @covers ::__construct
 */
class PlainTextContentStorageHandlerTest extends TestCase
{
	protected $tmp = __DIR__ . '/tmp';
	protected $model;
	protected $storage;

	public function setUp(): void
	{
		Dir::make($this->tmp);

		$this->model = new Page([
			'kirby'    => new App(),
			'root'     => $this->tmp,
			'slug'     => 'a-page',
			'template' => 'article'
		]);

		$this->storage = new PlainTextContentStorageHandler($this->model);
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

		$this->storage->create('changes', 'en', $fields);
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

		$this->storage->create('changes', 'default', $fields);
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

		$this->storage->create('published', 'en', $fields);
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

		$this->storage->create('published', 'default', $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreateInvalidType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->create('invalid', 'default', []);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		// test idempotency
		$this->storage->delete('published', 'default');
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

		$this->storage->delete('changes', 'en');
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

		$this->storage->delete('changes', 'default');
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

		$this->storage->delete('published', 'en');
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

		$this->storage->delete('published', 'default');
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

		$this->storage->delete('invalid', 'default');
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsNoneExisting(string $id, string|null $language)
	{
		$this->assertFalse($this->storage->exists($id, $language));
	}

	/**
	 * @covers ::exists
	 * @dataProvider existsProvider
	 */
	public function testExistsSomeExistingMultiLang(string $id, string|null $language, array $expected)
	{
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
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt');
		touch($this->tmp . '/_changes/article.en.txt');

		$this->assertSame($expected[1], $this->storage->exists($id, $language));
	}

	public static function existsProvider(): array
	{
		return [
			['changes', null, [true, false]],
			['changes', 'default', [false, false]],
			['changes', 'en', [true, true]],
			['published', null, [false, true]],
			['published', 'default', [true, true]],
			['published', 'en', [false, false]]
		];
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->exists('invalid', 'default');
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedNoneExisting(string $id, string $language)
	{
		$this->assertNull($this->storage->modified($id, $language));
	}

	/**
	 * @covers ::modified
	 * @dataProvider modifiedProvider
	 */
	public function testModifiedSomeExisting(string $id, string $language, int|null $expected)
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/article.txt', 1234567890);
		touch($this->tmp . '/_changes/article.en.txt', 1234567890);

		$this->assertSame($expected, $this->storage->modified($id, $language));
	}

	public static function modifiedProvider(): array
	{
		return [
			['changes', 'default', null],
			['changes', 'en', 1234567890],
			['published', 'default', 1234567890],
			['published', 'en', null]
		];
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->modified('invalid', 'default');
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

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read('changes', 'en'));
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

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read('changes', 'default'));
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

		Data::write($this->tmp . '/article.en.txt', $fields);

		$this->assertSame($fields, $this->storage->read('published', 'en'));
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

		Data::write($this->tmp . '/article.txt', $fields);

		$this->assertSame($fields, $this->storage->read('published', 'default'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->read('invalid', 'default');
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesMultiLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/_changes/article.en.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/_changes/article.en.txt'));

		$minTime = time();

		$this->storage->touch('changes', 'en');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/_changes/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchChangesSingleLang()
	{
		Dir::make($this->tmp . '/_changes');
		touch($this->tmp . '/_changes/article.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/_changes/article.txt'));

		$minTime = time();

		$this->storage->touch('changes', 'default');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/_changes/article.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchPublishedMultiLang()
	{
		touch($this->tmp . '/article.en.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/article.en.txt'));

		$minTime = time();

		$this->storage->touch('published', 'en');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/article.en.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchPublishedSingleLang()
	{
		touch($this->tmp . '/article.txt', 123456);
		$this->assertSame(123456, filemtime($this->tmp . '/article.txt'));

		$minTime = time();

		$this->storage->touch('published', 'default');

		clearstatcache();
		$this->assertGreaterThanOrEqual($minTime, filemtime($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->touch('invalid', 'default');
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

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.en.txt', $fields);

		$this->storage->update('changes', 'en', $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.en.txt'));
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

		Dir::make($this->tmp . '/_changes');
		Data::write($this->tmp . '/_changes/article.txt', $fields);

		$this->storage->update('changes', 'default', $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/_changes/article.txt'));
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

		Data::write($this->tmp . '/article.en.txt', $fields);

		$this->storage->update('published', 'en', $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.en.txt'));
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

		Data::write($this->tmp . '/article.txt', $fields);

		$this->storage->update('published', 'default', $fields);
		$this->assertSame($fields, Data::read($this->tmp . '/article.txt'));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->update('invalid', 'default', []);
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

		$storage = new PlainTextContentStorageHandler($model);
		$this->assertSame($this->tmp . '/' . $expected, $storage->contentFile($id, $language));
	}

	public static function contentFileProvider(): array
	{
		return [
			['file', 'changes', 'default', 'content/_changes/image.jpg.txt'],
			['file', 'changes', 'en', 'content/_changes/image.jpg.en.txt'],
			['file', 'published', 'default', 'content/image.jpg.txt'],
			['file', 'published', 'en', 'content/image.jpg.en.txt'],
			['page', 'changes', 'default', 'content/a-page/_changes/article.txt'],
			['page', 'changes', 'en', 'content/a-page/_changes/article.en.txt'],
			['page', 'published', 'default', 'content/a-page/article.txt'],
			['page', 'published', 'en', 'content/a-page/article.en.txt'],
			['site', 'changes', 'default', 'content/_changes/site.txt'],
			['site', 'changes', 'en', 'content/_changes/site.en.txt'],
			['site', 'published', 'default', 'content/site.txt'],
			['site', 'published', 'en', 'content/site.en.txt'],
			['user', 'changes', 'default', 'site/accounts/abcdefgh/_changes/user.txt'],
			['user', 'changes', 'en', 'site/accounts/abcdefgh/_changes/user.en.txt'],
			['user', 'published', 'default', 'site/accounts/abcdefgh/user.txt'],
			['user', 'published', 'en', 'site/accounts/abcdefgh/user.en.txt'],
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

		$storage = new PlainTextContentStorageHandler($model);
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

		$storage = new PlainTextContentStorageHandler($model);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Drafts cannot have a published content file');
		$storage->contentFile('published', $language);
	}

	public static function contentFileDraftProvider(): array
	{
		return [
			['default', 'content/_drafts/a-page/article.txt'],
			['en', 'content/_drafts/a-page/article.en.txt'],
		];
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version identifier "invalid"');

		$this->storage->contentFile('invalid', 'default');
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
}
