<?php

namespace Kirby\Panel\Routes;

use Closure;

class DropdownRoutes extends Routes
{
	protected static string $prefix = 'dropdowns';
	protected static string $type = 'dropdown';

	public function params(Closure|array $params): array
	{
		// support direct handler
		if ($params instanceof Closure) {
			$params = [
				'action' => $params
			];
		}

		// support old options handler
		if (isset($params['options']) === true) {
			$params['action'] = $params['options'];
		}

		return $params;
	}

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $name => $params) {
			$params  = $this->params($params);
			$pattern = $this->pattern($params['pattern'] ?? $name);

			$routes[] = $this->route(
				pattern: $pattern,
				action:  $params['action'],
				method: 'GET|POST'
			);
		}

		return $routes;
	}
}
