<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

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
        $this->assertEquals('(:any)', $routes[0]['pattern']);
        $this->assertEquals('en', $routes[0]['language']);
        $this->assertEquals('en', $router->call('anything'));
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
        $this->assertEquals('(:any)', $routes[0]['pattern']);
        $this->assertEquals('en|de', $routes[0]['language']);
        $this->assertEquals('slug', $router->call('slug'));
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
        $this->assertEquals('(:any)', $routes[0]['pattern']);
        $this->assertEquals('*', $routes[0]['language']);
        $this->assertEquals('slug', $router->call('slug'));
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

        $this->assertEquals('slug', $router->call('notes/slug'));
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

        $this->assertEquals('slug', $router->call('notes/a/slug'));
        $this->assertEquals('slug', $router->call('notes/b/slug'));
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

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "does-not-exist" does not exist');

        $language = $app->language('en');
        $router   = $language->router()->call('notes/a/slug');
    }
}
