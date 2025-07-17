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
class Collection extends Items
{
	public function __construct(
		public array $columns = [],
		public string $component = 'k-collection',
		public array|null $empty = null,
		public string|null $help = null,
		public array $items = [],
		public string $layout = 'list',
		public bool $link = true,
		public array|bool $pagination = false,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'medium',
		public string|null $theme = null,
	) {
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'empty'      => $this->empty(),
			'help'       => $this->help(),
			'pagination' => $this->pagination(),
		];
	}
}
