<?php

namespace Kirby\Cms;

/**
 * HasModels
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
trait HasModels
{
	/**
	 * Registry with all custom models
	 */
	public static array $models = [];

	/**
	 * Adds new models to the registry
	 * @internal
	 */
	public static function extendModels(array $models): array
	{
		return static::$models = [
			...static::$models,
			...array_change_key_case($models, CASE_LOWER)
		];
	}

	/**
	 * Creates a page model if it has been registered
	 * @internal
	 */
	public static function model(string $name, array $props = []): static
	{
		$name    = strtolower($name);
		$class   = static::$models[$name] ?? null;
		$class ??= static::$models['default'] ?? null;

		if ($class !== null) {
			$object = new $class($props);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($props);
	}
}
