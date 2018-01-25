<?php

use Kirby\Cms\File;
use Kirby\Cms\FileStore;
use Kirby\Cms\Media;
use Kirby\Cms\Page;
use Kirby\Cms\PageStore;
use Kirby\Cms\Pagination;
use Kirby\Cms\Site;
use Kirby\Cms\SiteStore;
use Kirby\Cms\User;
use Kirby\Cms\UserStore;
use Kirby\Http\Request;
use Kirby\Http\Router;
use Kirby\Http\Server;
use Kirby\Image\Darkroom;
use Kirby\Image\Darkroom\GdLib;
use Kirby\Text\Tags as Kirbytext;
use Kirby\Text\Markdown;
use Kirby\Text\Smartypants;
use Kirby\Toolkit\Url;
use Kirby\Toolkit\Url\Query;

return [
    'Darkroom' => [
        'singleton' => true,
        'type'      => Darkroom::class,
        'instance'  => function () {
            return new GdLib([
                'quality' => 80
            ]);
        }
    ],
    'FileStore' => [
        'singleton' => false,
        'type'      => FileStore::class,
        'instance'  => function (File $file) {
            return new FileStore($file);
        }
    ],
    'Kirbytext' => [
        'singleton' => true,
        'type'      => Kirbytext::class,
        'instance'  => function () {
            return new Kirbytext([
                'breaks' => true
            ]);
        }
    ],
    'Markdown' => [
        'singleton' => true,
        'type'      => Markdown::class,
        'instance'  => function () {
            return new Markdown([
                'breaks' => true
            ]);
        }
    ],
    'Media' => [
        'singleton' => true,
        'type'      => Media::class,
        'instance'  => function (Darkroom $darkroom, string $root, string $url) {
            return new Media([
                'darkroom' => $darkroom,
                'root'     => $root,
                'url'      => $url
            ]);
        }
    ],
    'PageStore' => [
        'singleton' => false,
        'type'      => PageStore::class,
        'instance'  => function (Page $page) {
            return new PageStore($page);
        }
    ],
    'Pagination' => [
        'singleton' => false,
        'type'      => Pagination::class,
        'instance'  => function (array $options) {

            // TODO: make this nicer!
            $options = array_merge([
                'limit' => 20,
                'page'  => App::instance()->request()->query()->get('page'),
                'url'   => Query::strip(Url::current())
            ], $options);

            return new Pagination($options);

        }
    ],
    'Request' => [
        'singleton' => true,
        'type'      => Request::class,
        'instance'  => function () {
            return new Request();
        }
    ],
    'Router' => [
        'singleton' => true,
        'type'      => Router::class,
        'instance'  => function (array $routes) {
            return new Router($array);
        }
    ],
    'Server' => [
        'singleton' => true,
        'type'      => Server::class,
        'instance'  => function () {
            return new Server;
        }
    ],
    'SiteStore' => [
        'singleton' => false,
        'type'      => SiteStore::class,
        'instance'  => function (Site $site) {
            return new SiteStore($site);
        }
    ],
    'SmartyPants' => [
        'singleton' => true,
        'type'      => SmartyPants::class,
        'instance'  => function () {
            return new SmartyPants();
        }
    ],
    'UserStore' => [
        'singleton' => false,
        'type'      => UserStore::class,
        'instance'  => function (User $user) {
            return new UserStore($user);
        }
    ],
];

