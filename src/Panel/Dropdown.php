<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Http\Response;

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
class Dropdown extends Json
{
	protected static string $key = '$dropdown';

	/**
	 * Renders dropdowns
	 */
	public static function response($data, array $options = []): Response
	{
		if (is_array($data) === true) {
			$data = [
				'options' => array_values($data)
			];
		}

		return parent::response($data, $options);
	}

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
		$type    = str_replace('$', '', static::$key);

		return [
			// load event
			[
				'pattern' => $pattern,
				'type'    => $type,
				'area'    => $areaId,
				'method'  => 'GET|POST',
				'action'  => $options['options'] ?? $options['action']
			]
		];
	}
}
