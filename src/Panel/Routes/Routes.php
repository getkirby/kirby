<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Panel\Area;

abstract class Routes
{
	protected static string $prefix = '/';
	protected static string $type = 'route';

	public function __construct(
		protected Area $area,
		protected array $routes,
	) {
	}

	/**
	 * Builds the full routing pattern with the
	 * given prefix
	 */
	public function pattern(
		string $pattern
	) {
		return trim(static::$prefix . '/' . $pattern, '/');
	}

	/**
	 * Creates a single route and injects
	 * type and a area id
	 */
	public function route(
		array|string $pattern,
		Closure $action,
		string $method = 'GET',
		bool $auth = true,
	) {
		return [
			'auth'    => $auth,
			'pattern' => $pattern,
			'type'    => static::$type,
			'area'    => $this->area->id(),
			'method'  => $method,
			'action'  => $action
		];
	}

	abstract public function toArray(): array;
}
