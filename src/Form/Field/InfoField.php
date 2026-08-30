<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Info field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class InfoField extends DisplayField
{
	use Mixin\Icon;
	use Mixin\Text;
	use Mixin\Theme;

	public function __construct(
		string|null $icon = null,
		array|string|null $text = null,
		string|null $theme = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->icon  = $icon;
		$this->text  = $text;
		$this->theme = $theme;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'icon'  => $this->icon(),
			'text'  => $this->text(),
			'theme' => $this->theme(),
		];
	}
}
