<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\TestCase;

class LanguageRouterTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code' => 'en'
				]
			]
		]);
	}

	public function testRouteForSingleLanguage()
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => 'en',
					'action'   => function (Language $langauge, $slug) {
						return 'en';
					}
				],
				[
					'pattern'  => '(:any)',
					'language' => 'de',
					'action'   => function (Language $langauge, $slug) {
						return 'de';
					}
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertCount(1, $routes);
		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('en', $routes[0]['language']);
		$this->assertSame('en', $router->call('anything'));
	}

	public function testRouteWithoutLanguageScope()
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'action'   => function ($slug) {
						return $slug;
					}
				]
			]
		]);

		$language = $app->language('en');

		$this->assertCount(0, $language->router()->routes());
	}

	public function testRouteForMultipleLanguages()
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => 'en|de',
					'action'   => function (Language $language, $slug) {
						return $slug;
					}
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertCount(1, $routes);
		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('en|de', $routes[0]['language']);
		$this->assertSame('slug', $router->call('slug'));
	}

	public function testRouteWildcard()
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern'  => '(:any)',
					'language' => '*',
					'action'   => function (Language $language, $slug) {
						return $slug;
					}
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();
		$routes   = $router->routes();

		$this->assertCount(1, $routes);
		$this->assertSame('(:any)', $routes[0]['pattern']);
		$this->assertSame('*', $routes[0]['language']);
		$this->assertSame('slug', $router->call('slug'));
	}

	public function testRouteWithPageScope()
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
					'action'   => function (Language $language, Page $page, $slug) {
						return $slug;
					}
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();

		$this->assertSame('slug', $router->call('notes/slug'));
	}

	public function testRouteWithPageScopeAndMultiplePatterns()
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
					'action'   => function (Language $language, Page $page, $slug) {
						return $slug;
					}
				]
			]
		]);

		$language = $app->language('en');
		$router   = $language->router();

		$this->assertSame('slug', $router->call('notes/a/slug'));
		$this->assertSame('slug', $router->call('notes/b/slug'));
	}

	public function testRouteWithPageScopeAndInvalidPage()
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
					'action'   => function (Language $language, Page $page, $slug) {
						return $slug;
					}
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" does not exist');

		$language = $app->language('en');
		$router   = $language->router()->call('notes/a/slug');
	}
}
