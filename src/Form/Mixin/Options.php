<?php

namespace Kirby\Form\Mixin;

use Kirby\Form\FieldOptions;
use Kirby\Reflection\Attributes\Prop;

/**
 * Provides options loading and resolution for choice-based fields
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Options
{
	/**
	 * An array with options
	 */
	#[Prop(computed: true)]
	protected array|string|null $options = null;

	/**
	 * Cache for the resolved options
	 *
	 * @var list<array<string, mixed>>|null
	 */
	protected array|null $optionsCache = null;

	/**
	 * @return list<array<string, mixed>>
	 */
	protected function fetchOptions(): array
	{
		$props   = FieldOptions::polyfill(['options' => $this->options ?? []]);
		$options = FieldOptions::factory($props['options']);
		return $options->render($this->model());
	}

	/**
	 * Returns the resolved options
	 *
	 * @return list<array<string, mixed>>
	 */
	public function options(): array
	{
		return $this->optionsCache ??= $this->fetchOptions();
	}
}
