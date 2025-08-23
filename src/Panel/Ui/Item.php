<?php

namespace Kirby\Panel\Ui;

use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Item extends Component
{
	protected string $layout;

	public function __construct(
		public string $text,
		public string|array|false|null $image = [],
		public string|null $info = null,
		string|null $layout = null,
	) {
		parent::__construct(component: 'k-item');

		$this->layout = $layout ?? 'list';
	}

	protected function info(): string|null
	{
		return $this->info;
	}

	protected function image(): array|false|null
	{
		return $this->image;
	}

	#[Override]
	public function props(): array
	{
		return [
			'image'  => $this->image(),
			'info'   => $this->info(),
			'layout' => $this->layout,
			'text'   => $this->text(),
		];
	}

	protected function text(): string
	{
		return $this->text;
	}
}
