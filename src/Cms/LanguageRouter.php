<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

/**
 * The language router is used internally
 * to handle language-specific (scoped) routes
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageRouter
{
	protected Router $router;

	/**
	 * Creates a new language router instance
	 * for the given language
	 */
	public function __construct(
		protected Language $language
	) {
	}

	/**
	 * Fetches all scoped routes for the
	 * current language from the Kirby instance
	 *
	 * @throws \Kirby\Exception\NotFoundException
	 */
	public function routes(): array
	{
		$language = $this->language;
		$kirby    = $language->kirby();
		$routes   = $kirby->routes();

		// only keep the scoped language routes
		$routes = array_values(array_filter($routes, function ($route) use ($language) {
			// no language scope
			if (empty($route['language']) === true) {
				return false;
			}

			// wildcard
			if ($route['language'] === '*') {
				return true;
			}

			// get all applicable languages
			$languages = Str::split(strtolower($route['language']), '|');

			// validate the language
			return in_array($language->code(), $languages) === true;
		}));

		// add the page-scope if necessary
		foreach ($routes as $index => $route) {
			if ($pageId = ($route['page'] ?? null)) {
				if ($page = $kirby->page($pageId)) {
					// convert string patterns to arrays
					$patterns = A::wrap($route['pattern']);

					// prefix all patterns with the page slug
					$patterns = A::map(
						$patterns,
						fn ($pattern) => $page->uri($language) . '/' . $pattern
					);

					// re-inject the pattern and the full page object
					$routes[$index]['pattern'] = $patterns;
					$routes[$index]['page']    = $page;
				} else {
					throw new NotFoundException('The page "' . $pageId . '" does not exist');
				}
			}
		}

		// Language-specific UUID URLs
		$routes[] = [
			'pattern' => '@/(page|file)/(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $languageCode, string $type, string $id) use ($kirby, $language) {
				// try to resolve to model, but only from UUID cache;
				// this ensures that only existing UUIDs can be queried
				// and attackers can't force Kirby to go through the whole
				// site index with a non-existing UUID
				if ($model = Uuid::for($type . '://' . $id)?->model(true)) {
					return $kirby
						->response()
						->redirect($model->url($language->code()));
				}

				// render the error page
				return false;
			}
		];

		return $routes;
	}

	/**
	 * Wrapper around the Router::call method
	 * that injects the Language instance and
	 * if needed also the Page as arguments.
	 */
	public function call(string|null $path = null): mixed
	{
		$language       = $this->language;
		$kirby          = $language->kirby();
		$this->router ??= new Router($this->routes());

		try {
			return $this->router->call($path, $kirby->request()->method(), function ($route) use ($kirby, $language) {
				$kirby->setCurrentTranslation($language);
				$kirby->setCurrentLanguage($language);

				if ($page = $route->page()) {
					return $route->action()->call(
						$route,
						$language,
						$page,
						...$route->arguments()
					);
				}

				return $route->action()->call(
					$route,
					$language,
					...$route->arguments()
				);
			});
		} catch (Exception) {
			return $kirby->resolve($path, $language->code());
		}
	}
}
