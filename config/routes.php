<?php

use Kirby\Api\Api;
use Kirby\Cms\Response;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\View;

$kirby = $this;

return [
    [
        'pattern' => '',
        'action'  => function () use ($kirby) {
            return $kirby->site()->homePage();
        }
    ],
    [
        'pattern' => 'api/(:all)',
        'method'  => 'ALL',
        'action'  => function ($path = null) use ($kirby) {

            $request = $kirby->request();

            return $kirby->component('api')->toResponse($path, $this->method(), [
                'body'    => $request->body()->toArray(),
                'files'   => $request->files()->toArray(),
                'headers' => $request->headers(),
                'query'   => $request->query()->toArray(),
            ]);
        }
    ],
    [
        'pattern' => 'media/(:any)/(:all)',
        'action'  => function (string $type, string $path) use ($kirby) {
            try {
                return new Redirect($kirby->media()->resolve($kirby, $type, $path)->url(), 307);
            } catch (Exception $e) {
                return 404;
            }
        }
    ],
    [
        'pattern' => '(:all)\.([a-z]{2,5})',
        'action'  => function (string $path, string $extension) use ($kirby) {
            return Response::for($kirby->site()->find($path), [], $extension);
        }
    ],
    [
        'pattern' => '(:all)',
        'action'  => function (string $path) use ($kirby) {
            return $kirby->site()->find($path);
        }
    ]
];
