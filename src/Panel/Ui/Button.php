<?php

namespace Kirby\Panel\Ui;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class Button extends Component
{
	public function __construct(
		public string $component = 'k-button',
		public array|null $badge = null,
		public string|null $class = null,
		public string|bool|null $current = null,
		public array|string|null $dialog = null,
		public bool $disabled = false,
		public array|string|null $drawer = null,
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
			'text'       => $this->text(),
			'theme'      => $this->theme,
			'title'      => $this->title(),
			'type'       => $this->type,
			'variant'    => $this->variant,
		];
	}

	public function text(): string|null
	{
		return $this->i18n($this->text);
	}

	public function title(): string|null
	{
		return $this->i18n($this->title);
	}
}
