<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class PagesRoutesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PagesRoutes';

	public function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => static::TMP,
			]
		]);
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testGet(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'b'
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/a');

		$this->assertSame('a', $response['data']['id']);

		$response = $app->api()->call('pages/a+b');

		$this->assertSame('a/b', $response['data']['id']);
	}

	public function testChildren(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'parent',
						'children' => [
							[
								'slug' => 'child-a'
							],
							[
								'slug' => 'child-b'
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/parent/children');

		$this->assertSame('parent/child-a', $response['data'][0]['id']);
		$this->assertSame('parent/child-b', $response['data'][1]['id']);
	}

	public function testChildrenWithStatusFilter(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'parent',
						'children' => [
							[
								'slug' => 'child-a',
								'num'  => 1
							],
							[
								'slug' => 'child-b'
							]
						],
						'drafts' => [
							[
								'slug' => 'draft-a'
							]
						]

					]
				]
			]
		]);

		$app->impersonate('kirby');

		// all
		$response = $app->api()->call('pages/parent/children', 'GET', [
			'query' => ['status' => 'all']
		]);

		$this->assertCount(3, $response['data']);
		$this->assertSame('parent/child-a', $response['data'][0]['id']);
		$this->assertSame('parent/child-b', $response['data'][1]['id']);
		$this->assertSame('parent/draft-a', $response['data'][2]['id']);

		// published
		$response = $app->api()->call('pages/parent/children', 'GET', [
			'query' => ['status' => 'published']
		]);

		$this->assertCount(2, $response['data']);
		$this->assertSame('parent/child-a', $response['data'][0]['id']);
		$this->assertSame('parent/child-b', $response['data'][1]['id']);

		// listed
		$response = $app->api()->call('pages/parent/children', 'GET', [
			'query' => ['status' => 'listed']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('parent/child-a', $response['data'][0]['id']);

		// unlisted
		$response = $app->api()->call('pages/parent/children', 'GET', [
			'query' => ['status' => 'unlisted']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('parent/child-b', $response['data'][0]['id']);

		// drafts
		$response = $app->api()->call('pages/parent/children', 'GET', [
			'query' => ['status' => 'drafts']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('parent/draft-a', $response['data'][0]['id']);
	}

	public function testFiles(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'c.jpg',
							],
							[
								'filename' => 'a.jpg',
							],
							[
								'filename' => 'b.jpg',
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/a/files');

		$this->assertCount(3, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
		$this->assertSame('c.jpg', $response['data'][2]['filename']);
	}

	public function testFilesOfAPageWithTheSlugFiles(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'files',
						'files' => [
							[
								'filename' => 'c.jpg',
							],
							[
								'filename' => 'a.jpg',
							],
							[
								'filename' => 'b.jpg',
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/files/files');

		$this->assertCount(3, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
		$this->assertSame('c.jpg', $response['data'][2]['filename']);
	}

	public function testFilesSorted(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => [
									'sort' => 2
								]
							],
							[
								'filename' => 'b.jpg',
								'content'  => [
									'sort' => 1
								]
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/a/files');

		$this->assertSame('b.jpg', $response['data'][0]['filename']);
		$this->assertSame('a.jpg', $response['data'][1]['filename']);
	}

	public function testFile(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/a/files/a.jpg');

		$this->assertSame('a.jpg', $response['data']['filename']);
	}
}
