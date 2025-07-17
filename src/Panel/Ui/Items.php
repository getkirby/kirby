<?php

namespace Kirby\Panel\Ui;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class Items extends Component
{
	public function __construct(
		public string $component = 'k-items',
		public array $columns = [],
		public array $fields = [],
		public array $items = [],
		public string $layout = 'list',
		public bool $link = true,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'medium',
		public string|null $theme = null,
	) {
	}

	public function props(): array
	{
		return [
			'columns'   => $this->columns(),
			'fields'    => $this->fields(),
			'items'     => $this->items(),
			'layout'    => $this->layout(),
			'link'      => $this->link(),
			'selecting' => $this->selecting(),
			'sortable'  => $this->sortable(),
			'size'      => $this->size(),
			'theme'     => $this->theme(),
		];
	}
}
