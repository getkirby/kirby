<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use ReflectionMethod;

/**
 * @coversDefaultClass Kirby\Content\ContentStorage
 * @covers ::__construct
 */
class ContentStorageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.ContentStorage';

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
		$this->storage = new ContentStorage($this->model);
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

		$this->assertFalse($this->storage->exists('changes', 'en'));
		$this->storage->create('changes', 'en', $fields);
		$this->assertTrue($this->storage->exists('changes', 'en'));
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

		$this->assertFalse($this->storage->exists('changes', 'default'));
		$this->storage->create('changes', 'default', $fields);
		$this->assertTrue($this->storage->exists('changes', 'default'));
	}

	/**
	 * @covers ::read
	 * @covers ::ensureExistingVersion
	 */
	public function testReadDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (default)" does not already exist');

		$this->storage->read('published', 'default');
	}

	/**
	 * @covers ::touch
	 * @covers ::ensureExistingVersion
	 */
	public function testTouchDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (default)" does not already exist');

		$this->storage->touch('published', 'default');
	}

	/**
	 * @covers ::update
	 * @covers ::ensureExistingVersion
	 */
	public function testUpdateDoesNotExist()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (default)" does not already exist');

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->update('published', 'default', $fields);
	}

	/**
	 * @covers ::language
	 * @dataProvider languageProvider
	 */
	public function testLanguageMultiLang(string|null $languageCode, bool $force, array $expectedCodes)
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

		$language = $this->language($this->storage, $languageCode, $force);
		$this->assertSame($expectedCodes[0], $language);
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

		$this->language($this->storage, 'fr', false);
	}

	/**
	 * @covers ::language
	 * @dataProvider languageProvider
	 */
	public function testLanguageSingleLang(string|null $languageCode, bool $force, array $expectedCodes)
	{
		$language = $this->language($this->storage, $languageCode, $force);
		$this->assertSame($expectedCodes[1], $language);
	}

	/**
	 * @covers ::language
	 */
	public function testLanguageSingleLangInvalid()
	{
		$language = $this->language($this->storage, 'fr', false);
		$this->assertSame('default', $language);
	}

	public static function languageProvider(): array
	{
		return [
			[null, false, ['en', 'default']],
			[null, true, ['default', 'default']],
			['en', false, ['en', 'default']],
			['en', true, ['en', 'en']],
			['de', false, ['de', 'default']],
			['de', true, ['de', 'de']],
			['fr', true, ['fr', 'fr']],
		];
	}

	protected function language(ContentStorage $obj, ...$args)
	{
		$method = new ReflectionMethod(ContentStorage::class, 'language');
		$method->setAccessible(true);
		return $method->invoke($obj, ...$args);
	}
}
