<?php

namespace Kirby\Panel\Ui;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class Drawer extends Component
{
	public function __construct(
		string $component = 'k-drawer',
		string|null $class = null,
		public string|null $icon = null,
		public array|null $options = null,
		string|null $style = null,
		public string|null $title = null,
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component' => $component,
			'class'     => $class,
			'style'     => $style
		]);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'icon'     => $this->icon,
			'options'  => $this->options,
			'title'    => $this->title,
		];
	}
}
