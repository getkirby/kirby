<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Info field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
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
		array|string|null $label = null,
		array|string|null $help = null,
		string|null $icon = null,
		string|null $name = null,
		array|string|null $text = null,
		string|null $theme = null,
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

		$this->setIcon($icon);
		$this->setText($text);
		$this->setTheme($theme);
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
