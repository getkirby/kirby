<?php

use Kirby\Api\Api;
use Kirby\Cms\PluginAssets;
use Kirby\Cms\Response;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\View;
use Kirby\Util\Str;
use Kirby\Util\F;

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
            'pattern' => 'media/plugins/index.(css|js)',
            'action'  => function (string $extension) use ($kirby) {
                return new Response(PluginAssets::index($extension), F::extensionToMime($extension));
            }
        ],
        [
            'pattern' => 'media/plugins/(:any)/(:any)/(:all).(css|gif|js|jpg|png|svg|webp)',
            'action'  => function (string $provider, string $pluginName, string $filename, string $extension) use ($kirby) {
                if ($url = PluginAssets::resolve($provider . '/' . $pluginName, $filename . '.' . $extension)) {
                    go($url);
                }
            }
        ],
        [
            'pattern' => [
                'media/site/(:any)',
                'media/pages/(:all)',
            ],
            'action'  => function ($path) use ($kirby) {
                if ($file = $kirby->file($path)) {
                    go($file->publish()->url(), 307);
                }
            }
        ],
        [
            'pattern' => 'media/users/(:any)/profile.jpg',
            'action'  => function ($id) use ($kirby) {
                if ($user = $kirby->users()->findBy('id', $id)) {
                    go($user->avatar()->publish()->url(), 307);
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

