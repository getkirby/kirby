<?php

namespace Kirby\Panel\Ui;

use Kirby\Exception\LogicException;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
abstract class Component
{
	public function __construct(
		public string $component,
		public string|null $class = null,
		public string|null $style = null
	) {
	}

	public function __call(string $name, array $args = [])
	{
		if (property_exists($this, $name) === false) {
			throw new LogicException('The property "' . $name . '" does not exist on the UI component "' . $this->component . '"');
		}

		// getter
		if (count($args) === 0) {
			return $this->$name;
		}

		// setter
		$this->$name = $args[0];
		return $this;
	}

	public function props(): array
	{
		return [
			'class' => $this->class,
			'style' => $this->style,
		];
	}

	public function render(): array|null
	{
		return [
			'component' => $this->component,
			'props'     => $this->props()
		];
	}
}
