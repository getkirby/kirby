<?php

namespace Kirby\Toolkit;

use AllowDynamicProperties;
use ArgumentCountError;
use Closure;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use TypeError;

/**
 * Vue-like components
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @todo remove the following psalm suppress when PHP >= 8.2 required
 * @psalm-suppress UndefinedAttributeClass
 */
#[AllowDynamicProperties]
class Component
{
	/**
	 * Registry for all component mixins
	 */
	public static array $mixins = [];

	/**
	 * Registry for all component types
	 */
	public static array $types = [];

	/**
	 * An array of all computed properties
	 */
	protected array $computed = [];

	/**
	 * An array of all registered methods
	 */
	protected array $methods = [];

	/**
	 * An array of all component options
	 * from the component definition
	 */
	protected array|string $options = [];

	/**
	 * An array of all resolved props
	 */
	protected array $props = [];

	/**
	 * Creates a new component for the given type
	 *
	 * @param string $type The component type
	 * @param array $attrs An array of all passed attributes
	 */
	public function __construct(
		protected string $type,
		protected array $attrs = []
	) {
		if (isset(static::$types[$type]) === false) {
			throw new InvalidArgumentException(
				message: 'Undefined component type: ' . $type
			);
		}

		$this->options = $options = static::setup($type);
		$this->methods = $methods = $options['methods'] ?? [];

		foreach ($attrs as $attrName => $attrValue) {
			$this->$attrName = $attrValue;
		}

		if (isset($options['props']) === true) {
			$this->applyProps($options['props']);
		}

		if (isset($options['computed']) === true) {
			$this->applyComputed($options['computed']);
		}

		// Reset main properties to avoid them being overwritten
		// when applying props and computes
		$this->attrs   = $attrs;
		$this->methods = $methods;
		$this->options = $options;
		$this->type    = $type;
	}

	/**
	 * Magic caller for defined methods and properties
	 */
	public function __call(string $name, array $arguments = [])
	{
		if (array_key_exists($name, $this->computed) === true) {
			return $this->computed[$name];
		}

		if (array_key_exists($name, $this->props) === true) {
			return $this->props[$name];
		}

		if (array_key_exists($name, $this->methods) === true) {
			return $this->methods[$name]->call($this, ...$arguments);
		}

		return $this->$name;
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Fallback for missing properties to return
	 * null instead of an error
	 */
	public function __get(string $attr)
	{
		return null;
	}

	/**
	 * A set of default options for each component.
	 * This can be overwritten by extended classes
	 * to define basic options that should always
	 * be applied.
	 */
	public static function defaults(): array
	{
		return [];
	}

	/**
	 * Register a single property
	 */
	protected function applyProp(string $name, mixed $value): void
	{
		// unset prop
		if ($value === null) {
			unset($this->props[$name]);

			/**
			 * Unset dynamic declared properties
			 * that are not defined in the class
			 */
			if (
				isset($this->$name) === true &&
				array_key_exists($name, get_object_vars($this)) === true &&
				array_key_exists($name, get_class_vars(get_class($this))) === false
			) {
				unset($this->$name);
			}

			return;
		}

		// apply a prop via a closure
		if ($value instanceof Closure) {
			if (isset($this->attrs[$name]) === true) {
				try {
					$this->$name = $this->props[$name] = $value->call(
						$this,
						$this->attrs[$name]
					);
					return;
				} catch (TypeError) {
					throw new TypeError('Invalid value for "' . $name . '"');
				}
			}

			try {
				$this->$name = $this->props[$name] = $value->call($this);
				return;
			} catch (ArgumentCountError) {
				throw new ArgumentCountError('Please provide a value for "' . $name . '"');
			}
		}

		// simple prop assignment by value
		$this->$name = $this->props[$name] = $value;
	}

	/**
	 * Register all defined props and apply the
	 * passed values.
	 */
	protected function applyProps(array $props): void
	{
		foreach ($props as $name => $value) {
			$this->applyProp($name, $value);
		}
	}

	/**
	 * Register all computed properties and calculate their values.
	 * This must happen after all props are registered.
	 */
	protected function applyComputed(array $computed): void
	{
		foreach ($computed as $name => $function) {
			if ($function instanceof Closure) {
				$this->$name = $this->computed[$name] = $function->call($this);
			}
		}
	}

	/**
	 * Load a component definition by type
	 */
	public static function load(string $type): array
	{
		$definition = static::$types[$type];

		// load definitions from string
		if (is_string($definition) === true) {
			if (is_file($definition) !== true) {
				throw new Exception(
					'Component definition ' . $definition . ' does not exist'
				);
			}

			static::$types[$type] = $definition = F::load(
				$definition,
				allowOutput: false
			);
		}

		return $definition;
	}

	/**
	 * Loads all options from the component definition
	 * mixes in the defaults from the defaults method and
	 * then injects all additional mixins, defined in the
	 * component options.
	 */
	public static function setup(string $type): array
	{
		// load component definition
		$definition = static::load($type);

		if (isset($definition['extends']) === true) {
			// extend other definitions
			$options = array_replace_recursive(
				static::defaults(),
				static::load($definition['extends']),
				$definition
			);
		} else {
			// inject defaults
			$options = array_replace_recursive(static::defaults(), $definition);
		}

		// inject mixins
		foreach ($options['mixins'] ?? [] as $mixin) {
			if (isset(static::$mixins[$mixin]) === true) {
				if (is_string(static::$mixins[$mixin]) === true) {
					// resolve a path to a mixin on demand

					static::$mixins[$mixin] = F::load(
						static::$mixins[$mixin],
						allowOutput: false
					);
				}

				$options = array_replace_recursive(
					static::$mixins[$mixin],
					$options
				);
			}
		}

		return $options;
	}

	/**
	 * Converts all props and computed props to an array
	 */
	public function toArray(): array
	{
		$closure = $this->options['toArray'] ?? null;

		if ($closure instanceof Closure) {
			return $closure->call($this);
		}

		$array = [...$this->attrs, ...$this->props, ...$this->computed];

		ksort($array);

		return $array;
	}
}
