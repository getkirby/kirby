<?php

namespace Kirby\Panel\Ui;

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
abstract class Component extends Renderable
{
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
			'props'     => array_filter($this->props(), fn ($x) => $x !== null)
		];
	}
}
