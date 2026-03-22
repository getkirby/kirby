<?php

namespace Kirby\Panel\Routes;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class DropdownRoutes extends Routes
{
	protected static string $prefix = 'dropdowns';
	protected static string $type = 'dropdown';

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $name => $params) {
			$params  = $this->params($params, 'options');
			$pattern = $this->pattern($params['pattern'] ?? $name);

			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['load'] ?? fn () => 'The dropdown action handler is missing',
				method: 'GET|POST'
			);
		}

		return $routes;
	}
}
