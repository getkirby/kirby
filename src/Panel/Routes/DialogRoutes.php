<?php

namespace Kirby\Panel\Routes;

/**
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
			$params  = $this->params($params);
			$pattern = $this->pattern($params['pattern'] ?? $name);

			// load handler
			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['load'] ?? fn () => 'The load handler is missing'
			);

			// submit handler
			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['submit'] ?? fn () => true,
				method:  'POST',
			);
		}

		return $routes;
	}
}
