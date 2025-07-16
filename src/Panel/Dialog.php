<?php

namespace Kirby\Panel;

use Kirby\Http\Response;

/**
 * The Dialog response class handles Fiber
 * requests to render the JSON object for
 * Panel dialogs and creates the routes
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Dialog extends Json
{
	protected static string $key = '$dialog';

	/**
	 * Renders dialogs
	 */
	public static function response($data, array $options = []): Response
	{
		// interpret true as success
		if ($data === true) {
			$data = [
				'code' => 200
			];
		}

		return parent::response($data, $options);
	}

	/**
	 * Builds the routes for a dialog
	 */
	public static function routes(
		string $id,
		string $areaId,
		string $prefix = '',
		array $options = []
	) {
		$routes = [];

		// create the full pattern with dialogs prefix
		$pattern = trim($prefix . '/' . ($options['pattern'] ?? $id), '/');
		$type    = str_replace('$', '', static::$key);

		// create load/submit events from controller class
		if ($controller = $options['controller'] ?? null) {
			if (is_string($controller) === true) {
				if (method_exists($controller, 'for') === true) {
					$controller = $controller::for(...);
				} else {
					$controller = fn (...$args) => new $controller(...$args);
				}
			}

			$options['load']   ??= fn (...$args) => $controller(...$args)->load();
			$options['submit'] ??= fn (...$args) => $controller(...$args)->submit();
		}

		// load event
		$routes[] = [
			'pattern' => $pattern,
			'type'    => $type,
			'area'    => $areaId,
			'action'  => $options['load'] ?? fn () => 'The load handler is missing'
		];

		// submit event
		$routes[] = [
			'pattern' => $pattern,
			'type'    => $type,
			'area'    => $areaId,
			'method'  => 'POST',
			'action'  => $options['submit'] ?? fn () => 'The submit handler is missing'
		];

		return $routes;
	}
}
