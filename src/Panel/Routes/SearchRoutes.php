<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Cms\App;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SearchRoutes extends Routes
{
	protected static string $prefix = 'search';
	protected static string $type = 'search';

	/**
	 * Wraps the given query action with additional
	 * controller code to fetch arguments from the request
	 */
	public function action(
		Closure $action
	): Closure {
		return function () use ($action) {
			$kirby   = App::instance();
			$request = $kirby->request();
			$query   = $request->get('query');
			$limit   = (int)$request->get('limit', $kirby->option('panel.search.limit', 10));
			$page    = (int)$request->get('page', 1);

			return $action($query, $limit, $page);
		};
	}

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $name => $params) {
			$routes[] = $this->route(
				pattern: $this->pattern($name),
				action:  $this->action($params['query'])
			);
		}

		return $routes;
	}
}
