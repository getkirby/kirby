<?php

namespace Kirby\Panel;

use Closure;

/**
 * The Dropdown response class handles Fiber
 * requests to render the JSON object for
 * dropdown menus
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Dropdown
{
	/**
	 * Routes for the dropdown
	 */
	public static function routes(
		string $id,
		string $areaId,
		string $prefix = '',
		Closure|array $options = []
	): array {
		// Handle shortcuts for dropdowns. The name is the pattern
		// and options are defined in a Closure
		if ($options instanceof Closure) {
			$options = [
				'pattern' => $id,
				'action'  => $options
			];
		}

		// create the full pattern with dialogs prefix
		$pattern = trim($prefix . '/' . ($options['pattern'] ?? $id), '/');

		return [
			// load event
			[
				'pattern' => $pattern,
				'type'    => 'dropdown',
				'area'    => $areaId,
				'method'  => 'GET|POST',
				'action'  => $options['options'] ?? $options['action']
			]
		];
	}
}
