<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageCopy::class)]
class PageCopyTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCopy';

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

	public function testLanguagesInMultiLanguageMode(): void
	{
		$this->setupMultiLanguage();
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		$copy = new PageCopy($page);
		$this->assertCount(2, $copy->languages());
	}

	public function testLanguagesInSingleLanguageMode(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$copy = new PageCopy($page);
		$this->assertSame([null], $copy->languages());
	}

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
		$this->assertSame($content, $copy->copy->text()->value());

		// UUIDs disabled
		$app = $app->clone(['options' => ['content' => ['uuid' => false]]]);
		$copy->replaceUuids();
		$page->purge();
		$this->assertSame($content, $copy->copy->text()->value());

		// UUIDs enabled
		$app = $app->clone(['options' => ['content' => ['uuid' => true]]]);
		$copy->replaceUuids();
		$page->purge();
		$this->assertNotSame($content, $copy->copy->text()->value());
		$this->assertStringContainsString('This is a text of  seeing whether', $copy->copy->text()->value());
	}
}
