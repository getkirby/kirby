<?php

namespace Kirby\Panel\Ui;

use Kirby\Toolkit\HtmlString;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Item extends Component
{
	protected string $layout;

	public function __construct(
		public string|HtmlString $text,
		public string|array|false|null $image = [],
		public string|HtmlString|null $info = null,
		string|null $layout = null,
	) {
		parent::__construct(component: 'k-item');

		$this->layout = $layout ?? 'list';
	}

	protected function info(): string|HtmlString|null
	{
		return $this->info;
	}

	protected function image(): string|array|false|null
	{
		return $this->image;
	}

	public function props(): array
	{
		return [
			'image'  => $this->image(),
			'info'   => $this->info(),
			'layout' => $this->layout,
			'text'   => $this->text(),
		];
	}

	protected function text(): string|HtmlString
	{
		return $this->text;
	}
}
