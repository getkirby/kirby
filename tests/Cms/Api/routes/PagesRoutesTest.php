<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class PagesRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PagesRoutes';

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

	public function testChildrenSearchWithPostRequestIgnoresFilterBy(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'parent',
						'children' => [
							[
								'slug'    => 'photography',
								'content' => ['title' => 'Photography']
							],
							[
								'slug'    => 'design',
								'content' => ['title' => 'Design']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		// filterBy slug == photography would normally return only 1 page;
		// since filterBy is stripped from the body, both pages are returned
		$response = $app->api()->call('pages/parent/children/search', 'POST', [
			'body' => [
				'filterBy' => [
					['field' => 'slug', 'operator' => '==', 'value' => 'photography']
				]
			]
		]);

		$this->assertCount(2, $response['data']);
	}

	public function testChildrenSearchWithPostRequestIgnoresSortBy(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'parent',
						'children' => [
							[
								'slug'    => 'a',
								'content' => ['title' => 'A']
							],
							[
								'slug'    => 'b',
								'content' => ['title' => 'B']
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		// sortBy in the body is stripped; default order (a, b) is preserved
		$response = $app->api()->call('pages/parent/children/search', 'POST', [
			'body' => [
				'sortBy' => 'slug desc'
			]
		]);

		$this->assertCount(2, $response['data']);
		$this->assertSame('parent/a', $response['data'][0]['id']);
		$this->assertSame('parent/b', $response['data'][1]['id']);
	}

	public function testFilesSearchWithPostRequestIgnoresFilterBy(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'photo.jpg'],
							['filename' => 'document.pdf']
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		// filterBy filename == photo.jpg would normally return only 1 file;
		// since filterBy is stripped from the body, both files are returned
		$response = $app->api()->call('pages/a/files/search', 'POST', [
			'body' => [
				'filterBy' => [
					['field' => 'filename', 'operator' => '==', 'value' => 'photo.jpg']
				]
			]
		]);

		$this->assertCount(2, $response['data']);
	}

	public function testFilesSearchWithPostRequestIgnoresSortBy(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg']
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		// sortBy in the body is stripped; default sorted order (a, b) is preserved
		$response = $app->api()->call('pages/a/files/search', 'POST', [
			'body' => [
				'sortBy' => 'filename desc'
			]
		]);

		$this->assertCount(2, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
	}
}
