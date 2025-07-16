<?php

namespace Kirby\Panel\Ui\Drawers;

use Kirby\Panel\Ui\Drawer;

/**
 * Drawer that displays some text
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class TextDrawer extends Drawer
{
	public function __construct(
		string|null $class = null,
		bool $disabled = false,
		string|null $icon = null,
		array|null $options = null,
		public string|null $text = null,
		string|null $style = null,
		string|null $title = null,
	) {
		parent::__construct(
			component: 'k-text-drawer',
			class: $class,
			disabled: $disabled,
			icon: $icon,
			options: $options,
			style: $style,
			title: $title,
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'text' => $this->text
		];
	}
}
