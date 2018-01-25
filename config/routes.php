<?php

use Kirby\Cms\Api;
use Kirby\Http\Response;
use Kirby\Http\Response\Json;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\View;

$app = $this;

return [
    new Route('', 'GET', function () use ($app) {
        return $app->site()->find('home');
    }),
    new Route('panel/(:all?)', 'GET', function ($path = null) use ($app) {

        $view = new View($app->root('kirby') . '/views/panel.php', [
            'kirby' => $app
        ]);

        return new Response($view);

    }),
    new Route('api/(:all)', 'ALL', function ($path = null) use ($app) {

        $api = new Api([
            'request' => $request = $app->request(),
            'path'    => $path,
            'data'    => [
                'app'   => $app,
                'site'  => $app->site(),
                'users' => $app->users()
            ],
            'routes' => require __DIR__ . '/../api/routes.php',
            'types'  => require __DIR__ . '/../api/types.php'
        ]);

        $result = $api->result();
        $pretty = $request->query()->get('pretty') === 'true';

        if (($result['status'] ?? 'ok') === 'error') {
            return new Json($result, 400, $pretty);
        }

        return new Json($result, 200, $pretty);

    }),
    new Route('media/(:any)/(:all)', 'GET', function (string $type, string $path) use ($app) {
        try {
            return new Redirect($app->media()->resolve($app, $type, $path)->url(), 307);
        } catch (Exception $e) {
            return 404;
        }
    }),
    new Route('(:all)', 'GET', function ($path) use ($app) {
        return $app->site()->find($path);
    }),
];
