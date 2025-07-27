<?php

namespace Kirby\Panel\Routes;

use Closure;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class DropdownRoutes extends Routes
{
	protected static string $prefix = 'dropdowns';
	protected static string $type = 'dropdown';

	public function params(Closure|array $params): array
	{
		$params = parent::params($params);

		// support old options handler
		if (isset($params['options']) === true) {
			$params['action'] ??= $params['options'];
		}

		// create from controller class
		if ($controller = $this->controller($params['action'] ?? null)) {
			$params['action'] = fn (...$args) => $controller(...$args)->options();
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
				action:  $params['action'] ?? fn () => 'The dropdown action handler is missing',
				method: 'GET|POST'
			);
		}

		return $routes;
	}
}
