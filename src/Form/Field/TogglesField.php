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
	protected bool|null $grow = null;

	/**
	 * If `false` all labels will be hidden for icon-only toggles.
	 */
	protected bool|null $labels = null;

	/**
	 * A toggle can be deactivated on click. If resettable is `false` deactivating a toggle is no longer possible.
	 */
	protected bool|null $resettable = null;

	public function __construct(
		bool|null $grow = null,
		bool|null $labels = null,
		bool|null $resettable = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->grow       = $grow;
		$this->labels     = $labels;
		$this->resettable = $resettable;
	}

	public function labels(): bool
	{
		return $this->labels ?? true;
	}

	public function grow(): bool
	{
		return $this->grow ?? true;
	}

	public function resettable(): bool
	{
		return $this->resettable ?? true;
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
