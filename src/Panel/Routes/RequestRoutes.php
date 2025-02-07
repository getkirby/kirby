<?php

namespace Kirby\Panel\Routes;

class RequestRoutes extends Routes
{
	protected static string $prefix = '';
	protected static string $type = 'request';

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $params) {
			$routes[] = $this->route(
				pattern: $params['pattern'],
				action:  $params['action'],
				method: $params['method'] ?? 'GET'
			);
		}

		return $routes;
	}
}
