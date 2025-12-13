<?php

namespace Kirby\Form\Mixin;

use Kirby\Form\FieldOptions;

trait Options
{
	/**
	 * An array with options
	 */
	protected array|string|null $options;
	protected array $optionsCache;

	protected function fetchOptions(): array
	{
		$props   = FieldOptions::polyfill(['options' => $this->options ?? []]);
		$options = FieldOptions::factory($props['options']);
		return $options->render($this->model());
	}

	public function options(): array
	{
		return $this->optionsCache ??= $this->fetchOptions();
	}
}
