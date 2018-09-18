<?php

use Kirby\Api\Api;
use Kirby\Cms\App;
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

    $routes = [
        [
            'pattern' => '',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function () use ($kirby) {

                $home = $kirby->site()->homePage();

                if ($kirby->multilang() === true && $kirby->url() !== $home->url()) {
                    return Response::redirect($kirby->site()->url());
                } else {
                    return $home;
                }

            }
        ],
        [
            'pattern' => $api . '/(:all)',
            'method'  => 'ALL',
            'env'     => 'api',
            'action'  => function ($path = null) use ($kirby) {
                if ($kirby->option('api') === false) {
                    return null;
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
            'env'     => 'media',
            'action'  => function (string $extension) use ($kirby) {
                return new Response(PluginAssets::index($extension), F::extensionToMime($extension));
            }
        ],
        [
            'pattern' => 'media/plugins/(:any)/(:any)/(:all).(css|gif|js|jpg|png|svg|webp|woff2|woff)',
            'env'     => 'media',
            'action'  => function (string $provider, string $pluginName, string $filename, string $extension) use ($kirby) {

                if ($url = PluginAssets::resolve($provider . '/' . $pluginName, $filename . '.' . $extension)) {
                    return Response::redirect($url, 307);
                }
            }
        ],
        [
            'pattern' => $panel . '/(:all?)',
            'env'     => 'panel',
            'action'  => function () use ($kirby) {
                if ($kirby->option('panel') === false) {
                    return null;
                }

                return Panel::render($kirby);
            }
        ],
        [
            'pattern' => 'media/pages/(:all)/(:any)',
            'env'     => 'media',
            'action'  => function ($path, $filename) use ($kirby) {
                $page = $kirby->page($path) ?? $kirby->site()->draft($path);

                if ($page && $url = Media::link($page, $filename)) {
                    return Response::redirect($url, 307);
                }
            }
        ],
        [
            'pattern' => 'media/site/(:any)',
            'env'     => 'media',
            'action'  => function ($filename) use ($kirby) {
                if ($url = Media::link($kirby->site(), $filename)) {
                    return Response::redirect($url, 307);
                }
            }
        ],
        [
            'pattern' => 'media/users/(:any)/(:any)',
            'env'     => 'media',
            'action'  => function ($id, $filename) use ($kirby) {
                $user = $kirby->users()->find($id);

                if ($user && $url = Media::link($user, $filename)) {
                    return Response::redirect($url, 307);
                }
            }
        ]
    ];

    // Multi-language setup
    if ($kirby->multilang() === true) {

        foreach ($kirby->languages() as $language) {
            $routes[] = [
                'pattern' => trim($language->pattern() . '/(:all?)', '/'),
                'method'  => 'ALL',
                'env'     => 'site',
                'action'  => function ($path = null) use ($kirby, $language) {
                    return $kirby->resolve($path, $language);
                }
            ];
        }

        // fallback route for unprefixed language URLs.
        $routes[] = [
            'pattern' => '(:all)',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function (string $path) use ($kirby) {

                if ($page = $kirby->page($path)) {

                    $url = $kirby->request()->url([
                        'query'    => null,
                        'params'   => null,
                        'fragment' => null
                    ]);

                    if ($url->toString() !== $page->url()) {
                        go($page->url());
                    }

                    return $page;
                }

            }
        ];

        return $routes;
    }

    // Single-language setup
    $routes[] = [
        'pattern' => '(:all)',
        'method'  => 'ALL',
        'env'     => 'site',
        'action'  => function (string $path) use ($kirby) {
            return $kirby->resolve($path);
        }
    ];

    return $routes;

};

