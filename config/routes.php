<?php

use Kirby\Cms\Api;
use Kirby\Http\Response;
use Kirby\Http\Response\Json;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\View;

$app     = $this;
$site    = $this->site();
$request = $this->request();

return [
    new Route('', 'GET', function () use ($site) {
        return $site->find('home');
    }),
    new Route('panel/(:all?)', 'GET', function ($path = null) use ($app) {

        $view = new View($app->root('kirby') . '/views/panel.php', [
            'kirby' => $app
        ]);

        return new Response($view);

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
            'routes' => require __DIR__ . '/../api/routes.php',
            'types'  => require __DIR__ . '/../api/types.php'
        ]);

        return new Json($api->result());
    }),
    new Route('media/(:any)/(:all)', 'GET', function (string $type, string $path) use ($app, $site) {
        try {
            return new Redirect($app->media()->resolve($app, $type, $path)->url(), 307);
        } catch (Exception $e) {
            return 404;
        }
    }),
    new Route('(:all)', 'GET', function ($path) use ($site) {
        return $site->find($path);
    }),
];
