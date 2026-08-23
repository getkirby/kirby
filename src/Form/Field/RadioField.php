<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Radio Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RadioField extends OptionField
{
	use Mixin\Columns;

	public function __construct(
		int|null $columns = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->columns = $columns;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'columns' => $this->columns(),
		];
	}
}
