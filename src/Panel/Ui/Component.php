<?php

namespace Kirby\Panel\Ui;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * Component that can be passed as component-props array
 * to the Vue Panel frontend
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
abstract class Component
{
	protected string $key;
	public array $attrs = [];

	public function __construct(
		public string $component,
		public string|null $class = null,
		public string|null $style = null,
		...$attrs
	) {
		$this->attrs = $attrs;
	}

	/**
	 * Magic setter and getter for component properties
	 *
	 * ```php
	 * $component->class('my-class')
	 * ```
	 */
	public function __call(string $name, array $args = [])
	{
		if (property_exists($this, $name) === false) {
			throw new LogicException(
				message: 'The property "' . $name . '" does not exist on the UI component "' . $this->component . '"'
			);
		}

		// getter
		if ($args === []) {
			return $this->$name;
		}

		// setter
		$this->$name = $args[0];
		return $this;
	}

	/**
	 * Returns a (unique) key that can be used
	 * for Vue's `:key` attribute
	 */
	public function key(): string
	{
		return $this->key ??= Str::random(10, 'alphaNum');
	}

	/**
	 * Returns the props that will be passed to the Vue component
	 */
	public function props(): array
	{
		return [
			'class' => $this->class,
			'style' => $this->style,
			...$this->attrs
		];
	}

	/**
	 * Returns array with the Vue component name and props array
	 */
	public function render(): array|null
	{
		return [
			'component' => $this->component,
			'key'       => $this->key(),
			'props'     => array_filter(
				$this->props(),
				fn ($prop) => $prop !== null
			)
		];
	}
}
