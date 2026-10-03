<?php

namespace Kirby\Form\Field;

/**
 * Range field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RangeField extends NumberField
{
	protected float|null $max = 100;

	protected array|bool $tooltip = true;

	public function __construct(
		array|bool|null $tooltip = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->tooltip = $tooltip ?? $this->tooltip;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'tooltip' => $this->tooltip()
		];
	}

	public function tooltip(): array|bool
	{
		if (is_array($this->tooltip) === true) {
			return [
				'after'  => $this->i18n($this->tooltip['after'] ?? null),
				'before' => $this->i18n($this->tooltip['before'] ?? null)
			];
		}

		return $this->tooltip;
	}
}
