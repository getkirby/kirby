<?php

use Kirby\Api\Api;
use Kirby\Cms\Response;
use Kirby\Cms\Resources;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\View;
use Kirby\Util\Str;

return function ($kirby) {

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

                return $kirby->component('api')->render($path, $this->method(), [
                    'body'    => $request->body()->toArray(),
                    'files'   => $request->files()->toArray(),
                    'headers' => $request->headers(),
                    'query'   => $request->query()->toArray(),
                ]);
            }
        ],
        [
            'pattern' => 'media/plugins/(:any)/(:any)/(:all).(css|js|jpg|png|gif|svg)',
            'action'  => function (string $provider, string $pluginName, string $path, string $extension) use ($kirby) {
                return $kirby->plugin($provider . '/' . $pluginName)->resource($path . '.' . $extension)->link()->redirect();
            }
        ],
        [
            'pattern' => 'media/(:any)/(:all)',
            'action'  => function (string $type, string $path) use ($kirby) {
                return new Redirect($kirby->media()->resolve($kirby, $type, $path)->url(), 307);
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
                if ($page = $kirby->site()->find($path)) {
                    return $page;
                }

                // authenticated users may see drafts
                if (Str::contains($path, '_drafts') === true) {
                    $id     = dirname($path);
                    $ptoken = basename($path);

                    if ($draft = $kirby->site()->draft($id)) {
                        if ($draft->isVerified($ptoken) === true) {
                            return $draft;
                        }
                    }
                }

                return null;
            }
        ]
    ];

};

