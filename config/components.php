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
        'Darkroom' => [
            'singleton' => true,
            'type'      => Kirby\Image\Darkroom::class,
            'instance'  => function () {
                return new Kirby\Image\Darkroom\GdLib([
                    'quality' => 80
                ]);
            }
        ],
        'Kirbytext' => [
            'singleton' => true,
            'type'      => Kirby\Text\Tags::class,
            'instance'  => function () use ($app) {
                return new Kirby\Text\Tags($app->extensions('tags'));
            }
        ],
        'Locales' => [
            'singleton' => true,
            'type'      => Kirby\Cms\Locales::class,
            'instance'  => function () use ($app) {

                $locales = new Kirby\Cms\Locales();

                foreach (Kirby\Util\Dir::read($app->root('locales')) as $file) {
                    if (Kirby\Util\F::extension($file) !== 'json') {
                        continue;
                    }

                    $locale = new Kirby\Cms\Locale(Kirby\Util\F::name($file));
                    $locales->set($locale->id(), $locale);
                }

                return $locales;

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
        'Media' => [
            'singleton' => true,
            'type'      => Kirby\Cms\Media::class,
            'instance'  => function (array $props) {
                return new Kirby\Cms\Media($props);
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
        'Template' => [
            'singleton' => false,
            'type'      => Kirby\Cms\Template::class,
            'instance'  => function (string $name, array $data = [], string $appendix = null) {
                return new Kirby\Cms\Template($name, $data, $appendix);
            }
        ],
        'Users' => [
            'singleton' => true,
            'type'      => Kirby\Cms\Users::class,
            'instance'  => function () use ($app) {

                $users = new Kirby\Cms\Users();
                $root  = $app->root('accounts');

                foreach (Kirby\Util\Dir::read($root) as $userDirectory) {

                    if (is_dir($root . '/' . $userDirectory) === false) {
                        continue;
                    }

                    $user = new Kirby\Cms\User([
                        'email' => $userDirectory,
                        'store' => Kirby\Cms\UserStore::class
                    ]);

                    $users->set($user->id(), $user);
                }

                return $users;

            }
        ]
    ];

};
