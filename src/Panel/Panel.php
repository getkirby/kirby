<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Panel
{
    /**
     * General area definitions
     *
     * @var array
     */
    public static $areas;

    /**
     * Normalize a panel area
     *
     * @param string $id
     * @param array $area
     * @return array
     */
    public static function area(string $id, array $area): array
    {
        $area['id']              = $id;
        $area['label']           = $area['label'] ?? $id;
        $area['breadcrumb']      = $area['breadcrumb'] ?? [];
        $area['breadcrumbLabel'] = $area['breadcrumbLabel'] ?? $area['label'];
        $area['title']           = $area['label'];
        $area['menu']            = $area['menu'] ?? false;
        $area['link']            = $area['link'] ?? $id;
        $area['search']          = $area['search'] ?? null;

        return $area;
    }


    public static function areas(App $kirby): array
    {
        $root   = $kirby->root('kirby') . '/config/areas';
        $system = $kirby->system();
        $user   = $kirby->user();

        // the system is not ready
        if ($system->isOk() === false || $system->isInstalled() === false) {
            return [
                'installation' => static::area('installation', (require $root . '/installation.php')($kirby)),
            ];
        }

        // not yet authenticated
        if (!$user) {
            return [
                'login' => static::area('login', (require $root . '/login.php')($kirby)),
            ];
        }

        // no panel access
        if ($user->role()->permissions()->for('access', 'panel') === false) {
            return [
                'noaccess' => [
                    'routes' => [
                        [
                            'pattern' => '(:all)',
                            'action'  => function () {
                                go();
                            }
                        ]
                    ]
                ]
            ];
        }

        // load default areas
        $areas = [
            'site'     => static::area('site', (require $root . '/site.php')($kirby)),
            'settings' => static::area('settings', (require $root . '/settings.php')($kirby)),
            'users'    => static::area('users', (require $root . '/users.php')($kirby)),
            'account'  => static::area('account', (require $root . '/account.php')($kirby)),
        ];

        // load plugins
        foreach ($kirby->extensions('areas') as $id => $area) {
            $areas[] = static::area($id, $area($kirby));
        }

        return $areas;
    }

    /**
     * Generates an array with all assets
     * that need to be loaded for the panel (js, css, icons)
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function assets(App $kirby): array
    {
        // get the assets from the Vite dev server in dev mode;
        // dev mode = explicitly enabled in the config AND Vite is running
        $dev   = $kirby->option('panel.dev', false);
        $isDev = $dev !== false && is_file($kirby->roots()->panel() . '/.vite-running') === true;

        if ($isDev === true) {
            // vite on explicitly configured base URL or port 3000
            // of the current Kirby request
            if (is_string($dev) === true) {
                $url = $dev;
            } else {
                $url = $kirby->request()->url([
                    'port'   => 3000,
                    'path'   => null,
                    'params' => null,
                    'query'  => null
                ])->toString();
            }
        } else {
            // vite is not running, use production assets
            $url = $kirby->url('media') . '/panel/' . $kirby->versionHash();
        }

        // fetch all plugins
        $plugins = new Plugins();

        $assets = [
            'css' => [
                'index'   => $url . '/css/style.css',
                'plugins' => $plugins->url('css'),
                'custom'  => static::customCss($kirby),
            ],
            'icons' => [
                'apple-touch-icon' => [
                    'type' => 'image/png',
                    'url'  => $url . '/apple-touch-icon.png',
                ],
                'shortcut icon' => [
                    'type' => 'image/svg+xml',
                    'url'  => $url . '/favicon.svg',
                ],
                'alternate icon' => [
                    'type' => 'image/png',
                    'url'  => $url . '/favicon.png',
                ]
            ],
            'js' => [
                'vendor'       => $url . '/js/vendor.js',
                'pluginloader' => $url . '/js/plugins.js',
                'plugins'      => $plugins->url('js'),
                'custom'       => static::customJs($kirby),
                'index'        => $url . '/js/index.js',
            ]
        ];

        // during dev mode, add vite client and adapt
        // path to `index.js` - vendor and stylesheet
        // don't need to be loaded in dev mode
        if ($isDev === true) {
            $assets['js']['vite']   = $url . '/@vite/client';
            $assets['js']['index']  = $url . '/src/index.js';
            $assets['js']['vendor'] = null;
            $assets['css']['index'] = null;
        }

        // remove missing files
        $assets['css'] = array_filter($assets['css']);
        $assets['js']  = array_filter($assets['js']);

        return $assets;
    }

    /**
     * Check for a custom css file from the
     * config (panel.css)
     *
     * @param \Kirby\Cms\App $kirby
     * @return string|false
     */
    public static function customCss(App $kirby)
    {
        if ($css = $kirby->option('panel.css')) {
            $asset = asset($css);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return false;
    }

    /**
     * Check for a custom js file from the
     * config (panel.js)
     *
     * @param \Kirby\Cms\App $kirby
     * @return string|false
     */
    public static function customJs(App $kirby)
    {
        if ($js = $kirby->option('panel.js')) {
            $asset = asset($js);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return false;
    }

    /**
     * Renders the error view with provided message
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $message
     * @return \Kirby\Http\Response
     */
    public static function error(App $kirby, string $message)
    {
        return [
            'component' => 'k-error-view',
            'props'     => [
                'error'  => $message,
                'layout' => $kirby->user() ? 'inside' : 'outside'
            ],
            'view' => [
                'title' => 'Error'
            ]
        ];
    }

    /**
     * Redirect to a Panel url
     *
     * @param string|null $path
     * @return void
     */
    public static function go(?string $path = null)
    {
        go(option('panel.slug', 'panel') . '/' . trim($path, '/'));
    }

    /**
     * Load the SVG icon sprite
     * This will be injected in the
     * initial HTML document for the Panel
     *
     * @param \Kirby\Cms\App $kirby
     * @return string
     */
    public static function icons(App $kirby): string
    {
        return F::read($kirby->root('kirby') . '/panel/dist/img/icons.svg');
    }

    /**
     * Creates $inertia response array
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $props
     * @return array
     */
    public static function inertia(App $kirby, string $component, array $props = []): array
    {
        $props = static::props($kirby, $component, $props);

        // inject the inertia config as props
        $props['$component'] = $component;
        $props['$url']       = Url::current();
        $props['$version']   = $kirby->versionHash();

        return [
            'component' => $props['$component'],
            'props'     => $props,
            'url'       => $props['$url'],
            'version'   => $props['$version']
        ];
    }

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @param \Kirby\Cms\App $kirby
     * @return bool
     * @throws \Exception If Panel assets could not be moved to the public directory
     */
    public static function link(App $kirby): bool
    {
        $mediaRoot   = $kirby->root('media') . '/panel';
        $panelRoot   = $kirby->root('panel') . '/dist';
        $versionHash = $kirby->versionHash();
        $versionRoot = $mediaRoot . '/' . $versionHash;

        // check if the version already exists
        if (is_dir($versionRoot) === true) {
            return false;
        }

        // delete the panel folder and all previous versions
        Dir::remove($mediaRoot);

        // recreate the panel folder
        Dir::make($mediaRoot, true);

        // create a symlink to the dist folder
        if (Dir::copy($panelRoot, $versionRoot) !== true) {
            throw new Exception('Panel assets could not be linked');
        }

        return true;
    }

    /**
     * Creates props array for the component
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $props
     * @return array
     */
    public static function props(App $kirby, string $component, array $props = []): array
    {
        // merge with shared props
        $shared = [
            '$config' => [
                'debug'     => $kirby->option('debug'),
                'kirbytext' => $kirby->option('panel.kirbytext', true),
                'search'    => [
                    'limit' => $kirby->option('panel.search.limit', 10),
                    'type'  => $kirby->option('panel.search.type', 'pages')
                ],
                'translation' => $kirby->option('panel.language', 'en'),
            ],
            '$language' => function () use ($kirby) {
                if (
                    $kirby->option('languages') === true &&
                    $language = $kirby->language()
                ) {
                    return [
                        'code' => $language->code(),
                        'name' => $language->name(),
                    ];
                }
            },
            '$languages' => function () use ($kirby): array {
                if ($kirby->option('languages') === true) {
                    return $kirby->languages()->values(function ($language) {
                        return [
                            'code'    => $language->code(),
                            'default' => $language->isDefault(),
                            'name'    => $language->name(),
                        ];
                    });
                }

                return [];
            },
            '$permissions' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    return $user->role()->permissions()->toArray();
                }
            },
            '$props' => [],
            '$system' => [
                'ascii'     => Str::$ascii,
                'csrf'      => $kirby->option('api.csrf') ?? csrf(),
                'isLocal'   => $kirby->system()->isLocal(),
                'license'   => $kirby->system()->license(),
                'locales'   => function () use ($kirby) {
                    $locales = [];
                    $translations = $kirby->translations();
                    foreach ($translations as $translation) {
                        $locales[$translation->code()] = $translation->locale();
                    }
                    return $locales;
                },
                'multilang' => $kirby->option('languages', false) !== false,
                'slugs'     => Str::$language,
            ],
            '$translation' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    $translation = $kirby->translation($user->language());
                } else {
                    $translation = $kirby->translation($kirby->option('panel.language', 'en'));
                }

                if (!$translation) {
                    $translation = $kirby->translation('en');
                }

                return [
                    'code'      => $translation->code(),
                    'data'      => $translation->dataWithFallback(),
                    'direction' => $translation->direction(),
                    'name'      => $translation->name(),
                ];
            },
            '$urls' => [
                'api'  => $kirby->url('api'),
                'site' => $kirby->url('index')
            ],
            '$user' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    return [
                        'email'       => $user->email(),
                        'id'          => $user->id(),
                        'language'    => $user->language(),
                        'permissions' => $user->role()->permissions()->toArray(),
                        'role'        => $user->role()->id(),
                        'username'    => $user->username(),
                    ];
                }

                return null;
            }
        ];

        $props = array_merge($shared, $props);

        // is it a partial request?
        $request = $kirby->request();
        $only    = Str::split($request->header('X-Inertia-Partial-Data'));

        // only include new props in array, if partial request,
        // partial requests are made via dot notation, e.g.
        // $props.tab.columns
        if (
            empty($only) === false &&
            $request->header('X-Inertia-Partial-Component') === $component
        ) {
            $partials = [];
            foreach ($only as $partial) {
                $partials[$partial] = A::get($props, $partial);
            }
            $props = A::nest($partials);
        }

        // resolve lazy props
        return A::apply($props);
    }

    /**
     * Renders the main panel view
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $props
     * @return \Kirby\Http\Response
     */
    public static function render(App $kirby, string $component, array $props)
    {
        // get $inertia response array
        $inertia = static::inertia($kirby, $component, $props);

        // if requested, send $inertia data as JSON
        $request = $kirby->request();
        if (
            $request->method() === 'GET' &&
            ($request->header('X-Inertia') || $request->get('json'))
        ) {
            return Response::json($inertia, null, null, [
                'Vary'      => 'Accept',
                'X-Inertia' => 'true'
            ]);
        }

        try {
            if (static::link($kirby) === true) {
                usleep(1);
                go($kirby->url('index') . '/' . $kirby->path());
            }
        } catch (Throwable $e) {
            die('The Panel assets cannot be installed properly. ' . $e->getMessage());
        }

        // get the uri object for the panel url
        $uri = new Uri($url = $kirby->url('panel'));

        // fetch all plugins
        $plugins = new Plugins();

        return new Response(
            Tpl::load($kirby->root('kirby') . '/views/panel.php', [
                'assets'   => static::assets($kirby),
                'icons'    => static::icons($kirby),
                'nonce'    => $kirby->nonce(),
                'inertia'  => $inertia,
                'panelUrl' => $uri->path()->toString(true) . '/',
            ])
        );
    }

    /**
     * Router for the Panel views
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $path
     * @return \Kirby\Http\Response|false
     */
    public static function router(App $kirby, string $path = null)
    {
        if ($kirby->option('panel') === false) {
            return false;
        }

        Pagination::$validate = false;

        $user    = $kirby->user();
        $session = $kirby->session();

        // set translation for Panel UI before gathering
        // areas and routes, so that the `t()` helper
        // can already be used
        $kirby->setCurrentTranslation($user ? $user->language() : $kirby->panelLanguage());

        // language switcher
        if ($kirby->options('languages')) {
            if ($lang = get('language')) {
                $session->set('panel.language', $lang);
            }

            $kirby->setCurrentLanguage($session->get('panel.language', 'en'));
        }

        $areas  = static::areas($kirby);
        $routes = static::routes($kirby, $areas);

        // create a micro-router for the Panel
        return router($path, $kirby->request()->method(), $routes, function ($route) use ($areas, $kirby, $session, $user) {

            // route needs authentication?
            $auth   = $route->attributes()['auth'] ?? true;
            $areaId = $route->attributes()['area'] ?? null;
            $area   = $areas[$areaId] ?? null;

            // check for access before executing area routes
            if ($auth !== false && empty($areaId) === false) {
                if ($user->role()->permissions()->for('access', $areaId) !== true) {
                    return t('error.access.view');
                }
            }

            // call the route action to check the result
            try {
                $result = $route->action()->call($route, ...$route->arguments());
            } catch (Throwable $e) {
                $result = static::error($kirby, $e->getMessage());
            }

            // pass responses directly down to the Kirby router
            if (is_a($result, 'Kirby\Http\Response') === true) {
                return $result;
            }

            // interpret strings as errors
            if (is_string($result) === true) {
                $result = static::error($kirby, $result);
            }

            // only expect arrays from here on
            if (is_array($result) === false) {
                throw new InvalidArgumentException('Invalid Panel response');
            }

            // create the view based on the current area
            $view = static::view($kirby, $area, $result['view'] ?? []);

            return static::render($kirby, $result['component'], [
                '$props' => $result['props'] ?? [],
                '$view'  => $view,
                '$areas' => array_map(function ($area) {
                    // routes should not be included in the frontend object
                    unset($area['routes']);
                    return $area;
                }, $areas)
            ]);
        });
    }

    /**
     * Exract the routes from the given array
     * of active areas.
     *
     * @return array
     */
    public static function routes(App $kirby, array $areas): array
    {
        // the browser incompatibility
        // warning is always needed
        $routes = [
            [
                'pattern' => 'browser',
                'action'  => function () use ($kirby) {
                    return new Response(
                        Tpl::load($kirby->root('kirby') . '/views/browser.php')
                    );
                },
            ]
        ];

        // register all routes from areas
        foreach ($areas as $areaId => $area) {
            foreach ($area['routes'] as $route) {
                $route['area'] = $areaId;
                $routes[] = $route;
            }
        }

        // redirect routes
        $routes[] = [
            'pattern' => [
                '/',
                'installation',
                'login',
            ],
            'action' => function () use ($kirby) {
                /**
                 * If the last path has been stored in the
                 * session, redirect the user to it
                 */
                $path = trim($kirby->session()->get('panel.path'), '/');

                // ignore various paths when redirecting
                if (in_array($path, ['', 'login', 'logout', 'installation'])) {
                    $path = 'site';
                }

                Panel::go($path);
            }
        ];

        // catch all route
        $routes[] = [
            'pattern' => '(:all)',
            'action'  => function () use ($kirby) {
                return 'The view could not be found';
            }
        ];

        return $routes;
    }


    /**
     * Returns data array for view
     *
     * @param \Kirby\Cms\App $kirby
     * @param array $area
     * @param array $view
     * @return array
     */
    public static function view(App $kirby, ?array $area = null, array $view = []): array
    {
        // merge view with area defaults
        $view = array_replace_recursive($area ?? [], $view);

        $view['breadcrumb'] = $view['breadcrumb'] ?? [];
        $view['path']       = Str::after($kirby->path(), '/');
        $view['search']     = $view['search'] ?? $kirby->option('panel.search.type', 'pages');

        // make sure that routes are gone
        unset($view['routes']);

        // resolve lazy props
        return A::apply($view);
    }
}
