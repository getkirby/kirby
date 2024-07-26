<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Buttons\ViewButtons;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class View extends Component
{
	protected App $kirby;

	public function __construct(
		public string $id,
		string|null $component = null,
		public string|null $class = null,
		public string|null $search = null,
		public string|null $style = null,
		public string|null $title = null,
	) {
		$this->kirby = App::instance();

		parent::__construct(
			component: $component ?? 'k-' . $id . '-view',
			class: $class,
			style: $style
		);
	}

	/**
	 * Returns breadcrumb trail to  display
	 */
	public function breadcrumb(): array
	{
		return [];
	}

	/**
	 * Returns header button names which should be displayed
	 */
	public function buttons(): ViewButtons
	{
		return ViewButtons::view($this->id);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			// add buttons lazily as closure
			'buttons' => fn () => $this->buttons()->render()
		];
	}

	public function render(): array|null
	{
		return [
			...parent::render(),
			// add breadcrumb lazily as closure
			'breadcrumb' => fn () => $this->breadcrumb(),
			'search'     => $this->search,
			'title'      => $this->title
		];
	}
}
