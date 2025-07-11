<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Controller\Search
 */
class SearchTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Search';

	public function setUp(): void
	{
		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'articles',
						'children' => [
							['slug' => 'beautiful-flowers'],
							['slug' => 'beautiful-trees'],
							[
								'slug' => 'beautiful-animals',
								'files' => [
									['filename' => 'green-fish.jpg'],
									['filename' => 'pink-fish.jpg']
								]
							]
						],
						'files' => [
							['filename' => 'blue-fish.jpg'],
							['filename' => 'red-fish.jpg']
						]
					]
				],
				'files' => [
					['filename' => 'purple-fish.jpg']
				]
			],
			'users' => [
				['email' => 'homer@simpson.com'],
				['email' => 'bart@simpson.com'],
				['email' => 'net@flanders.com']
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
		App::destroy();
	}

	/**
	 * @covers ::files
	 */
	public function testFiles(): void
	{
		$result = Search::files('fish');

		$this->assertCount(5, $result['results']);
		$this->assertArrayHasKey('image', $result['results'][0]);
		$this->assertArrayHasKey('text', $result['results'][0]);
		$this->assertArrayHasKey('info', $result['results'][0]);
		$this->assertArrayHasKey('link', $result['results'][0]);
		$this->assertEqualsCanonicalizing([
			'red-fish.jpg',
			'blue-fish.jpg',
			'pink-fish.jpg',
			'green-fish.jpg',
			'purple-fish.jpg'
		], array_column($result['results'], 'text'));
		$this->assertNull($result['pagination']);

		// without query
		$result = Search::files();
		$this->assertCount(0, $result['results']);
	}

	/**
	 * @covers ::files
	 */
	public function testFilesNotListable(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'photos',
						'files' => [
							['filename' => 'blue-fish.jpg'],
							[
								'filename' => 'red-fish.jpg',
								'template' => 'secret'
							]
						]
					]
				]
			],
			'blueprints' => [
				'files/secret' => [
					'options' => [
						'list' => [
							'*'      => true,
							'editor' => false
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'homer@simpson.com',
					'role'  => 'editor'
				],
			],
			'roles' => [
				['name'  => 'editor']
			]
		]);

		$this->app->impersonate('homer@simpson.com');
		$result = Search::files('fish');
		$this->assertCount(1, $result['results']);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg'
		], array_column($result['results'], 'text'));

		$this->app->impersonate('kirby');
		$result = Search::files('fish');
		$this->assertCount(2, $result['results']);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg',
			'red-fish.jpg'
		], array_column($result['results'], 'text'));
	}

	/**
	 * @covers ::files
	 */
	public function testFilesPaginated(): void
	{
		$result = Search::files('fish', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);

		$result = Search::files('fish', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);
	}

	/**
	 * @covers ::pages
	 */
	public function testPages(): void
	{
		$result = Search::pages('beautiful');

		$this->assertCount(3, $result['results']);
		$this->assertArrayHasKey('image', $result['results'][0]);
		$this->assertArrayHasKey('text', $result['results'][0]);
		$this->assertArrayHasKey('info', $result['results'][0]);
		$this->assertArrayHasKey('link', $result['results'][0]);
		$this->assertEqualsCanonicalizing([
			'beautiful-animals',
			'beautiful-flowers',
			'beautiful-trees'
		], array_column($result['results'], 'text'));
		$this->assertNull($result['pagination']);

		// without query
		$result = Search::pages();
		$this->assertCount(0, $result['results']);
	}

	/**
	 * @covers ::pages
	 */
	public function testPagesNotListable(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a-friend',
						'content' => [
							'title' => 'A friend'
						]
					],
					[
						'slug'     => 'a-secret-friend',
						'template' => 'secret',
						'content'  => [
							'title' => 'A secret friend'
						]
					]
				]
			],
			'blueprints' => [
				'pages/secret' => [
					'options' => [
						'list' => [
							'*'      => true,
							'editor' => false
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'homer@simpson.com',
					'role'  => 'editor'
				],
			],
			'roles' => [
				['name'  => 'editor']
			]
		]);

		$this->app->impersonate('homer@simpson.com');
		$result = Search::pages('friend');
		$this->assertCount(1, $result['results']);
		$this->assertEqualsCanonicalizing([
			'A friend'
		], array_column($result['results'], 'text'));

		$this->app->impersonate('kirby');
		$result = Search::pages('friend');
		$this->assertCount(2, $result['results']);
		$this->assertEqualsCanonicalizing([
			'A friend',
			'A secret friend'
		], array_column($result['results'], 'text'));
	}

	/**
	 * @covers ::pages
	 */
	public function testPagesPaginated(): void
	{
		$result = Search::pages('beautiful', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);

		$result = Search::pages('beautiful', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);
	}

	/**
	 * @covers ::users
	 */
	public function testUsers(): void
	{
		$result = Search::users('simpson');

		$this->assertCount(2, $result['results']);
		$this->assertArrayHasKey('image', $result['results'][0]);
		$this->assertArrayHasKey('text', $result['results'][0]);
		$this->assertArrayHasKey('info', $result['results'][0]);
		$this->assertArrayHasKey('link', $result['results'][0]);
		$this->assertEqualsCanonicalizing([
			'bart@simpson.com',
			'homer@simpson.com'
		], array_column($result['results'], 'text'));
		$this->assertNull($result['pagination']);

		// without query
		$result = Search::users();
		$this->assertCount(0, $result['results']);
	}

	/**
	 * @covers ::users
	 */
	public function testUsersPaginated(): void
	{
		$result = Search::users('simpson', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);

		$result = Search::users('simpson', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);
	}
}
