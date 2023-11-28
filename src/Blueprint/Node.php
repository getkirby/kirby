<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;

/**
 * A node of the blueprint
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage once blueprint refactoring is done
 * @codeCoverageIgnore
 */
class Node
{
	public const TYPE = 'node';

	public function __construct(
		public string $id,
		public Extension|null $extends = null,
	) {
	}

	/**
	 * Dynamic getter for properties
	 */
	public function __call(string $name, array $args)
	{
		$this->defaults();
		return $this->$name;
	}

	/**
	 * Apply default values
	 */
	public function defaults(): static
	{
		return $this;
	}

	/**
	 * Creates an instance by a set of array properties.
	 */
	public static function factory(array $props): static
	{
		$props = Extension::apply($props);
		$props = static::polyfill($props);
		return Factory::make(static::class, $props);
	}

	public static function load(string|array $props): static
	{
		// load by path
		if (is_string($props) === true) {
			$props = static::loadProps($props);
		}

		return static::factory($props);
	}

	public static function loadProps(string $path): array
	{
		$config = new Config($path);
		$props  = $config->read();

		// add the id if it's not set yet
		$props['id'] ??= basename($path);

		return $props;
	}

	/**
	 * Optional method that runs before static::factory sends
	 * its properties to the instance. This is perfect to clean
	 * up props or keep deprecated props compatible.
	 */
	public static function polyfill(array $props): array
	{
		return $props;
	}

	public function render(ModelWithContent $model)
	{
		// apply default values
		$this->defaults();

		$array = [];

		// go through all public properties
		foreach (get_object_vars($this) as $key => $value) {
			if (is_object($value) === false && is_resource($value) === false) {
				$array[$key] = $value;
				continue;
			}

			if (method_exists($value, 'render') === true) {
				$array[$key] = $value->render($model);
			}
		}

		return $array;
	}

	/**
	 * Universal setter for properties
	 */
	public function set(string $property, $value): static
	{
		$this->$property = Factory::forProperty(static::class, $property, $value);
		return $this;
	}
}
