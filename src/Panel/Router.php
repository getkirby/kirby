<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Http\Router as BaseRouter;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Router
{
	protected App $kirby;

	public function __construct(
		protected Panel $panel
	) {
		$this->kirby = App::instance();
	}

	public function execute(string|null $path = null): Response|null
	{
		// run garbage collection
		$this->garbage();

		// collect areas
		$areas  = $this->panel->areas()->toArray();

		// create a micro-router for the Panel
		return BaseRouter::execute(
			path: $path,
			method: $method = $this->kirby->request()->method(),
			routes: static::routes($areas),
			callback: function ($route) use ($areas, $method, $path) {
				// route needs authentication?
				$auth   = $route->attributes()['auth'] ?? true;
				$areaId = $route->attributes()['area'] ?? null;
				$type   = $route->attributes()['type'] ?? 'view';
				$area   = $areas[$areaId] ?? null;

				// call the route action to check the result
				try {
					// trigger hook
					$route = $this->kirby->apply(
						'panel.route:before',
						compact('route', 'path', 'method'),
						'route'
					);

					// check for access before executing area routes
					if ($auth !== false) {
						Access::has(
							$this->kirby->user(),
							$areaId,
							throws: true
						);
					}

					$result = $route->action()->call($route, ...$route->arguments());
				} catch (Throwable $e) {
					$result = $e;
				}

				$response = $this->response($result, [
					'area'  => $area,
					'areas' => $areas,
					'path'  => $path,
					'type'  => $type
				]);

				return $this->kirby->apply(
					'panel.route:after',
					compact('route', 'path', 'method', 'response'),
					'response'
				);
			}
		);
	}

	/**
	 * Garbage collection which runs with a probability
	 * of 10% on each Panel request
	 *
	 * @since 5.0.0
	 * @codeCoverageIgnore
	 */
	protected function garbage(): void
	{
		// run garbage collection with a chance of 10%;
		if (mt_rand(1, 10000) <= 0.1 * 10000) {
			// clean up leftover upload chunks
			Upload::cleanTmpDir();
		}
	}

	/**
	 * Creates a Response object from the result of
	 * a Panel route call
	 */
	public function response($result, array $options = []): Response
	{
		// pass responses directly down to the Kirby router
		if ($result instanceof Response) {
			return $result;
		}

		// interpret missing/empty results as not found
		if ($result === null || $result === false) {
			$result = new NotFoundException(
				message: 'The data could not be found'
			);

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
		foreach ($areas as $id => $area) {
			$routes = [
				...$routes,
				...static::routesForViews($id, $area),
				...static::routesForSearches($id, $area),
				...static::routesForDialogs($id, $area),
				...static::routesForDrawers($id, $area),
				...static::routesForDropdowns($id, $area),
				...static::routesForRequests($id, $area),
			];
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
			$routes = [
				...$routes,
				...Dialog::routes(
					id: $dialogId,
					areaId: $areaId,
					prefix: 'dialogs',
					options: $dialog
				)
			];
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
			$routes = [
				...$routes,
				...Drawer::routes(
					id: $drawerId,
					areaId: $areaId,
					prefix: 'drawers',
					options: $drawer
				)
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

		foreach ($dropdowns as $dropdownId => $dropdown) {
			$routes = [
				...$routes,
				...Dropdown::routes(
					id: $dropdownId,
					areaId: $areaId,
					prefix: 'dropdowns',
					options: $dropdown
				)
			];
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
}
