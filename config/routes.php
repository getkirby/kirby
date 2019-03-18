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
    $api   = $kirby->option('api.slug', 'api');
    $panel = $kirby->option('panel.slug', 'panel');

    /**
     * Before routes are running before the
     * plugin routes and cannot be overwritten by
     * plugins.
     */
    $before = [
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
                return $kirby
                    ->response()
                    ->type($extension)
                    ->body(PluginAssets::index($extension));
            }
        ],
        [
            'pattern' => 'media/plugins/(:any)/(:any)/(:all).(css|gif|js|jpg|png|svg|webp|woff2|woff)',
            'env'     => 'media',
            'action'  => function (string $provider, string $pluginName, string $filename, string $extension) use ($kirby) {
                return PluginAssets::resolve($provider . '/' . $pluginName, $filename . '.' . $extension);
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
            'pattern' => 'media/pages/(:all)/(:any)/(:any)',
            'env'     => 'media',
            'action'  => function ($path, $hash, $filename) use ($kirby) {
                return Media::link($kirby->page($path), $hash, $filename);
            }
        ],
        [
            'pattern' => 'media/site/(:any)/(:any)',
            'env'     => 'media',
            'action'  => function ($hash, $filename) use ($kirby) {
                return Media::link($kirby->site(), $hash, $filename);
            }
        ],
        [
            'pattern' => 'media/users/(:any)/(:any)/(:any)',
            'env'     => 'media',
            'action'  => function ($id, $hash, $filename) use ($kirby) {
                return Media::link($kirby->user($id), $hash, $filename);
            }
        ],
        [
            'pattern' => 'media/assets/(:all)/(:any)/(:any)',
            'env'     => 'media',
            'action'  => function ($path, $hash, $filename) use ($kirby) {
                return Media::thumb($path, $hash, $filename);
            }
        ]
    ];

    // Multi-language setup
    if ($kirby->multilang() === true) {

        // Multi-language home
        $after[] = [
            'pattern' => '',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function () use ($kirby) {
                $home = $kirby->site()->homePage();

                if ($home && $kirby->url() !== $home->url()) {
                    if ($kirby->option('languages.detect') === true) {
                        return $kirby
                            ->response()
                            ->redirect($kirby->detectedLanguage()->url());
                    } else {
                        return $kirby
                            ->response()
                            ->redirect($kirby->site()->url());
                    }
                } else {
                    return $kirby->resolve(null, $kirby->detectedLanguage()->code());
                }
            }
        ];

        foreach ($kirby->languages() as $language) {
            $after[] = [
                'pattern' => trim($language->pattern() . '/(:all?)', '/'),
                'method'  => 'ALL',
                'env'     => 'site',
                'action'  => function ($path = null) use ($kirby, $language) {
                    return $kirby->resolve($path, $language->code());
                }
            ];
        }

        // fallback route for unprefixed default language URLs.
        $after[] = [
            'pattern' => '(:all)',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function (string $path) use ($kirby) {

                // check for content representations or files
                $extension = F::extension($path);

                // try to redirect prefixed pages
                if (empty($extension) === true && $page = $kirby->page($path)) {
                    $url = $kirby->request()->url([
                        'query'    => null,
                        'params'   => null,
                        'fragment' => null
                    ]);

                    if ($url->toString() !== $page->url()) {
                        return $kirby
                            ->response()
                            ->redirect($page->url());
                    }
                }

                return $kirby->resolve($path, $kirby->defaultLanguage()->code());
            }
        ];
    } else {

        // Single-language home
        $after[] = [
            'pattern' => '',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function () use ($kirby) {
                return $kirby->resolve();
            }
        ];

        // redirect the home page folder to the real homepage
        $after[] = [
            'pattern' => $kirby->option('home', 'home'),
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function () use ($kirby) {
                return $kirby
                    ->response()
                    ->redirect($kirby->site()->url());
            }
        ];

        // Single-language subpages
        $after[] = [
            'pattern' => '(:all)',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function (string $path) use ($kirby) {
                return $kirby->resolve($path);
            }
        ];
    }

    return [
        'before' => $before,
        'after'  => $after
    ];
};
