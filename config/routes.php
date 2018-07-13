<?php

use Kirby\Api\Api;
use Kirby\Cms\Media;
use Kirby\Cms\Panel;
use Kirby\Cms\PluginAssets;
use Kirby\Cms\Response;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response\Redirect;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\View;

return function ($kirby) {

    $api   = $kirby->options['api']['slug']   ?? 'api';
    $panel = $kirby->options['panel']['slug'] ?? 'panel';

    return [
        [
            'pattern' => '',
            'action'  => function () use ($kirby) {
                return $kirby->site()->homePage();
            }
        ],
        [
            'pattern' => $api . '/(:all)',
            'method'  => 'ALL',
            'action'  => function ($path = null) use ($kirby) {
                if ($kirby->option('api') === false) {
                    throw new NotFoundException();
                }

                $request = $kirby->request();

                return $kirby->api()->render($path, $this->method(), [
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
                    go($url, 307);
                }
            }
        ],
        [
            'pattern' => $panel . '/(:all?)',
            'action'  => function () use ($kirby) {
                if ($kirby->option('panel') === false) {
                    throw new NotFoundException();
                }

                return Panel::render($kirby);
            }
        ],
        [
            'pattern' => 'media/pages/(:all)/(:any)',
            'action'  => function ($path, $filename) use ($kirby) {
                $page = $kirby->page($path) ?? $kirby->site()->draft($path);

                if ($page && $url = Media::link($page, $filename)) {
                    go($url, 307);
                }
            }
        ],
        [
            'pattern' => 'media/site/(:any)',
            'action'  => function ($filename) use ($kirby) {
                if ($url = Media::link($kirby->site(), $filename)) {
                    go($url, 307);
                }
            }
        ],
        [
            'pattern' => 'media/users/(:any)/(:any)',
            'action'  => function ($id, $filename) use ($kirby) {
                $user = $kirby->users()->find($id);

                if ($user && $url = Media::link($user, $filename)) {
                    go($url, 307);
                }
            }
        ],
        [
            'pattern' => '(:all)\.([a-z]{2,5})',
            'action'  => function (string $path, string $extension) use ($kirby) {
                if ($page = $kirby->site()->find($path)) {
                    return Response::for($page, [], $extension);
                }

                return null;
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

                    if ($draft = $kirby->site()->draft($path)) {
                        if ($draft->isVerified(get('token'))) {
                            return $draft;
                        }
                    }
                }

                return null;
            }
        ]
    ];

};

