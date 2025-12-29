<?php

namespace Kirby\Form\Field;

/**
 * Buttons field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ButtonsField extends DisplayField
{
	/**
	 * Array or query string for buttons
	 */
	protected array|string|null $buttons;

	public function __construct(
		array|string|null $label = null,
		array|string|null $help = null,
		string|null $name = null,
		array|string|null $buttons = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			label: $label,
			help:  $help,
			name:  $name,
			when:  $when,
			width: $width
		);

		$this->buttons = $buttons;
	}

	public function buttons(): array
	{
		if (is_string($this->buttons) === true) {
			return $this->model()->query($this->buttons);
		}

		return $this->buttons ?? [];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'buttons' => $this->buttons()
		];
	}
}
