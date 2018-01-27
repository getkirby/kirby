<?php

return function (Kirby\Cms\App $app) {

    return [
        'Darkroom' => [
            'singleton' => true,
            'type'      => Kirby\Image\Darkroom::class,
            'instance'  => function () {
                return new Kirby\Image\Darkroom\GdLib([
                    'quality' => 80
                ]);
            }
        ],
        'FileStore' => [
            'singleton' => false,
            'type'      => Kirby\Cms\FileStore::class,
            'instance'  => function (Kirby\Cms\File $file) {
                return new Kirby\Cms\FileStore($file);
            }
        ],
        'Kirbytext' => [
            'singleton' => true,
            'type'      => Kirby\Text\Tags::class,
            'instance'  => function () {
                return new Kirby\Text\Tags([
                    'breaks' => true
                ]);
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
        'PageStore' => [
            'singleton' => false,
            'type'      => Kirby\Cms\PageStore::class,
            'instance'  => function (Kirby\Cms\Page $page) {
                return new Kirby\Cms\PageStore($page);
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
        'SiteStore' => [
            'singleton' => false,
            'type'      => Kirby\Cms\SiteStore::class,
            'instance'  => function (Kirby\Cms\Site $site) {
                return new Kirby\Cms\SiteStore($site);
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
        'UserStore' => [
            'singleton' => false,
            'type'      => Kirby\Cms\UserStore::class,
            'instance'  => function (Kirby\Cms\User $user) {
                return new Kirby\Cms\UserStore($user);
            }
        ],
    ];

};
