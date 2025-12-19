<?php

namespace Kirby\Blueprint;

class OptionsProps
{
	/**
	 * Normalizes blueprint options. This must be used in the
	 * constructor of an extended class, if you want to make use of it.
	 */
	public static function normalize(
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
