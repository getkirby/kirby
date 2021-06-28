<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\User;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Toolkit\A;
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
     * @return array
     */
    public static function areas(): array
    {
        $kirby  = kirby();
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
     * @return array
     */
    public static function assets(): array
    {
        $kirby = kirby();

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
     * @return string|null
     */
    public static function customCss(): ?string
    {
        if ($css = kirby()->option('panel.css')) {
            $asset = asset($css);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return null;
    }

    /**
     * Check for a custom js file from the
     * config (panel.js)
     *
     * @return string|null
     */
    public static function customJs(): ?string
    {
        if ($js = kirby()->option('panel.js')) {
            $asset = asset($js);

            if ($asset->exists() === true) {
                return $asset->url() . '?' . $asset->modified();
            }
        }

        return null;
    }

    /**
     * Creates data array for Fiber and the component
     *
     * @param array $data
     * @return array
     */
    public static function data(array $data = []): array
    {
        $kirby = kirby();

        // multilang setup check
        $multilang = $kirby->option('languages', false) !== false;

        // shared data for all requests
        $shared = [
            '$language' => function () use ($kirby, $multilang) {
                if ($multilang === true && $language = $kirby->language()) {
                    return [
                        'code'    => $language->code(),
                        'default' => $language->isDefault(),
                        'name'    => $language->name(),
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
            '$license' => (bool)$kirby->system()->license(),
            '$multilang' => $multilang,
            '$url' => Url::current(),
            '$user' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    return [
                        'email'       => $user->email(),
                        'id'          => $user->id(),
                        'language'    => $user->language(),
                        'role'        => $user->role()->id(),
                        'username'    => $user->username(),
                    ];
                }

                return null;
            }
        ];

        // merge with shared data
        return array_merge($shared, $data);
    }

    /**
     * Renders the error view with provided message
     *
     * @param string $message
     * @param int $code
     * @return \Kirby\Http\Response
     */
    public static function error(string $message, int $code = 404)
    {
        return [
            'code'      => $code,
            'component' => 'k-error-view',
            'props'     => [
                'error'  => $message,
                'layout' => static::hasAccess(kirby()->user()) ? 'inside' : 'outside'
            ],
            'title' => 'Error'
        ];
    }

    /**
     * Creates $fiber response array
     *
     * @param array $data
     * @return array
     */
    public static function fiber(array $data = []): array
    {
        // get all data for the request
        $data = static::data($data);

        // filter data, if it's a partial request
        $data = static::partial($data);

        // resolve lazy data entries
        return A::apply($data);
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
            throw new PermissionException(['key' => 'access.panel']);
        }

        // get all access permissions for the user role
        $permissions = $user->role()->permissions()->toArray()['access'];

        // check for general panel access
        if (($permissions['panel'] ?? false) !== true) {
            throw new PermissionException(['key' => 'access.panel']);
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
            throw new PermissionException(['key' => 'access.view']);
        }

        return true;
    }

    /**
     * Creates global data for the Panel.
     * This will be injected in the full Panel
     * view via the script tag. Global data
     * is only requested once on the first page load.
     * It can be loaded partially later if needed,
     * but is otherwise not included in Fiber calls.
     *
     * @return array
     */
    public static function globals(): array
    {
        $kirby = kirby();

        return [
            '$config' => function () use ($kirby) {
                return [
                    'debug'     => $kirby->option('debug', false),
                    'kirbytext' => $kirby->option('panel.kirbytext', true),
                    'search'    => [
                        'limit' => $kirby->option('panel.search.limit', 10),
                        'type'  => $kirby->option('panel.search.type', 'pages')
                    ],
                    'translation' => $kirby->option('panel.language', 'en'),
                ];
            },
            '$system' => function () use ($kirby) {
                $locales = [];

                foreach ($kirby->translations() as $translation) {
                    $locales[$translation->code()] = $translation->locale();
                }

                return [
                    'ascii'   => Str::$ascii,
                    'csrf'    => $kirby->option('api.csrf') ?? csrf(),
                    'isLocal' => $kirby->system()->isLocal(),
                    'locales' => $locales,
                    'slugs'   => Str::$language,
                    'title'   => $kirby->site()->title()->or('Kirby Panel')->toString()
                ];
            },
            '$translation' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    $translation = $kirby->translation($user->language());
                } else {
                    $translation = $kirby->translation($kirby->panelLanguage());
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
     * @throws \Kirby\Panel\Redirect
     * @return void
     * @codeCoverageIgnore
     */
    public static function go(?string $path = null): void
    {
        $slug = kirby()->option('panel.slug', 'panel');
        $url  = url($slug . '/' . trim($path, '/'));
        throw new Redirect($url, 302);
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
     * @return string
     */
    public static function icons(): string
    {
        return F::read(kirby()->root('kirby') . '/panel/dist/img/icons.svg');
    }

    /**
     * Checks for a Fiber request
     * via get parameters or headers
     *
     * @return bool
     */
    public static function isFiberRequest(): bool
    {
        $request = kirby()->request();

        if ($request->method() === 'GET') {
            return (bool)($request->get('_json') ?? $request->header('X-Fiber'));
        }

        return false;
    }

    /**
     * Returns a JSON response
     * for Fiber calls
     *
     * @param array $data
     * @return \Kirby\Http\Response
     */
    public static function json(array $data)
    {
        return Response::json($data, $data['$view']['code'] ?? 200, get('_pretty'), [
            'X-Fiber' => 'true'
        ]);
    }

    /**
     * Links all dist files in the media folder
     * and returns the link to the requested asset
     *
     * @return bool
     * @throws \Exception If Panel assets could not be moved to the public directory
     */
    public static function link(): bool
    {
        $kirby       = kirby();
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
     * @param array $data
     * @return array
     */
    public static function partial(array $data): array
    {
        // is it a partial request?
        $kirby          = kirby();
        $request        = $kirby->request();
        $requestInclude = $request->header('X-Fiber-Include') ?? get('_include');

        // split include string into an array of fields
        $include = Str::split($requestInclude, ',');

        // if a full request is made, return all data
        if (empty($include) === true) {
            return $data;
        }

        // otherwise filter data based on
        // dot notation, e.g. `$props.tab.columns`
        $partials = [];

        // check if globals are requested and need to be merged
        if (Str::contains($requestInclude, '$')) {
            $data = array_merge_recursive(static::globals(), $data);
        }

        // make sure the data is already resolved to make
        // nested data fetching work
        $data = A::apply($data);

        // build a new array with all requested data
        foreach ($include as $partial) {
            $partials[$partial] = A::get($data, $partial);
        }

        return A::nest($partials);
    }

    /**
     * Creates a Response object from the result of
     * a Panel route call
     *
     * @params mixed $result
     * @params array $options
     * @return \Kirby\Http\Response
     */
    public static function response($result, array $options = [])
    {
        // pass responses directly down to the Kirby router
        if (is_a($result, 'Kirby\Http\Response') === true) {
            return $result;
        }

        // interpret strings as errors
        if (is_string($result) === true) {
            $result = static::error($result);
        }

        // only expect arrays from here on
        if (is_array($result) === false) {
            $result = static::error('Invalid Panel response', 500);
        }

        return static::responseForView($result, $options);
    }

    /**
     * Renders the main panel view
     *
     * @param array $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function responseForView(array $data, array $options = [])
    {
        $kirby = kirby();
        $area  = $options['area']  ?? null;
        $areas = $options['areas'] ?? [];

        // create the view based on the current area
        $view = static::view($data, $area);

        // get $fiber response array
        $fiber = static::fiber([
            '$view'  => $view,
            '$areas' => array_map(function ($area) {
                // routes should not be included in the frontend object
                unset($area['routes']);
                return $area;
            }, $areas)
        ]);

        // if requested, send $fiber data as JSON
        if (static::isFiberRequest() === true) {
            return static::json($fiber);
        }

        // Full HTML response
        try {
            if (static::link() === true) {
                usleep(1);
                go($kirby->url('index') . '/' . $kirby->path());
            }
        } catch (Throwable $e) {
            die('The Panel assets cannot be installed properly. ' . $e->getMessage());
        }

        // get the uri object for the panel url
        $uri = new Uri($url = $kirby->url('panel'));

        // inject globals
        $globals = static::globals();
        $fiber   = array_merge_recursive(A::apply($globals), $fiber);
        $code    = $view['code'] ?? 200;

        // load the main Panel view template
        $body = Tpl::load($kirby->root('kirby') . '/views/panel.php', [
            'assets'   => static::assets(),
            'icons'    => static::icons(),
            'nonce'    => $kirby->nonce(),
            'fiber'    => $fiber,
            'panelUrl' => $uri->path()->toString(true) . '/',
        ]);

        return new Response($body, 'text/html', $code);
    }

    /**
     * Router for the Panel views
     *
     * @param string $path
     * @return \Kirby\Http\Response|false
     */
    public static function router(string $path = null)
    {
        $kirby = kirby();

        if ($kirby->option('panel') === false) {
            return null;
        }

        // set the translation for Panel UI before
        // gathering areas and routes, so that the
        // `t()` helper can already be used
        static::setTranslation();

        // set the language in multi-lang installations
        static::setLanguage();

        $areas  = static::areas();
        $routes = static::routes($areas);

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
            } catch (Redirect $e) {
                $result = Response::redirect($e->location(), $e->getCode());
            } catch (KirbyException $e) {
                $result = static::error($e->getMessage(), $e->getHttpCode());
            } catch (Throwable $e) {
                $result = static::error($e->getMessage(), 500);
            }

            return static::response($result, [
                'area'  => $area,
                'areas' => $areas
            ]);
        });
    }

    /**
     * Extract the routes from the given array
     * of active areas.
     *
     * @return array
     */
    public static function routes(array $areas): array
    {
        $kirby = kirby();

        // the browser incompatibility
        // warning is always needed
        $routes = [
            [
                'pattern' => 'browser',
                'auth'    => false,
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

        // if the Panel is already installed and/or the
        // user is authenticated, those areas won't be
        // included, which is why we add redirect routes
        // to main Panel view as fallbacks
        $routes[] = [
            'pattern' => [
                '/',
                'installation',
                'login',
            ],
            'action' => function () use ($kirby) {
                // if the last path has been stored in the
                // session, redirect the user to it
                // (used after successful login)
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
     * @return string|null
     */
    public static function setLanguage(): ?string
    {
        $kirby = kirby();

        // language switcher
        if ($kirby->options('languages')) {
            $session  = $kirby->session();
            $sessionLanguage = $session->get('panel.language', 'en');
            $language = get('language') ?? $sessionLanguage;

            // keep the language for the next visit
            if ($language !== $sessionLanguage) {
                $session->set('panel.language', $language);
            }

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
     * @return string
     */
    public static function setTranslation(): string
    {
        $kirby = kirby();

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
     * @param array $view
     * @param array $area
     * @return array
     */
    public static function view(array $view = [], ?array $area = null): array
    {
        $kirby = kirby();

        // merge view with area defaults
        $view = array_replace_recursive($area ?? [], $view);

        $view['breadcrumb'] = $view['breadcrumb'] ?? [];
        $view['path']       = Str::after($kirby->path(), '/');
        $view['props']      = $view['props'] ?? [];
        $view['search']     = $view['search'] ?? $kirby->option('panel.search.type', 'pages');

        // make sure that routes are gone
        unset($view['routes']);

        return $view;
    }
}
