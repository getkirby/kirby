<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class SiteRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteRoutes';

	public function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'content' => [
					'title' => 'Test Site'
				]
			]
		]);

		$this->app->impersonate('kirby');
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testGet(): void
	{
		$response = $this->app->api()->call('site', 'GET');

		$this->assertSame('Test Site', $response['data']['title']);
	}

	public function testChildren(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
					],
					[
						'slug' => 'b',
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/children', 'GET');

		$this->assertCount(2, $response['data']);
		$this->assertSame('a', $response['data'][0]['id']);
		$this->assertSame('b', $response['data'][1]['id']);
	}

	public function testChildrenWithStatusFilter(): void
	{
		$app = $this->app->clone([
			'site' => [
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
		]);

		$app->impersonate('kirby');

		// all
		$response = $app->api()->call('site/children', 'GET', [
			'query' => ['status' => 'all']
		]);

		$this->assertCount(3, $response['data']);
		$this->assertSame('child-a', $response['data'][0]['id']);
		$this->assertSame('child-b', $response['data'][1]['id']);
		$this->assertSame('draft-a', $response['data'][2]['id']);

		// published
		$response = $app->api()->call('site/children', 'GET', [
			'query' => ['status' => 'published']
		]);

		$this->assertCount(2, $response['data']);
		$this->assertSame('child-a', $response['data'][0]['id']);
		$this->assertSame('child-b', $response['data'][1]['id']);

		// listed
		$response = $app->api()->call('site/children', 'GET', [
			'query' => ['status' => 'listed']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('child-a', $response['data'][0]['id']);

		// unlisted
		$response = $app->api()->call('site/children', 'GET', [
			'query' => ['status' => 'unlisted']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('child-b', $response['data'][0]['id']);

		// drafts
		$response = $app->api()->call('site/children', 'GET', [
			'query' => ['status' => 'drafts']
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('draft-a', $response['data'][0]['id']);
	}

	public function testFiles(): void
	{
		$app = $this->app->clone([
			'site' => [
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
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/files');

		$this->assertCount(3, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
		$this->assertSame('c.jpg', $response['data'][2]['filename']);
	}

	public function testFilesSorted(): void
	{
		$app = $this->app->clone([
			'site' => [
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
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/files');

		$this->assertSame('b.jpg', $response['data'][0]['filename']);
		$this->assertSame('a.jpg', $response['data'][1]['filename']);
	}

	public function testFile(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'a.jpg',
					],
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/files/a.jpg');

		$this->assertSame('a.jpg', $response['data']['filename']);
	}

	public function testFind(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'aa'
							],
							[
								'slug' => 'ab'
							]
						],
					],
					[
						'slug' => 'b'
					]
				]
			],
		]);

		$app->impersonate('kirby');

		// find single
		$result = $app->api()->call('site/find', 'POST', [
			'body' => [
				'a',
			]
		]);

		$this->assertCount(1, $result['data']);
		$this->assertSame('a', $result['data'][0]['id']);

		// find multiple
		$result = $app->api()->call('site/find', 'POST', [
			'body' => [
				'a',
				'a/aa',
				'b'
			]
		]);

		$this->assertCount(3, $result['data']);
		$this->assertSame('a', $result['data'][0]['id']);
		$this->assertSame('a/aa', $result['data'][1]['id']);
		$this->assertSame('b', $result['data'][2]['id']);
	}


	public function testSearchWithGetRequest(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'parent',
						'content' => [
							'title' => 'Projects'
						],
						'children' => [
							[
								'slug' => 'child',
								'content' => [
									'title' => 'Photography'
								],
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/search', 'GET', [
			'query' => [
				'q' => 'Photo'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('parent/child', $response['data'][0]['id']);
	}

	public function testSearchWithPostRequest(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'parent',
						'content' => [
							'title' => 'Projects'
						],
						'children' => [
							[
								'slug' => 'child',
								'content' => [
									'title' => 'Photography'
								],
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('site/search', 'POST', [
			'body' => [
				'search' => 'Photo'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('parent/child', $response['data'][0]['id']);
	}
}
