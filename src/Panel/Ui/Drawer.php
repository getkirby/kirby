<?php

namespace Kirby\Panel\Ui;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 * @unstable
 */
class Drawer extends Component
{
	public function __construct(
		string $component = 'k-drawer',
		string|null $class = null,
		public bool $disabled = false,
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
			'disabled' => $this->disabled,
			'icon'     => $this->icon,
			'options'  => $this->options,
			'title'    => $this->title,
		];
	}
}
