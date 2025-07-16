<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchController::class)]
class SearchControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.SearchController';

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

	public function testFiles(): void
	{
		$result = SearchController::files('fish');

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
		$result = SearchController::files();
		$this->assertCount(0, $result['results']);
	}

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
		$result = SearchController::files('fish');
		$this->assertCount(1, $result['results']);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg'
		], array_column($result['results'], 'text'));

		$this->app->impersonate('kirby');
		$result = SearchController::files('fish');
		$this->assertCount(2, $result['results']);
		$this->assertEqualsCanonicalizing([
			'blue-fish.jpg',
			'red-fish.jpg'
		], array_column($result['results'], 'text'));
	}

	public function testFilesPaginated(): void
	{
		$result = SearchController::files('fish', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);

		$result = SearchController::files('fish', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(5, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(5, $result['pagination']['total']);
	}

	public function testPages(): void
	{
		$result = SearchController::pages('beautiful');

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
		$result = SearchController::pages();
		$this->assertCount(0, $result['results']);
	}

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
		$result = SearchController::pages('friend');
		$this->assertCount(1, $result['results']);
		$this->assertEqualsCanonicalizing([
			'A friend'
		], array_column($result['results'], 'text'));

		$this->app->impersonate('kirby');
		$result = SearchController::pages('friend');
		$this->assertCount(2, $result['results']);
		$this->assertEqualsCanonicalizing([
			'A friend',
			'A secret friend'
		], array_column($result['results'], 'text'));
	}

	public function testPagesPaginated(): void
	{
		$result = SearchController::pages('beautiful', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);

		$result = SearchController::pages('beautiful', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(3, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(3, $result['pagination']['total']);
	}

	public function testUsers(): void
	{
		$result = SearchController::users('simpson');

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
		$result = SearchController::users();
		$this->assertCount(0, $result['results']);
	}

	public function testUsersPaginated(): void
	{
		$result = SearchController::users('simpson', limit: 1);
		$this->assertCount(1, $result['results']);
		$this->assertSame(1, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(0, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);

		$result = SearchController::users('simpson', limit: 1, page: 2);
		$this->assertCount(1, $result['results']);
		$this->assertSame(2, $result['pagination']['page']);
		$this->assertSame(2, $result['pagination']['pages']);
		$this->assertSame(1, $result['pagination']['offset']);
		$this->assertSame(1, $result['pagination']['limit']);
		$this->assertSame(2, $result['pagination']['total']);
	}
}
