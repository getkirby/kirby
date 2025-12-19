<?php

namespace Kirby\Blueprint;

/**
 * Helper class to normalize blueprint options
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Options
{
	/**
	 * Normalizes blueprint options. This must be used in the
	 * constructor of an extended class, if you want to make use of it.
	 */
	public static function normalizeOptionsProps(
		array|string|bool|null $options,
		array $defaults,
		array $aliases = []
	) {
		// return defaults when options are not defined or set to true
		if ($options === true) {
			return $defaults;
		}

		// set all options to false
		if ($options === false) {
			return array_map(fn () => false, $defaults);
		}

		// extend options if possible
		$options = Blueprint::extend($options);

		foreach ($options as $key => $value) {
			$alias = $aliases[$key] ?? null;

			if ($alias !== null) {
				$options[$alias] ??= $value;
				unset($options[$key]);
			}
		}

		return [...$defaults, ...$options];
	}
}
