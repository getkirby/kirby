<?php

namespace Kirby\Panel\Ui;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
abstract class Renderable
{
	public string $component;
	protected string $key;

	/**
	 * Magic setter and getter for properties
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

	abstract public function render(): array|null;
}
