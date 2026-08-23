<?php

namespace Kirby\Form\Field;

use Kirby\Panel\Ui\Button\ModelButton;
use Kirby\Toolkit\A;

/**
 * Buttons field
 *
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
		array|string|null $buttons = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->buttons = $buttons;
	}

	public function buttons(): array
	{
		$buttons = $this->buttons ?? [];

		if (is_string($buttons) === true) {
			$buttons = $this->model()->query($buttons);
		}

		return A::map(
			$buttons,
			fn (array $button) => (new ModelButton(
				...$button,
				model: $this->model()
			))->props()
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'buttons' => $this->buttons()
		];
	}
}
