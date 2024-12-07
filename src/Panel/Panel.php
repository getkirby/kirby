<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Url as CmsUrl;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Response;
use Kirby\Http\Router;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The Panel class is only responsible to create
 * a working panel view with all the right URLs
 * and other panel options. The view template is
 * located in `kirby/views/panel.php`
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Panel
{
	/**
	 * Normalize a panel area
	 */
	public static function area(string $id, array $area): array
	{
		$area['id']                = $id;
		$area['label']           ??= $id;
		$area['breadcrumb']      ??= [];
		$area['breadcrumbLabel'] ??= $area['label'];
		$area['title']             = $area['label'];
		$area['menu']            ??= false;
		$area['link']            ??= $id;
		$area['search']          ??= null;

		return $area;
	}

	/**
	 * Collect all registered areas
	 */
	public static function areas(): array
	{
		$kirby  = App::instance();
		$system = $kirby->system();
		$user   = $kirby->user();
		$areas  = $kirby->load()->areas();

		// the system is not ready
		if (
			$system->isOk() === false ||
			$system->isInstalled() === false
		) {
			return [
				'installation' => static::area(
					'installation',
					$areas['installation']
				),
			];
		}

		// not yet authenticated
		if (!$user) {
			return [
				'logout' => static::area('logout', $areas['logout']),
				// login area last because it defines a fallback route
				'login'  => static::area('login', $areas['login']),
			];
		}

		unset($areas['installation'], $areas['login']);

		// Disable the language area for single-language installations
		// This does not check for installed languages. Otherwise you'd
		// not be able to add the first language through the view
		if (!$kirby->option('languages')) {
			unset($areas['languages']);
		}

		$result = [];

		foreach ($areas as $id => $area) {
			$result[$id] = static::area($id, $area);
		}

		return $result;
	}

	/**
	 * Check for access permissions
	 */
	public static function firewall(
		User|null $user = null,
		string|null $areaId = null
	): bool {
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
	 * @throws \Kirby\Panel\Redirect
	 * @codeCoverageIgnore
	 */
	public static function go(string|null $url = null, int $code = 302): void
	{
		throw new Redirect(static::url($url), $code);
	}

	/**
	 * Check if the given user has access to the panel
	 * or to a given area
	 */
	public static function hasAccess(
		User|null $user = null,
		string|null $area = null
	): bool {
		try {
			static::firewall($user, $area);
			return true;
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Checks for a Fiber request
	 * via get parameters or headers
	 */
	public static function isFiberRequest(): bool
	{
		$request = App::instance()->request();

		if ($request->method() === 'GET') {
			return
				(bool)($request->get('_json') ??
				$request->header('X-Fiber'));
		}

		return false;
	}

	/**
	 * Returns a JSON response
	 * for Fiber calls
	 */
	public static function json(array $data, int $code = 200): Response
	{
		$request = App::instance()->request();

		return Response::json($data, $code, $request->get('_pretty'), [
			'X-Fiber'       => 'true',
			'Cache-Control' => 'no-store, private'
		]);
	}

	/**
	 * Checks for a multilanguage installation
	 */
	public static function multilang(): bool
	{
		// multilang setup check
		$kirby = App::instance();
		return $kirby->option('languages') || $kirby->multilang();
	}

	/**
	 * Returns the referrer path if present
	 */
	public static function referrer(): string
	{
		$request = App::instance()->request();

		$referrer = $request->header('X-Fiber-Referrer')
				 ?? $request->get('_referrer')
				 ?? '';

		return '/' . trim($referrer, '/');
	}

	/**
	 * Creates a Response object from the result of
	 * a Panel route call
	 */
	public static function response($result, array $options = []): Response
	{
		// pass responses directly down to the Kirby router
		if ($result instanceof Response) {
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
		return match ($options['type'] ?? null) {
			'dialog'   => Dialog::response($result, $options),
			'drawer'   => Drawer::response($result, $options),
			'dropdown' => Dropdown::response($result, $options),
			'request'  => Request::response($result, $options),
			'search'   => Search::response($result, $options),
			default    => View::response($result, $options)
		};
	}

	/**
	 * Router for the Panel views
	 */
	public static function router(string|null $path = null): Response|null
	{
		$kirby = App::instance();

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
		return Router::execute($path, $method = $kirby->request()->method(), $routes, function ($route) use ($areas, $kirby, $method, $path) {
			// route needs authentication?
			$auth   = $route->attributes()['auth'] ?? true;
			$areaId = $route->attributes()['area'] ?? null;
			$type   = $route->attributes()['type'] ?? 'view';
			$area   = $areas[$areaId] ?? null;

			// call the route action to check the result
			try {
				// trigger hook
				$route = $kirby->apply(
					'panel.route:before',
					compact('route', 'path', 'method'),
					'route'
				);

				// check for access before executing area routes
				if ($auth !== false) {
					static::firewall($kirby->user(), $areaId);
				}

				$result = $route->action()->call($route, ...$route->arguments());
			} catch (Throwable $e) {
				$result = $e;
			}

			$response = static::response($result, [
				'area'  => $area,
				'areas' => $areas,
				'path'  => $path,
				'type'  => $type
			]);

			return $kirby->apply(
				'panel.route:after',
				compact('route', 'path', 'method', 'response'),
				'response'
			);
		});
	}

	/**
	 * Extract the routes from the given array
	 * of active areas.
	 */
	public static function routes(array $areas): array
	{
		$kirby = App::instance();

		// the browser incompatibility
		// warning is always needed
		$routes = [
			[
				'pattern' => 'browser',
				'auth'    => false,
				'action'  => fn () => new Response(
					Tpl::load($kirby->root('kirby') . '/views/browser.php')
				),
			]
		];

		// register all routes from areas
		foreach ($areas as $areaId => $area) {
			$routes = array_merge(
				$routes,
				static::routesForViews($areaId, $area),
				static::routesForSearches($areaId, $area),
				static::routesForDialogs($areaId, $area),
				static::routesForDrawers($areaId, $area),
				static::routesForDropdowns($areaId, $area),
				static::routesForRequests($areaId, $area),
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
			'action' => fn () => Panel::go(Home::url()),
			'auth' => false
		];

		// catch all route
		$routes[] = [
			'pattern' => '(:all)',
			'action'  => fn (string $pattern) => 'Could not find Panel view for route: ' . $pattern
		];

		return $routes;
	}

	/**
	 * Extract all routes from an area
	 */
	public static function routesForDialogs(string $areaId, array $area): array
	{
		$dialogs = $area['dialogs'] ?? [];
		$routes  = [];

		foreach ($dialogs as $dialogId => $dialog) {
			$routes = array_merge($routes, Dialog::routes(
				id: $dialogId,
				areaId: $areaId,
				prefix: 'dialogs',
				options: $dialog
			));
		}

		return $routes;
	}

	/**
	 * Extract all routes from an area
	 */
	public static function routesForDrawers(string $areaId, array $area): array
	{
		$drawers = $area['drawers'] ?? [];
		$routes  = [];

		foreach ($drawers as $drawerId => $drawer) {
			$routes = array_merge($routes, Drawer::routes(
				id: $drawerId,
				areaId: $areaId,
				prefix: 'drawers',
				options: $drawer
			));
		}

		return $routes;
	}

	/**
	 * Extract all routes for dropdowns
	 */
	public static function routesForDropdowns(string $areaId, array $area): array
	{
		$dropdowns = $area['dropdowns'] ?? [];
		$routes    = [];

		foreach ($dropdowns as $dropdownId => $dropdown) {
			$routes = array_merge($routes, Dropdown::routes(
				id: $dropdownId,
				areaId: $areaId,
				prefix: 'dropdowns',
				options: $dropdown
			));
		}

		return $routes;
	}

	/**
	 * Extract all routes from an area
	 */
	public static function routesForRequests(string $areaId, array $area): array
	{
		$routes = $area['requests'] ?? [];

		foreach ($routes as $key => $route) {
			$routes[$key]['area'] = $areaId;
			$routes[$key]['type'] = 'request';
		}

		return $routes;
	}

	/**
	 * Extract all routes for searches
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
					$kirby   = App::instance();
					$request = $kirby->request();
					$query   = $request->get('query');
					$limit   = (int)$request->get('limit', $kirby->option('panel.search.limit', 10));
					$page    = (int)$request->get('page', 1);

					return $params['query']($query, $limit, $page);
				}
			];
		}

		return $routes;
	}

	/**
	 * Extract all views from an area
	 */
	public static function routesForViews(string $areaId, array $area): array
	{
		$views  = $area['views'] ?? [];
		$routes = [];

		foreach ($views as $view) {
			$view['area'] = $areaId;
			$view['type'] = 'view';

			$when = $view['when'] ?? null;
			unset($view['when']);

			// enable the route by default, but if there is a
			// when condition closure, it must return `true`
			if (
				$when instanceof Closure === false ||
				$when($view, $area) === true
			) {
				$routes[] = $view;
			}
		}

		return $routes;
	}

	/**
	 * Set the current language in multi-lang
	 * installations based on the session or the
	 * query language query parameter
	 */
	public static function setLanguage(): string|null
	{
		$kirby = App::instance();

		// language switcher
		if (static::multilang()) {
			$fallback = 'en';

			if ($defaultLanguage = $kirby->defaultLanguage()) {
				$fallback = $defaultLanguage->code();
			}

			$session         = $kirby->session();
			$sessionLanguage = $session->get('panel.language', $fallback);
			$language        = $kirby->request()->get('language') ?? $sessionLanguage;

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
	 */
	public static function setTranslation(): string
	{
		$kirby = App::instance();

		// use the user language for the default translation or
		// fall back to the language from the config
		$translation = $kirby->user()?->language() ??
						$kirby->panelLanguage();

		$kirby->setCurrentTranslation($translation);

		return $translation;
	}

	/**
	 * Creates an absolute Panel URL
	 * independent of the Panel slug config
	 */
	public static function url(string|null $url = null, array $options = []): string
	{
		// only touch relative paths
		if (Url::isAbsolute($url) === false) {
			$kirby = App::instance();
			$slug  = $kirby->option('panel.slug', 'panel');
			$path  = trim($url, '/');

			$baseUri  = new Uri($kirby->url());
			$basePath = trim($baseUri->path()->toString(), '/');

			// removes base path if relative path contains it
			if (empty($basePath) === false && Str::startsWith($path, $basePath) === true) {
				$path = Str::after($path, $basePath);
			}
			// add the panel slug prefix if it it's not
			// included in the path yet
			elseif (Str::startsWith($path, $slug . '/') === false) {
				$path = $slug . '/' . $path;
			}

			// create an absolute URL
			$url = CmsUrl::to($path, $options);
		}

		return $url;
	}
}
