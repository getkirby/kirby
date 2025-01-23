<?php

namespace Kirby\Panel\Ui;

use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class Button extends Component
{
	public function __construct(
		public string $component = 'k-button',
		public array|null $badge = null,
		public string|null $class = null,
		public string|bool|null $current = null,
		public string|null $dialog = null,
		public bool $disabled = false,
		public string|null $drawer = null,
		public bool|null $dropdown = null,
		public string|null $icon = null,
		public string|null $link = null,
		public bool|string $responsive = true,
		public string|null $size = null,
		public string|null $style = null,
		public string|null $target = null,
		public string|array|null $text = null,
		public string|null $theme = null,
		public string|array|null $title = null,
		public string $type = 'button',
		public string|null $variant = null,
		...$attrs
	) {
		$this->attrs = $attrs;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'badge'      => $this->badge,
			'current'    => $this->current,
			'dialog'     => $this->dialog,
			'disabled'   => $this->disabled,
			'drawer'     => $this->drawer,
			'dropdown'   => $this->dropdown,
			'icon'       => $this->icon,
			'link'       => $this->link,
			'responsive' => $this->responsive,
			'size'       => $this->size,
			'target'     => $this->target,
			'text'       => I18n::translate($this->text, $this->text),
			'theme'      => $this->theme,
			'title'      => I18n::translate($this->title, $this->title),
			'type'       => $this->type,
			'variant'    => $this->variant,
		];
	}
}
