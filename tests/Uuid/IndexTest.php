<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;

/**
 * @coversDefaultClass \Kirby\Uuid\Index
 */
class IndexTest extends TestCase
{
	/**
	 * @covers ::collection
	 */
	public function testCollection()
	{
		// without
		$uuid       = Uuid::for('page://my-id');
		$collection = Index::collection($uuid);
		$this->assertInstanceOf(Generator::class, $collection);
		$this->assertSame(0, iterator_count($collection));

		// with
		$pages      = Pages::factory([['slug' => 'a'], ['slug' => 'b']]);
		$uuid       = Uuid::for('page://my-id', $pages);
		$collection = Index::collection($uuid);
		$this->assertInstanceOf(Generator::class, $collection);
		$this->assertSame(2, iterator_count($collection));
	}

	/**
	 * @covers ::find
	 * @covers ::findInContent
	 */
	public function testFindPage()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'children' => [
							[
								'slug' => 'b',
								'content' => ['uuid' => 'my-id']
							],
							['slug' => 'c']
						]
					]
				],
				'drafts' => [
					[
						'slug' => 'y',
						'content' => ['uuid' => 'my-draft']
					],
				],
			]
		]);

		$page = $app->page('a/b');
		$uuid = Uuid::for('page://my-id');
		$this->assertSame($page, Index::find($uuid));

		// with context collection
		$pages = new Pages([
			new Page(['slug' => 'd']),
			new Page(['slug' => 'e', 'content' => ['uuid' => 'my-other-id']])
		]);
		$uuid = Uuid::for('page://my-other-id', $pages);
		$this->assertSame($pages->find('e'), Index::find($uuid));

		// draft
		$draft = $app->page('y');
		$uuid = Uuid::for('page://my-draft');
		$this->assertSame($draft, Index::find($uuid));
	}

	/**
	 * @covers ::find
	 * @covers ::findInContent
	 */
	public function testFindFile()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'my-page-file']
							]
						]
					]
				],
				'files' => [
					[
						'filename' => 'b.jpg',
						'content'  => ['uuid' => 'my-site-file']
					]
				]
			],
			'users' => [
				[
					'id'    => 'y',
					'email' => 'homer@simpson.de',
					'files' => [
						[
							'filename' => 'c.jpg',
							'content'  => ['uuid' => 'my-user-file']
						]
					]
				]
			]
		]);

		// page file
		$file = $app->page('a')->file('a.jpg');
		$uuid = Uuid::for('file://my-page-file');
		$this->assertSame($file, Index::find($uuid));

		// site file
		$file = $app->site()->file('b.jpg');
		$uuid = Uuid::for('file://my-site-file');
		$this->assertSame($file, Index::find($uuid));

		// user file
		$file = $app->user('y')->file('c.jpg');
		$uuid = Uuid::for('file://my-user-file');
		$this->assertSame($file, Index::find($uuid));
	}

	/**
	 * @covers ::find
	 */
	public function testFindNotFound()
	{
		$uuid = Uuid::for('page://nothing-to-see');
		$this->assertNull(Index::find($uuid));

		$uuid = Uuid::for('user://does-not-need-index');
		$this->assertNull(Index::find($uuid));
	}

	/**
	 * @covers ::populate
	 * @covers ::blocks
	 * @covers ::fields
	 * @covers ::pages
	 * @covers ::structures
	 */
	public function testPopulate()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'content'  => ['uuid' => 'my-page'],
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'my-page-file']
							]
						]
					]
				],
				'files' => [
					[
						'filename' => 'b.jpg',
						'content'  => ['uuid' => 'my-site-file']
					]
				]
			],
			'users' => [
				[
					'id'    => 'y',
					'email' => 'homer@simpson.de',
					'files' => [
						[
							'filename' => 'c.jpg',
							'content'  => ['uuid' => 'my-user-file']
						]
					]
				]
			]
		]);

		$page     = $app->page('a');
		$pageFile = $page->file('a.jpg');
		$siteFile = $app->site()->file('b.jpg');
		$userFile = $app->user('y')->file('c.jpg');

		$this->assertFalse(Uuid::for($page)->isCached());
		$this->assertFalse(Uuid::for($pageFile)->isCached());
		$this->assertFalse(Uuid::for($siteFile)->isCached());
		$this->assertFalse(Uuid::for($userFile)->isCached());

		Index::populate();

		$this->assertTrue(Uuid::for($page)->isCached());
		$this->assertTrue(Uuid::for($pageFile)->isCached());
		$this->assertTrue(Uuid::for($siteFile)->isCached());
		$this->assertTrue(Uuid::for($userFile)->isCached());
	}

	/**
	 * @covers ::fields
	 * @covers ::blocks
	 */
	public function testBlocks()
	{
		$this->app->clone([
			'roots' => [
				'content' => $this->fixtures,
			],
			'blueprints' => [
				'pages/album' => [
					'fields' => [
						'photographer' => ['type' => 'structure'],
						'notes' => ['type' => 'blocks'],
						'foo' => ['type' => 'radio']
					]
				],
				'pages/note' => [
					'fields' => [
						'author' => ['type' => 'structure'],
						'text' => ['type' => 'blocks'],
						'foo' => ['type' => 'select']
					]
				]
			]
		]);

		$this->assertSame(2, iterator_count(Index::blocks()));
	}

	/**
	 * @covers ::fields
	 * @covers ::structures
	 */
	public function testStructures()
	{
		$this->app->clone([
			'roots' => [
				'content' => $this->fixtures,
			],
			'blueprints' => [
				'pages/album' => [
					'fields' => [
						'photographer' => ['type' => 'structure'],
						'notes' => ['type' => 'blocks'],
						'foo' => ['type' => 'radio']
					]
				],
				'pages/note' => [
					'fields' => [
						'author' => ['type' => 'structure'],
						'text' => ['type' => 'blocks'],
						'foo' => ['type' => 'select']
					]
				]
			]
		]);

		$this->assertSame(3, iterator_count(Index::structures()));
	}
}
