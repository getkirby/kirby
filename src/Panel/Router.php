<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Http\Response;
use Kirby\Http\Router as BaseRouter;
use Kirby\Panel\Response\DialogResponse;
use Kirby\Panel\Response\DrawerResponse;
use Kirby\Panel\Response\DropdownResponse;
use Kirby\Panel\Response\JsonResponse;
use Kirby\Panel\Response\RequestResponse;
use Kirby\Panel\Response\SearchResponse;
use Kirby\Panel\Response\ViewResponse;
use Kirby\Panel\Response\ViewDocumentResponse;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
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
		$areas = $this->panel->areas()->toArray();

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

				$response = $this->response(
					data: $result,
					area: $area,
					areas: $areas,
					path: $path,
					type: $type
				);

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
	public function response(
		mixed $data,
		Area|null $area = null,
		array $areas = [],
		string|null $path = null,
		string $type = 'view',
	): Response {

		// handle different response types (view, dialog, ...)
		$response = match ($type) {
			'dialog'   => DialogResponse::from($data),
			'drawer'   => DrawerResponse::from($data),
			'dropdown' => DropdownResponse::from($data),
			'request'  => RequestResponse::from($data),
			'search'   => SearchResponse::from($data),
			default    => $this->view($data),
		};

		// pass HTTP responses through directly
		if ($response instanceof JsonResponse === false) {
			return $response;
		}

		$response->context(
			area: $area,
			areas: $areas,
			path: $path,
			query: $this->kirby->request()->query()->toArray(),
			referrer: $this->panel->referrer()
		);

		return $response;
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
		foreach ($areas as $area) {
			$routes = [
				...$routes,
				...$area->routes(),
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
			'action' => fn () => Panel::go($kirby->panel()->home()->url()),
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
	public static function routesForRequests(Area $area): array
	{
		$areaId = $area->id();
		$routes = $area->requests();

		foreach ($routes as $key => $route) {
			$routes[$key]['area'] = $areaId;
			$routes[$key]['type'] = 'request';
		}

		return $routes;
	}

	public function view(mixed $data): ViewResponse|ViewDocumentResponse|Response
	{
		// if requested, send $fiber data as JSON
		if (Panel::isFiberRequest() === true) {
			return ViewResponse::from($data);
		}

		// send a full HTML document otherwise
		return ViewDocumentResponse::from($data);
	}
}
