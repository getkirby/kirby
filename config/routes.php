<?php

use Kirby\Api\Api;
use Kirby\Cms\Assets\PageAssets;
use Kirby\Http\Response\Json;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;

return function () {

    $app     = $this;
    $site    = $this->site();
    $request = $this->request();

    return [
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
                'routes' => require __DIR__ . '/../api/routes.php',
                'types'  => require __DIR__ . '/../api/types.php'
            ]);

            return new Json($api->result());
        }),
        new Route('files/(:all)/(:any)', 'GET', function ($path, $filename) use ($app, $site) {

            try {
                $pageAssets = new PageAssets($app->root('files'), $site->find($path));
                $pageAssets->create($filename);

                return new Redirect($app->url('files') . '/' . $path . '/' . $filename, 307);
            } catch (Exception $e) {
                return 404;
            }

        }),
        new Route('(:all)', 'GET', function ($path) use ($site) {
            return $site->find($path);
        }),
    ];
};
