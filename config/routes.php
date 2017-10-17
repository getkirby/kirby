<?php

use Kirby\Api\Api;
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
    ];
};
