<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\PageCopy
 */
class PageCopyTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCopy';

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	/**
	 * @covers ::children
	 */
	public function testChildren(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'children' => [
							['slug' => 'test-a'],
							['slug' => 'test-b']
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		$copy = new PageCopy($page, withChildren: true);
		$this->assertCount(2, $copy->children());

		$copy = new PageCopy($page, withChildren: false);
		$this->assertCount(0, $copy->children());
	}

	/**
	 * @covers ::convertUuids
	 * @covers ::convertChildrenUuids
	 * @covers ::convertFileUuids
	 */
	public function testConvertUuids(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'content'  => ['uuid' => 'a'],
						'children' => [
							[
								'slug'     => 'test-a',
								'content'  => ['uuid' => 'aa'],
								'files' => [
									[
										'filename' => 'test-a.jpg',
										'content'  => ['uuid' => 'file-aa'],
									],
									[
										'filename' => 'test-b.jpg',
										'content'  => ['uuid' => 'file-ab'],
									],
								]
							],
							[
								'slug'     => 'test-b',
								'content'  => ['uuid' => 'ab'],
								'children' => [
									[
										'slug'     => 'test-ba',
										'content'  => ['uuid' => 'aba'],
									]
								]
							]
						],
						'files' => [
							[
								'filename' => 'test-a.jpg',
								'content'  => ['uuid' => 'file-a'],
							],
							[
								'filename' => 'test-b.jpg',
								'content'  => ['uuid' => 'file-b'],
							],
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		$copy = new PageCopy($page);
		$copy->convertUuids(null);
		$this->assertSame(['page://a'], array_keys($copy->uuids));

		// with children (and their files)
		$copy = new PageCopy($page, withChildren: true);
		$copy->convertUuids(null);
		$this->assertSame([
			'page://a',
			'page://aa',
			'file://file-aa',
			'file://file-ab',
			'page://ab',
			'page://aba',
		], array_keys($copy->uuids));

		// with files and children
		$copy = new PageCopy($page, withFiles: true, withChildren: true);
		$copy->convertUuids(null);
		$this->assertSame([
			'page://a',
			'file://file-a',
			'file://file-b',
			'page://aa',
			'file://file-aa',
			'file://file-ab',
			'page://ab',
			'page://aba',
		], array_keys($copy->uuids));

		// with non-default language
		$language = new Language(['code' => 'de', 'default' => false]);
		$copy = new PageCopy($page, withFiles: true, withChildren: true);
		$copy->convertUuids($language);
		$this->assertSame([], array_keys($copy->uuids));

		// UUIDs disabled
		$app = $app->clone(['options' => ['content' => ['uuid' => false]]]);
		$copy = new PageCopy($page, withFiles: true, withChildren: true);
		$copy->convertUuids(null);
		$this->assertSame([], array_keys($copy->uuids));
	}

	/**
	 * @covers ::convertChildrenUuids
	 */
	public function testConvertUuidsClearChildren(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'original',
						'content'  => ['uuid' => 'a'],
						'children' => [
							[
								'slug'     => 'test-a',
								'content'  => ['uuid' => 'aa'],
								'files'    => [
									[
										'filename' => 'test-a.jpg',
										'content'  => ['uuid' => 'file-aa'],
									],
									[
										'filename' => 'test-b.jpg',
										'content'  => ['uuid' => 'file-ab'],
									],
								]
							],
							[
								'slug'    => 'test-b',
								'content' => ['uuid' => 'ab']
							]
						]
					],
					[
						'slug'     => 'copy',
						'content'  => ['uuid' => 'a'],
					],
				]
			]
		]);

		$page     = $app->page('copy');
		$original = $app->page('original');

		$copy = new PageCopy($page, $original);
		$copy->convertUuids(null);
		$this->assertSame([
			'page://a',
			'page://aa',
			'file://file-aa',
			'file://file-ab',
			'page://ab',
		], array_keys($copy->uuids));
		$this->assertSame('', $copy->uuids['page://aa']);
		$this->assertSame('', $copy->uuids['file://file-aa']);
		$this->assertSame('', $copy->uuids['file://file-ab']);
		$this->assertSame('', $copy->uuids['page://ab']);
	}

	/**
	 * @covers ::convertFileUuids
	 */
	public function testConvertUuidsClearFiles(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'original',
						'content'  => ['uuid' => 'a'],
						'files' => [
							[
								'filename' => 'test-a.jpg',
								'content'  => ['uuid' => 'file-a'],
							],
							[
								'filename' => 'test-b.jpg',
								'content'  => ['uuid' => 'file-b'],
							],
						]
					],
					[
						'slug'     => 'copy',
						'content'  => ['uuid' => 'a'],
					],
				]
			]
		]);

		$page    = $app->page('copy');
		$original = $app->page('original');

		$copy = new PageCopy($page, $original);
		$copy->convertUuids(null);
		$this->assertSame([
			'page://a',
			'file://file-a',
			'file://file-b',
		], array_keys($copy->uuids));
		$this->assertSame('', $copy->uuids['file://file-a']);
		$this->assertSame('', $copy->uuids['file://file-b']);
	}

	/**
	 * @covers ::files
	 */
	public function testFiles(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'files' => [
							['filename' => 'test-a.jpg'],
							['filename' => 'test-b.jpg'],
						]
					]
				]
			]
		]);

		$page = $app->page('test');

		$copy = new PageCopy($page, withFiles: true);
		$this->assertCount(2, $copy->files());

		$copy = new PageCopy($page, withFiles: false);
		$this->assertCount(0, $copy->files());
	}

	/**
	 * @covers ::languages
	 */
	public function testLanguages(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$page = $app->page('test');
		$copy = new PageCopy($page);
		$this->assertSame([null], $copy->languages());

		$app = $app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				],
			]
		]);

		$this->assertCount(2, $copy->languages());
	}

	/**
	 * @covers ::process
	 */
	public function testProcess(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'content'  => ['uuid' => 'a'],
						'children' => [
							[
								'slug'     => 'test-a',
								'content'  => ['uuid' => 'aa'],
								'files' => [
									[
										'filename' => 'test-a.jpg',
										'content'  => ['uuid' => 'file-aa'],
									]
								]
							]
						]
					]
				]
			]
		]);

		$page       = $app->page('test');
		$normalized = PageCopy::process($page, withChildren: true);
		$this->assertNotSame('page://a', $normalized->uuid()->toString());
		$this->assertNotSame('page://aa', $normalized->find('test-a')->uuid()->toString());
		$this->assertNotSame('file://file-aa', $normalized->find('test-a')->file()->uuid()->toString());
	}

	/**
	 * @covers ::removeSlug
	 */
	public function testRemoveSlug(): void
	{
		$app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'translations' => [
							[
								'code' => 'en'
							],
							[
								'code'    => 'de',
								'content' => ['slug' => 'taest']
							],
						]
					]
				]
			]
		]);

		$page = $app->page('test');
		$copy = new PageCopy($page);

		$copy->removeSlug(null);
		$this->assertSame('taest', $app->page('test')->slug('de'));

		$copy->removeSlug($app->language('en'));
		$this->assertSame('taest', $app->page('test')->slug('de'));

		$copy->removeSlug($app->language('de'));
		$this->assertSame('test', $app->page('test')->slug('de'));
	}

	/**
	 * @covers ::replaceUuids
	 */
	public function testReplaceUuids(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'original',
						'files' => [
							[
								'filename' => 'test-a.jpg',
								'content'  => ['uuid' => 'file-a'],
							]
						]
					],
					[
						'slug'     => 'test',
						'content'  => [
							'uuid' => 'a',
							'text' => $content = 'This is a text of file://file-a seeing whether page://a and page://aa and its file://file-aa get replaced with new UUIDs.'
						],
						'children' => [
							[
								'slug'     => 'test-a',
								'content'  => ['uuid' => 'aa'],
								'files' => [
									[
										'filename' => 'test-a.jpg',
										'content'  => ['uuid' => 'file-aa'],
									]
								]
							]
						]
					]
				]
			]
		]);

		$page     = $app->page('test');
		$original = $app->page('original');

		$copy = new PageCopy($page, $original, withChildren: true);
		$copy->convertUuids(null);
		$this->assertSame($content, $page->text()->value());

		// UUIDs disabled
		$app = $app->clone(['options' => ['content' => ['uuid' => false]]]);
		$copy->replaceUuids();
		$page->purge();
		$this->assertSame($content, $page->text()->value());

		// UUIDs enabled
		$app = $app->clone(['options' => ['content' => ['uuid' => true]]]);
		$copy->replaceUuids();
		$page->purge();
		$this->assertNotSame($content, $page->text()->value());
		$this->assertStringContainsString('This is a text of  seeing whether', $page->text()->value());
	}
}