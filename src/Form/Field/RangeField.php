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
	protected array|bool|null $tooltip;

	public function __construct(
		array|bool|null $tooltip = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->tooltip = $tooltip;
	}

	public function max(): float|null
	{
		return $this->max ?? 100;
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

		return $this->tooltip ?? true;
	}
}
