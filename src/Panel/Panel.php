<?php

namespace Kirby\Panel;

use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Response;
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
            if (is_a($area, 'Closure') === false) {
                throw new InvalidArgumentException(sprintf('Panel area "%s" must be defined as a Closure', $id));
            }

            $areas[$id] = static::area($id, (array)$area($kirby));
        }

        return $areas;
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
        if (($permissions['panel'] ?? true) !== true) {
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
     * Redirect to a Panel url
     *
     * @param string|null $path
     * @param int $code
     * @throws \Kirby\Panel\Redirect
     * @return void
     * @codeCoverageIgnore
     */
    public static function go(?string $path = null, int $code = 302): void
    {
        $slug = kirby()->option('panel.slug', 'panel');
        $url  = url($slug . '/' . trim($path, '/'));
        throw new Redirect($url, $code);
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
     * @param int $code
     * @return \Kirby\Http\Response
     */
    public static function json(array $data, int $code = 200)
    {
        return Response::json($data, $code, get('_pretty'), [
            'X-Fiber' => 'true'
        ]);
    }

    /**
     * Returns the referrer path if present
     *
     * @return string|null
     */
    public static function referrer(): ?string
    {
        $referrer = kirby()->request()->header('X-Fiber-Referrer') ?? get('_referrer');
        return '/' . trim($referrer, '/');
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

        // interpret missing/empty results as not found
        if ($result === null || $result === false) {
            $result = new NotFoundException('The data could not be found');

        // interpret strings as errors
        } elseif (is_string($result) === true) {
            $result = new Exception($result);
        }

        // handle different response types (view, dialog, ...)
        switch ($options['type'] ?? 'view') {
            case 'dialog':
                return Dialog::response($result, $options);
            case 'dropdown':
                return Dropdown::response($result, $options);
            case 'search':
                return Search::response($result, $options);
            case 'view':
                return View::response($result, $options);
        }
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
            $type   = $route->attributes()['type'] ?? 'view';
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
            } catch (Throwable $e) {
                $result = $e;
            }

            $response = static::response($result, [
                'area'  => $area,
                'areas' => $areas,
                'path'  => $path,
                'type'  => $type
            ]);

            return $kirby->apply('panel.route:after', compact('route', 'path', 'method', 'response'), 'response');
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
            $routes = array_merge(
                $routes,
                static::routesForViews($areaId, $area),
                static::routesForSearches($areaId, $area),
                static::routesForDialogs($areaId, $area),
                static::routesForDropdowns($areaId, $area),
            );
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
            'action'  => function () {
                return 'The view could not be found';
            }
        ];

        return $routes;
    }

    /**
     * Extract all routes from an area
     *
     * @param string $areaId
     * @param array $area
     * @return array
     */
    public static function routesForDialogs(string $areaId, array $area): array
    {
        $dialogs = $area['dialogs'] ?? [];
        $routes  = [];

        foreach ($dialogs as $pattern => $dialog) {

            // create the full pattern with dialogs prefix
            $pattern = 'dialogs/' . trim($pattern, '/');

            // load event
            $routes[] = [
                'pattern' => $pattern,
                'type'    => 'dialog',
                'area'    => $areaId,
                'action'  => $dialog['load'] ?? function () {
                    return false;
                },
            ];

            // submit event
            $routes[] = [
                'pattern' => $pattern,
                'type'    => 'dialog',
                'area'    => $areaId,
                'method'  => 'POST',
                'action'  => $dialog['submit'] ?? function () {
                    return false;
                }
            ];
        }

        return $routes;
    }

    /**
     * Extract all routes for dropdowns
     *
     * @param string $areaId
     * @param array $area
     * @return array
     */
    public static function routesForDropdowns(string $areaId, array $area): array
    {
        $dropdowns = $area['dropdowns'] ?? [];
        $routes    = [];

        foreach ($dropdowns as $pattern => $action) {

            // create the full pattern with dropdowns prefix
            $pattern = 'dropdowns/' . trim($pattern, '/');

            // load event
            $routes[] = [
                'pattern' => $pattern,
                'type'    => 'dropdown',
                'area'    => $areaId,
                'action'  => $action
            ];
        }

        return $routes;
    }

    /**
     * Extract all routes for searches
     *
     * @param string $areaId
     * @param array $area
     * @return array
     */
    public static function routesForSearches(string $areaId, array $area): array
    {
        $searches = $area['searches'] ?? [];
        $routes   = [];

        foreach ($searches as $name => $params) {

            // create the full routing pattern
            $pattern = 'search/' . $name;

            // load event
            $routes[] = [
                'pattern' => $pattern,
                'type'    => 'search',
                'area'    => $areaId,
                'action'  => function () use ($params) {
                    return $params['query'](get('query'));
                }
            ];
        }

        return $routes;
    }

    /**
     * Extract all views from an area
     *
     * @param string $areaId
     * @param array $area
     * @return array
     */
    public static function routesForViews(string $areaId, array $area): array
    {
        $views  = $area['views'] ?? [];
        $routes = [];

        foreach ($views as $view) {
            $view['area'] = $areaId;
            $view['type'] = 'view';
            $routes[] = $view;
        }

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
        if ($kirby->option('languages')) {
            $session         = $kirby->session();
            $sessionLanguage = $session->get('panel.language', 'en');
            $language        = get('language') ?? $sessionLanguage;

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
}
