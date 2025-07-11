<?php

namespace Kirby\Panel\Routes;

class DialogRoutes extends Routes
{
	protected static string $prefix = 'dialogs';
	protected static string $type = 'dialog';

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $name => $params) {
			$pattern = $this->pattern($params['pattern'] ?? $name);

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
