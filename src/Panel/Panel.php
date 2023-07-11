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
	public static function area(string $id, array|string $area): array
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
		if ($system->isOk() === false || $system->isInstalled() === false) {
			return [
				'installation' => static::area('installation', $areas['installation']),
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

		$menu = $kirby->option('panel.menu', [
			'site',
			'languages',
			'users',
			'system',
		]);

		$result = [];

		// add the sorted areas
		foreach ($menu as $id) {
			if ($area = ($areas[$id] ?? null)) {
				$result[$id] = static::area($id, $area);
				unset($areas[$id]);
			}
		}

		// add the remaining areas
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
			return (bool)($request->get('_json') ?? $request->header('X-Fiber'));
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
			'X-Fiber' => 'true',
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
			'dropdown' => Dropdown::response($result, $options),
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
				$route = $kirby->apply('panel.route:before', compact('route', 'path', 'method'), 'route');

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

			return $kirby->apply('panel.route:after', compact('route', 'path', 'method', 'response'), 'response');
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
			'action' => fn () => Panel::go(Home::url()),
			'auth' => false
		];

		// catch all route
		$routes[] = [
			'pattern' => '(:all)',
			'action'  => fn () => 'The view could not be found'
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

		foreach ($dialogs as $key => $dialog) {
			// create the full pattern with dialogs prefix
			$pattern = 'dialogs/' . trim(($dialog['pattern'] ?? $key), '/');

			// load event
			$routes[] = [
				'pattern' => $pattern,
				'type'    => 'dialog',
				'area'    => $areaId,
				'action'  => $dialog['load'] ?? fn () => 'The load handler for your dialog is missing'
			];

			// submit event
			$routes[] = [
				'pattern' => $pattern,
				'type'    => 'dialog',
				'area'    => $areaId,
				'method'  => 'POST',
				'action'  => $dialog['submit'] ?? fn () => 'Your dialog does not define a submit handler'
			];
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

		foreach ($dropdowns as $name => $dropdown) {
			// Handle shortcuts for dropdowns. The name is the pattern
			// and options are defined in a Closure
			if ($dropdown instanceof Closure) {
				$dropdown = [
					'pattern' => $name,
					'action'  => $dropdown
				];
			}

			// create the full pattern with dropdowns prefix
			$pattern = 'dropdowns/' . trim(($dropdown['pattern'] ?? $name), '/');

			// load event
			$routes[] = [
				'pattern' => $pattern,
				'type'    => 'dropdown',
				'area'    => $areaId,
				'method'  => 'GET|POST',
				'action'  => $dropdown['options'] ?? $dropdown['action']
			];
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
					$request = App::instance()->request();

					return $params['query']($request->get('query'));
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
			$routes[] = $view;
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
	public static function url(string|null $url = null): string
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
			$url = CmsUrl::to($path);
		}

		return $url;
	}
}
