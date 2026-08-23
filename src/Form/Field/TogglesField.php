<?php

namespace Kirby\Form\Field;

/**
 * Toggles Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TogglesField extends OptionField
{
	/**
	 * Toggles will automatically span the full width of the field. With the grow option, you can disable this behaviour for a more compact layout.
	 */
	protected bool $grow = true;

	/**
	 * If `false` all labels will be hidden for icon-only toggles.
	 */
	protected bool $labels = true;

	/**
	 * A toggle can be deactivated on click. If resettable is `false` deactivating a toggle is no longer possible.
	 */
	protected bool $resettable = true;

	public function __construct(
		bool|null $grow = null,
		bool|null $labels = null,
		bool|null $resettable = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->grow       = $grow ?? $this->grow;
		$this->labels     = $labels ?? $this->labels;
		$this->resettable = $resettable ?? $this->resettable;
	}

	public function labels(): bool
	{
		return $this->labels;
	}

	public function grow(): bool
	{
		return $this->grow;
	}

	public function resettable(): bool
	{
		return $this->resettable;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'grow'       => $this->grow(),
			'labels'     => $this->labels(),
			'resettable' => $this->resettable(),
		];
	}
}
