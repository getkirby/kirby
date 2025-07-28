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
class ViewRoutes extends Routes
{
	protected static string $prefix = '';
	protected static string $type = 'view';

	public function isAccessible(array $view): bool
	{
		$when = $view['when'] ?? null;

		if ($when === null) {
			return true;
		}

		unset($view['when']);

		// enable the route by default, but if there is a
		// when condition closure, it must return `true`
		return $when($view, $this->area) === true;
	}

	public function toArray(): array
	{
		$routes = [];

		foreach ($this->routes as $params) {
			if ($this->isAccessible($params) === false) {
				continue;
			}

			$routes[] = [
				...$params,
				'area' => $this->area->id(),
				'type' => 'view'
			];
		}

		return $routes;
	}
}
