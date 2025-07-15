<?php

namespace Kirby\Panel\Routes;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class DialogRoutes extends Routes
{
	protected static string $prefix = 'dialogs';
	protected static string $type = 'dialog';

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $name => $params) {
			$pattern = $this->pattern($params['pattern'] ?? $name);

			// create load/submit events from controller class
			if ($controller = $params['controller'] ?? null) {
				if (is_string($controller) === true) {
					if (method_exists($controller, 'for') === true) {
						$controller = $controller::for(...);
					} else {
						$controller = fn (...$args) => new $controller(...$args);
					}
				}

				$params['load']   ??= fn (...$args) => $controller(...$args)->load();
				$params['submit'] ??= fn (...$args) => $controller(...$args)->submit();
			}

			// load handler
			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['load'] ?? fn () => 'The load handler is missing'
			);

			// submit handler
			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['submit'] ?? fn () => 'The submit handler is missing',
				method:  'POST',
			);
		}

		return $routes;
	}
}
