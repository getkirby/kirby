<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class LanguageRouterTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageRouter';

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'languages' => [
				[
					'code' => 'en'
				]
			]
		]);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testRouteForSingleLanguage(): void
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => 'en',
					'action'   => fn (Language $langauge, $slug) => 'en'
				],
				[
					'pattern'  => '(:any)',
					'language' => 'de',
					'action'   => fn (Language $langauge, $slug) => 'de'
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('en', $routes[0]['language']);
		$this->assertSame('en', $router->call('anything'));
	}

	public function testRouteWithoutLanguageScope(): void
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'action'   => fn ($slug) => $slug
				]
			]
		]);

		$language = $app->language('en');

		$this->assertCount(1, $language->router()->routes());
	}

	public function testRouteForMultipleLanguages(): void
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => 'en|de',
					'action'   => fn (Language $language, $slug) => $slug
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('en|de', $routes[0]['language']);
		$this->assertSame('slug', $router->call('slug'));
	}

	public function testRouteWildcard(): void
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => '*',
					'action'   => fn (Language $language, $slug) => $slug
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('*', $routes[0]['language']);
		$this->assertSame('slug', $router->call('slug'));
	}

	public function testRouteWithPageScope(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => '*',
					'page'     => 'notes',
					'action'   => fn (Language $language, Page $page, $slug) => $slug
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();

		$this->assertSame('slug', $router->call('notes/slug'));
	}

	public function testRouteWithPageScopeAndMultiplePatterns(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'routes' => [
				[
					'pattern'  => [
						'a/(:any)',
						'b/(:any)'
					],
					'language' => '*',
					'page'     => 'notes',
					'action'   => fn (Language $language, Page $page, $slug) => $slug
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();

		$this->assertSame('slug', $router->call('notes/a/slug'));
		$this->assertSame('slug', $router->call('notes/b/slug'));
	}

	public function testRouteWithPageScopeAndInvalidPage(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => '*',
					'page'     => 'does-not-exist',
					'action'   => fn (Language $language, Page $page, $slug) => $slug
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" does not exist');

		$language = $app->language('en');
		$router   = $language->router()->call('notes/a/slug');
	}

	public function testUUIDRoute(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'notes',
					],
					[
						'slug' => 'albums',
					]
				]
			],
		]);

		$uuid = $app->page('notes')->uuid();
		$uuid->populate();

		$language = $app->language('en');
		$response = $language->router()->call('@/page/' . $uuid->id());

		$this->assertSame(302, $response->code());
		$this->assertSame('/en/notes', $response->header('Location'));

		// not cached
		$uuid = $app->page('albums')->uuid();
		$response = $language->router()->call('@/page/' . $uuid->id());
		$this->assertFalse($response);
	}
}
