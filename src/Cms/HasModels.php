<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Str;

/**
 * HasModels
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasModels
{
	/**
	 * Registry with all custom models
	 */
	public static array $models = [];

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
