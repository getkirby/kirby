<?php

return function ($app) {

    return [
        'Api' => [
            'singleton' => true,
            'type'      => Kirby\Api\Api::class,
            'instance'  => function () {
                return new Kirby\Api\Api(include __DIR__ . '/api.php');
            }
        ],
        'Email' => [
            'singleton' => true,
            'type'      => Kirby\Email\Email::class,
            'instance'  => function (array $props = []) use ($app) {
                return new Kirby\Email\PHPMailer($props, $props['debug'] ?? false);
            }
        ],
        'Kirbytext' => [
            'singleton' => true,
            'type'      => Kirby\Text\KirbyText::class,
            'instance'  => function () use ($app) {

                // pass all Kirby options to the tags
                Kirby\Text\KirbyTag::$options = $app->options();

                return new Kirby\Text\KirbyText;

            }
        ],
        'Markdown' => [
            'singleton' => true,
            'type'      => Kirby\Text\Markdown::class,
            'instance'  => function () {
                return new Kirby\Text\Markdown([
                    'breaks' => true
                ]);
            }
        ],
        'Pagination' => [
            'singleton' => false,
            'type'      => Kirby\Cms\Pagination::class,
            'instance'  => function (array $options = []) use ($app) {

                // TODO: make this nicer!
                $options = array_merge([
                    'limit' => 20,
                    'page'  => $app->request()->query()->get('page'),
                    'url'   => Kirby\Toolkit\Url\Query::strip(Kirby\Toolkit\Url::current())
                ], $options);

                return new Kirby\Cms\Pagination($options);
            }
        ],
        'Request' => [
            'singleton' => true,
            'type'      => Kirby\Http\Request::class,
            'instance'  => function () {
                return new Kirby\Http\Request();
            }
        ],
        'Response' => [
            'singleton' => false,
            'type'      => Kirby\Http\Response::class,
            'instance'  => function ($input) {
                return Kirby\Cms\Response::for($input);
            }
        ],
        'Roles' => [
            'singleton' => true,
            'type'      => Kirby\Cms\Roles::class,
            'instance'  => function () {
                return Kirby\Cms\Roles::factory();
            }
        ],
        'Router' => [
            'singleton' => true,
            'type'      => Kirby\Http\Router::class,
            'instance'  => function (array $routes = []) {
                return new Kirby\Http\Router($routes);
            }
        ],
        'Server' => [
            'singleton' => true,
            'type'      => Kirby\Http\Server::class,
            'instance'  => function () {
                return new Kirby\Http\Server;
            }
        ],
        'Site' => [
            'singleton' => true,
            'type'      => Kirby\Cms\Site::class,
            'instance'  => function () use ($app) {
                return new Kirby\Cms\Site([
                    'errorPageId' => 'error',
                    'homePageId'  => 'home',
                    'url'         => $app->url('index'),
                    'kirby'       => $app,
                    'store'       => Kirby\Cms\SiteStore::class,
                ]);
            }
        ],
        'SmartyPants' => [
            'singleton' => true,
            'type'      => Kirby\Text\SmartyPants::class,
            'instance'  => function () {
                return new Kirby\Text\SmartyPants();
            }
        ],
        'Snippet' => [
            'singleton' => false,
            'type'      => Kirby\Cms\Snippet::class,
            'instance'  => function (string $name, array $data = []) {
                return new Kirby\Cms\Snippet($name, $data);
            }
        ],
        'Session' => [
            'singleton' => true,
            'type'      => Kirby\Session\AutoSession::class,
            'instance'  => function () use ($app) {
                $options = (array)$app->option('session');
                return new Kirby\Session\AutoSession($app->root('sessions'), $options);
            }
        ],
        'Template' => [
            'singleton' => false,
            'type'      => Kirby\Cms\Template::class,
            'instance'  => function (string $name, array $data = [], string $appendix = null) {
                return new Kirby\Cms\Template($name, $data, $appendix);
            }
        ],
    ];

};
