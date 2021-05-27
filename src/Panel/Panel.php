<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
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

    /**
     * Collect all registered areas
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
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

        // load default areas
        $areas = [
            'site'     => static::area('site', (require $root . '/site.php')($kirby)),
            'settings' => static::area('settings', (require $root . '/settings.php')($kirby)),
            'users'    => static::area('users', (require $root . '/users.php')($kirby)),
            'account'  => static::area('account', (require $root . '/account.php')($kirby)),
        ];

        // load plugins
        foreach ($kirby->extensions('areas') as $id => $area) {
            if (is_a($area, 'Closure') === true) {
                $areas[$id] = static::area($id, (array)$area($kirby));
            }
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
                $url = rtrim($kirby->request()->url([
                    'port'   => 3000,
                    'path'   => null,
                    'params' => null,
                    'query'  => null
                ])->toString(), '/');
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
     * Creates data array for Fiber and the component
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $data
     * @return array
     */
    public static function data(App $kirby, string $component, array $data = []): array
    {
        // shared default data
        $multilang = $kirby->option('languages', false) !== false;
        $shared = [
            '$language' => function () use ($kirby, $multilang) {
                if (
                    $multilang === true &&
                    $language = $kirby->language()
                ) {
                    return [
                        'code' => $language->code(),
                        'name' => $language->name(),
                    ];
                }
            },
            '$languages' => function () use ($kirby, $multilang): array {
                if ($multilang === true) {
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
            '$license' => $kirby->system()->license(),
            '$multilang' => $multilang,
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

        // merge with shared data
        $data = array_merge($shared, $data);

        // filter data, if it's a partial request
        $data = static::partial($kirby, $component, $data);

        // resolve lazy data entries
        return A::apply($data);
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
                'layout' => Panel::hasAccess($kirby->user()) ? 'inside' : 'outside'
            ],
            'view' => [
                'title' => 'Error'
            ]
        ];
    }

    /**
     * Creates $fiber response array
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $data
     * @return array
     */
    public static function fiber(App $kirby, string $component, array $data = []): array
    {
        $data = static::data($kirby, $component, $data);

        // inject the Fiber config as props
        $data['$component'] = $component;
        $data['$url']       = Url::current();
        $data['$version']   = $kirby->versionHash();

        return [
            'component' => $data['$component'],
            'data'      => $data,
            'url'       => $data['$url'],
            'version'   => $data['$version']
        ];
    }

    /**
     * Check for access permissions
     *
     * @param \Kirby\Cms\User|null $user
     * @param string|null $areaId
     * @return bool
     */
    public static function firewall(?User $user = null, ?string $areaId = null): bool
    {
        // a user has to be logged in
        if ($user === null) {
            throw new PermissionException(t('error.access.panel'));
        }

        // get all access permissions for the user role
        $permissions = $user->role()->permissions()->toArray()['access'];

        // check for general panel access
        if (($permissions['panel'] ?? false) !== true) {
            throw new PermissionException(t('error.access.panel'));
        }

        // don't check if the area is not defined
        if (empty($areaId) === true) {
            return true;
        }

        // undefined area permissions means access
        if (isset($permissions[$areaId]) === false) {
            return true;
        }

        // no access
        if ($permissions[$areaId] !== true) {
            throw new PermissionException(t('error.access.view'));
        }

        return true;
    }

    /**
     * Creates global data for the Panel.
     * This will be injected in the full Panel
     * view via the script tag.
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function globals(App $kirby): array
    {
        return [
            '$config' => function () use ($kirby) {
                return [
                    'debug'     => $kirby->option('debug'),
                    'kirbytext' => $kirby->option('panel.kirbytext', true),
                    'search'    => [
                        'limit' => $kirby->option('panel.search.limit', 10),
                        'type'  => $kirby->option('panel.search.type', 'pages')
                    ],
                    'translation' => $kirby->option('panel.language', 'en'),
                ];
            },
            '$system' => function () use ($kirby) {
                return [
                    'ascii'   => Str::$ascii,
                    'csrf'    => $kirby->option('api.csrf') ?? csrf(),
                    'isLocal' => $kirby->system()->isLocal(),
                    'locales' => function () use ($kirby) {
                        $locales = [];
                        foreach ($kirby->translations() as $translation) {
                            $locales[$translation->code()] = $translation->locale();
                        }
                        return $locales;
                    },
                    'slugs'   => Str::$language,
                ];
            },
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
            '$urls' => function () use ($kirby) {
                return [
                    'api'  => $kirby->url('api'),
                    'site' => $kirby->url('index')
                ];
            }
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
     * Check if the given user has access to the panel
     * or to a given area
     *
     * @param \Kirby\Cms\User|null $user
     * @param string|null $area
     * @return bool
     */
    public static function hasAccess(?User $user = null, string $area = null): bool
    {
        try {
            static::firewall($user, $area);
            return true;
        } catch (Throwable $e) {
            return false;
        }
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
     * Filters the data array and returns only
     * entries requested via a Fiber partial
     * request (or the whole array if full request).
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $data
     * @return array
     */
    public static function partial(App $kirby, string $component, array $data): array
    {
        // is it a partial request?
        $request          = $kirby->request();
        $include          = $request->header('X-Fiber-Include')   ?? get('_include');
        $requestComponent = $request->header('X-Fiber-Component') ?? get('_component');

        // split include string into an array of fields
        $include = Str::split($include);

        // if a full request is made, return all data
        if (empty($include) === true) {
            return $data;
        }

        // if a new component is requested, the include
        // parameter must be ignored and all data returned
        if (empty($requestComponent) === false && $requestComponent !== $component) {
            return $data;
        }

        // otherwise filter data based on
        // dot notation, e.g. `$props.tab.columns`
        $partial = [];

        // get all unresolved globals and their keys
        $globals    = static::globals($kirby);
        $globalKeys = array_keys($globals);

        // check if globals are requested and need to be merged
        if (empty(array_intersect($globalKeys, $include)) === false) {
            $data = array_merge_recursive($globals, $data);
        }

        // build a new array with all requested data
        foreach ($include as $partial) {
            $partials[$partial] = A::get($data, $partial);
        }

        return A::nest($partials);
    }

    /**
     * Renders the main panel view
     *
     * @param \Kirby\Cms\App $kirby
     * @param string $component
     * @param array $data
     * @return \Kirby\Http\Response
     */
    public static function render(App $kirby, string $component, array $data)
    {
        // get $fiber response array
        $fiber = static::fiber($kirby, $component, $data);

        // if requested, send $fiber data as JSON
        $request = $kirby->request();
        if (
            $request->method() === 'GET' &&
            ($request->header('X-Fiber') || get('_json'))
        ) {
            return Response::json($fiber, null, get('_pretty'), [
                'Vary'      => 'Accept',
                'X-Fiber' => 'true'
            ]);
        }

        // Full HTML response
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

        // inject globals
        $globals = static::globals($kirby);
        $fiber['data'] = array_merge_recursive(A::apply($globals), $fiber['data']);

        // fetch all plugins
        $plugins = new Plugins();

        return new Response(
            Tpl::load($kirby->root('kirby') . '/views/panel.php', [
                'assets'   => static::assets($kirby),
                'icons'    => static::icons($kirby),
                'nonce'    => $kirby->nonce(),
                'fiber'    => $fiber,
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

        // switch off pagination exceptions
        Pagination::$validate = false;

        // set the translation for Panel UI before
        // gathering areas and routes, so that the
        // `t()` helper can already be used
        static::setTranslation($kirby);

        // set the language in multi-lang installations
        static::setLanguage($kirby);

        $areas  = static::areas($kirby);
        $routes = static::routes($kirby, $areas);

        // create a micro-router for the Panel
        return router($path, $method = $kirby->request()->method(), $routes, function ($route) use ($areas, $kirby, $method, $path) {

            // trigger hook
            $route = $kirby->apply('panel.route:before', compact('route', 'path', 'method'), 'route');

            // route needs authentication?
            $auth   = $route->attributes()['auth'] ?? true;
            $areaId = $route->attributes()['area'] ?? null;
            $area   = $areas[$areaId] ?? null;

            // call the route action to check the result
            try {
                // check for access before executing area routes
                if ($auth !== false) {
                    static::firewall($kirby->user(), $areaId);
                }

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
                $result = static::error($kirby, 'Invalid Panel response');
            }

            // create the view based on the current area
            $view = static::view($kirby, $area, $result['view'] ?? []);

            $kirby->trigger('panel.route:after', compact('route', 'path', 'method', 'result', 'view'));

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
     * Extract the routes from the given array
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
     * Set the current language in multi-lang
     * installations based on the session or the
     * query language query parameter
     *
     * @param \Kirby\Cms\App $kirby
     * @return string|null
     */
    public static function setLanguage(App $kirby): ?string
    {
        // language switcher
        if ($kirby->options('languages')) {
            $session  = $kirby->session();
            $language = get('language') ?? $session->get('panel.language', 'en');

            // keep the language for the next visit
            $session->set('panel.language', $language);

            // activate the current language in Kirby
            $kirby->setCurrentLanguage($language);

            return $language;
        }

        return null;
    }

    /**
     * Set the currently active Panel translation
     * based on the current user or config
     *
     * @param \Kirby\Cms\App $kirby
     * @return string
     */
    public static function setTranslation(App $kirby): string
    {
        if ($user = $kirby->user()) {
            // use the user language for the default translation
            $translation = $user->language();
        } else {
            // fall back to the language from the config
            $translation = $kirby->panelLanguage();
        }

        $kirby->setCurrentTranslation($translation);

        return $translation;
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
