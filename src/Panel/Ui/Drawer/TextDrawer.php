<?php

namespace Kirby\Panel\Ui\Drawer;

use Kirby\Panel\Ui\Drawer;
use Override;

/**
 * Drawer that displays some text
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class TextDrawer extends Drawer
{
	public function __construct(
		string $component = 'k-text-drawer',
		string|null $class = null,
		public string|null $empty = null,
		string|null $icon = null,
		array|null $options = null,
		string|null $style = null,
		string|null $title = null,
		public string|null $text = null,
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component' => $component,
			'class'     => $class,
			'icon'      => $icon,
			'options'   => $options,
			'style'     => $style,
			'title'     => $title,
		]);
	}

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'empty' => $this->empty,
			'text'  => $this->text
		];
	}
}
