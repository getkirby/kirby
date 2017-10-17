<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/extensions/helpers.php';
require __DIR__ . '/extensions/methods.php';

use Kirby\Api\Api;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Site;
use Kirby\FileSystem\Folder;
use Kirby\FileSystem\File;
use Kirby\Http\Request;
use Kirby\Http\Router;
use Kirby\Http\Router\Route;
use Kirby\Http\Server;
use Kirby\Http\Response;
use Kirby\Http\Response\Json;
use Kirby\Http\Response\Redirect;
use Kirby\Toolkit\Url;
use Kirby\Text\Smartypants;
use Kirby\Text\Markdown;
use Kirby\Text\Tags;
use Kirby\Toolkit\View;
use Kirby\Users\User;
use Kirby\Users\User\Auth\Password as UserAuth;
use Kirby\Users\User\Avatar as UserAvatar;
use Kirby\Users\User\Store as UserStore;
use Kirby\Users\Users;
use Kirby\Image\Darkroom\GdLib as Darkroom;

/**
 * App configuration
 */
$app = new Kirby([
    'url' => [
        '/'     => $url = Url::index(),
        'files' => $url . '/files',
    ],
    'root' => [
        '/'           => $root = dirname(__DIR__),
        'kirby'       => __DIR__,
        'files'       => $root . '/files',
        'content'     => $root . '/content',
        'controllers' => $root . '/site/controllers',
        'accounts'    => $root . '/site/accounts',
        'snippets'    => $root . '/site/snippets',
        'templates'   => $root . '/site/templates',
        'blueprints'  => $root . '/site/blueprints',
        'panel'       => $root . '/panel'
    ],
    'request'     => $request = new Request(),
    'server'      => $server  = new Server(),
    'smartypants' => new Smartypants(),
    'kirbytext'   => new Tags(),
    'markdown'    => new Markdown(['breaks' => true]),
]);

/**
 * Site setup
 */
$app->set('site', $site = new Site([
    'url'  => $app->url(),
    'root' => $app->root('content')
]));


/**
 * Schema setup
 */
$app->set('schema', function () {

    $root = $this->root('kirby') . '/fields';

    return [
        'checkboxes' => require $root . '/checkboxes.php',
        'radio'      => require $root . '/radio.php',
        'select'     => require $root . '/select.php',
        'table'      => require $root . '/table.php',
        'tags'       => require $root . '/tags.php',
    ];
});


/**
 * Users setup
 */
$app->set('users', function () {

    $folder = new Folder($this->root('accounts'));
    $users  = [];

    foreach ($folder->folders() as $root) {
        $users[] = [
            'id'     => $id = basename($root),
            'auth'   => new UserAuth,
            'store'  => new UserStore(['root' => $root]),
            'avatar' => new UserAvatar([
                'url'  => $this->url() . '/site/accounts/' . $id . '/' . $id . '.jpg',
                'root' => $root . '/' . $id . '.jpg'
            ])
        ];
    }

    return new Users($users);

});


/**
 * Darkroom
 */
$app->set('darkroom', function () {
    return new Darkroom([
        'quality' => 80
    ]);
});


/**
 * Router
 */
$router = new Router([
    new Route('', 'GET', function () use ($site) {
        return $site->find('home');
    }),
    new Route('api/(:all)', 'ALL', function ($path = null) use ($app, $site, $request) {

        header('Access-Control-Allow-Origin: *');

        $api = new Api([
            'request' => $request,
            'path'    => $path,
            'data'    => [
                'app'   => $app,
                'site'  => $site,
                'users' => $app->users()
            ],
            'routes' => require __DIR__ . '/api/routes.php',
            'types'  => require __DIR__ . '/api/types.php'
        ]);

        return new Json($api->result());

    }),
    new Route('files/(:all)/(:any)', 'GET', function ($path, $filename) use ($app, $site) {
        if ($page = $site->find($path)) {
            if ($file = $page->file($filename)) {
                $root = $app->root('/') . '/files/' . $path;
                $link = $root . '/' . $file->filename();

                if (is_dir($root) === false) {
                    mkdir($root, 0777, true);
                }

                if (is_link($link) === false) {
                    link($file->realpath(), $link);
                }

                return new Redirect($app->url('files') . '/' . $path . '/' . $filename);
            }
        }
        return 404;
    }),
    new Route('(:all)', 'GET', function ($path) use ($site) {
        return $site->find($path);
    }),
]);

// get the current path
$path = $server->get('path_info');

// fetch the page at the current path
$response = $router->call(trim($path, '/'), $request->method());

if (is_a($response, Response::class)) {
    die( $response );
}

if ($response === null) {
    $page = $site->find('error');
} else {
    $page = $response;
}

// make main objects available for all views
View::globals([
    'site'  => $site,
    'pages' => $pages = $site->children(),
    'page'  => $page,
]);

// load the template file
$template = new File($app->root('templates') . '/' . ($page->template() ?? 'default') . '.php');

// switch to the default template if the file cannot be found
if ($template->exists() === false) {
    $template = new File($app->root('templates') . '/default.php');
}

// load the controller file
if ($page->template()) {
    $controllerFile = new File($app->root('controllers') . '/' . $page->template() . '.php');

    if ($controllerFile->exists()) {
        $controllerFunc = require $controllerFile->root();
        $controllerData = $controllerFunc($site, $pages, $page);

        View::globals(array_merge($controllerData, View::globals()));
    }
}

// create the template
$view = new View($template->realpath());

// render the response
echo new Response($view->toString());
