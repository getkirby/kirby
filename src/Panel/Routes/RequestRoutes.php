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
class RequestRoutes extends Routes
{
	protected static string $prefix = '';
	protected static string $type = 'request';

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $params) {
			$params   = $this->params($params);
			$routes[] = $this->route(
				pattern: $params['pattern'],
				action:  $params['action'] ?? fn () => 'The request action handler is missing',
				method: $params['method'] ?? 'GET'
			);
		}

		return $routes;
	}
}
