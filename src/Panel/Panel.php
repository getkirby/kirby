<?php

namespace Kirby\Panel;

use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Cms\Url as CmsUrl;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Http\Router;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Panel\Response\DialogResponse;
use Kirby\Panel\Response\DrawerResponse;
use Kirby\Panel\Response\DropdownResponse;
use Kirby\Panel\Response\RequestResponse;
use Kirby\Panel\Response\SearchResponse;
use Kirby\Panel\Routes\DialogRoutes;
use Kirby\Panel\Routes\DrawerRoutes;
use Kirby\Panel\Routes\DropdownRoutes;
use Kirby\Panel\Routes\RequestRoutes;
use Kirby\Panel\Routes\SearchRoutes;
use Kirby\Panel\Routes\ViewRoutes;
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
	protected Access $access;
	protected Areas $areas;
	protected Home $home;

	public function __construct(
		protected App $kirby
	) {
	}

	/**
	 * Returns the Panel Access object
	 * @since 6.0.0
	 */
	public function access(): Access
	{
		return $this->access ??= new Access($this);
	}

	/**
	 * Collect all registered areas
	 */
	public function areas(): Areas
	{
		return $this->areas ??= new Areas($this);
	}

	/**
	 * Garbage collection which runs with a probability
	 * of 10% on each Panel request
	 *
	 * @since 5.0.0
	 * @codeCoverageIgnore
	 */
	protected static function garbage(): void
	{
		// run garbage collection with a chance of 10%;
		if (mt_rand(1, 10000) <= 0.1 * 10000) {
			// clean up leftover upload chunks
			Upload::cleanTmpDir();
		}
	}

	/**
	 * Redirect to a Panel url
	 *
	 * @throws \Kirby\Panel\Redirect
	 * @codeCoverageIgnore
	 */
	public static function go(string|null $url = null, int $code = 302): void
	{
		throw new Redirect(App::instance()->panel()->url($url), $code);
	}


	/**
	 * Returns the Panel home instance
	 * @since 6.0.0
	 */
	public function home(): Home
	{
		return $this->home ??= new Home($this);
	}

	/**
	 * Checks for a Panel request
	 * via get parameters or headers
	 */
	public static function isStateRequest(): bool
	{
		$request = App::instance()->request();

		if ($request->method() === 'GET') {
			return
				(bool)($request->get('_json') ??
				$request->header('X-Panel'));
		}

		return false;
	}

	/**
	 * Checks if the given URL is a Panel Url
	 * @since 6.0.0
	 */
	public function isPanelUrl(string $url): bool
	{
		return Str::startsWith($url, $this->kirby->url('panel'));
	}

	/**
	 * Returns a JSON response
	 * for State calls
	 */
	public static function json(array $data, int $code = 200): Response
	{
		$request = App::instance()->request();

		return Response::json($data, $code, $request->get('_pretty'), [
			'X-Panel'       => 'true',
			'Cache-Control' => 'no-store, private'
		]);
	}

	/**
	 * Returns the Kirby instance
	 * @since 6.0.0
	 */
	public function kirby(): App
	{
		return $this->kirby;
	}

	/**
	 * Checks for a multilanguage installation
	 */
	public function multilang(): bool
	{
		return $this->kirby->option('languages') || $this->kirby->multilang();
	}

	/**
	 * Returns the path after /panel/ which can then
	 * be used in the router or to find a matching view
	 * @since 6.0.0
	 */
	public function path(string $url): string|null
	{
		$after = Str::after($url, $this->kirby->url('panel'));
		return trim($after, '/');
	}

	/**
	 * Returns the referrer path if present
	 */
	public function referrer(): string
	{
		$request = $this->kirby->request();

		$referrer = $request->header('X-Panel-Referrer')
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
			$result = new NotFoundException(
				message: 'The data could not be found'
			);

		// interpret strings as errors
		} elseif (is_string($result) === true) {
			$result = new Exception($result);
		}

		// handle different response types (view, dialog, ...)
		return match ($options['type'] ?? null) {
			'dialog'   => DialogResponse::from($result),
			'drawer'   => DrawerResponse::from($result),
			'dropdown' => DropdownResponse::from($result),
			'request'  => RequestResponse::from($result),
			'search'   => SearchResponse::from($result),
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

		// run garbage collection
		static::garbage();

		// set the translation for Panel UI before
		// gathering areas and routes, so that the
		// `t()` helper can already be used
		static::setTranslation();

		// set the language in multi-lang installations
		static::setLanguage();

		$areas  = $kirby->panel()->areas()->toArray();
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
					compact('route', 'path', 'method')
				);

				// check for access before executing area routes
				if ($auth !== false) {
					$kirby->panel()->access()->area($areaId, throws: true);
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
		foreach ($areas as $area) {
			$view     = new ViewRoutes($area, $area['views'] ?? []);
			$search   = new SearchRoutes($area, $area['searches'] ?? []);
			$dialog   = new DialogRoutes($area, $area['dialogs'] ?? []);
			$drawer   = new DrawerRoutes($area, $area['drawers'] ?? []);
			$dropdown = new DropdownRoutes($area, $area['dropdowns'] ?? []);
			$request  = new RequestRoutes($area, $area['requests'] ?? []);

			$routes = [
				...$routes,
				...$view->toArray(),
				...$search->toArray(),
				...$dialog->toArray(),
				...$drawer->toArray(),
				...$dropdown->toArray(),
				...$request->toArray()
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
			'action' => fn () => $kirby->panel()->go(
				url: $kirby->panel()->home()->url()
			),
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
	 * Set the current language in multi-lang
	 * installations based on the session or the
	 * query language query parameter
	 */
	public static function setLanguage(): string|null
	{
		$kirby = App::instance();

		// language switcher
		if ($kirby->panel()->multilang() === true) {
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
	public function url(string|null $url = null, array $options = []): string
	{
		// only touch relative paths
		if (Url::isAbsolute($url) === false) {
			$slug  = $this->kirby->option('panel.slug', 'panel');
			$path  = trim($url ?? '', '/');

			$baseUri  = new Uri($this->kirby->url());
			$basePath = trim($baseUri->path()->toString(), '/');

			// removes base path if relative path contains it
			if (
				empty($basePath) === false &&
				Str::startsWith($path, $basePath) === true
			) {
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
