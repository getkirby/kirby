<?php

namespace Kirby\Panel;

use Kirby\Api\Upload;
use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Http\Router as BaseRouter;
use Kirby\Panel\Response\DialogResponse;
use Kirby\Panel\Response\DrawerResponse;
use Kirby\Panel\Response\DropdownResponse;
use Kirby\Panel\Response\JsonResponse;
use Kirby\Panel\Response\RequestResponse;
use Kirby\Panel\Response\SearchResponse;
use Kirby\Panel\Response\ViewDocumentResponse;
use Kirby\Panel\Response\ViewResponse;
use Kirby\Panel\Response\ViewSubmitResponse;
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
		$this->kirby = $this->panel->kirby();
	}

	public function call(string|null $path = null): Response|null
	{
		// Set the translation for Panel UI before
		// gathering areas and routes, so that the
		// `t()` helper can already be used
		$this->setTranslation();

		// Set the content language in multi-lang installations
		$this->setLanguage();

		// run garbage collection
		$this->garbage();

		// collect areas
		$areas = $this->panel->areas();

		// create a micro-router for the Panel
		return BaseRouter::execute(
			path: $path,
			method: $method = $this->kirby->request()->method(),
			routes: $this->routes($areas),
			callback: function ($route) use ($areas, $method, $path) {
				// route needs authentication?
				$auth   = $route->attributes()['auth'] ?? true;
				$areaId = $route->attributes()['area'] ?? null;
				$type   = $route->attributes()['type'] ?? 'view';
				$area   = $areaId ? $areas->get($areaId) : null;

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
						$this->panel->access()->area($areaId, throws: true);
					}

					$result = $route->action()->call($route, ...$route->arguments());
				} catch (Throwable $e) {
					$result = $e;
				}

				$response = $this->response(
					data: $result,
					area: $area,
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
		string|null $path = null,
		string $type = 'view'
	): Response {
		// handle different response types (view, dialog, ...)
		$response = match ($type) {
			'dialog'   => DialogResponse::from($data),
			'drawer'   => DrawerResponse::from($data),
			'dropdown' => DropdownResponse::from($data),
			'request'  => RequestResponse::from($data),
			'search'   => SearchResponse::from($data),
			default    => $this->view($data)
		};

		// pass HTTP responses through directly
		if ($response instanceof JsonResponse === false) {
			return $response;
		}

		$response->context(
			area: $area,
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
	public function routes(Areas|null $areas = null): array
	{
		$kirby   = $this->kirby;
		$panel   = $this->panel;
		$areas ??= $panel->areas();

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
			$routes = [...$routes, ...$area->routes()];
		}

		// if the Panel is already installed and/or the
		// user is authenticated, those areas won't be
		// included, which is why we add redirect routes
		// to main Panel view as fallbacks
		$routes[] = [
			'pattern' => ['/', 'installation', 'login'],
			'action'  => fn () => $panel->go($panel->home()->url()),
			'auth'    => false
		];

		// catch all route
		$routes[] = [
			'pattern' => '(:all)',
			'action'  => fn (string $pattern) => throw new NotFoundException('Could not find Panel route: ' . $pattern)
		];

		return $routes;
	}

	/**
	 * Set the current language in multi-lang
	 * installations based on the session or the
	 * query language query parameter
	 */
	public function setLanguage(): string|null
	{
		// language switcher
		if ($this->panel->multilang() === true) {
			$fallback = 'en';

			if ($defaultLanguage = $this->kirby->defaultLanguage()) {
				$fallback = $defaultLanguage->code();
			}

			$session         = $this->kirby->session();
			$sessionLanguage = $session->get('panel.language', $fallback);
			$language        = $this->kirby->request()->get('language') ?? $sessionLanguage;

			// keep the language for the next visit
			if ($language !== $sessionLanguage) {
				$session->set('panel.language', $language);
			}

			// activate the current language in Kirby
			$this->kirby->setCurrentLanguage($language);

			return $language;
		}

		return null;
	}

	/**
	 * Set the currently active Panel translation
	 * based on the current user or config
	 */
	public function setTranslation(): string
	{
		// use the user language for the default translation or
		// fall back to the language from the config
		$translation = $this->kirby->user()?->language() ??
						$this->kirby->panelLanguage();

		$this->kirby->setCurrentTranslation($translation);

		return $translation;
	}

	public function view(mixed $data): ViewResponse|ViewDocumentResponse|ViewSubmitResponse|Response
	{
		if ($this->kirby->request()->method() === 'POST') {
			return ViewSubmitResponse::from($data);
		}

		// if requested, send state data as JSON
		if ($this->panel->isStateRequest() === true) {
			return ViewResponse::from($data);
		}

		// send a full HTML document otherwise
		return ViewDocumentResponse::from($data);
	}
}
