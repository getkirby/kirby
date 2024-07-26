<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Panel\Model;
use Kirby\Toolkit\A;

/**
 * Collects view buttons for a specific view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class ViewButtons
{
	public function __construct(
		public readonly string $view,
		public array|null $buttons = null,
		public array $data = []
	) {
		$this->buttons ??= App::instance()->option(
			'panel.viewButtons.' . $view
		);
	}

	/**
	 * Adds data passed to view button closures
	 *
	 * @return $this
	 */
	public function bind(array $data): static
	{
		$this->data = [...$this->data, ...$data];
		return $this;
	}


	/**
	 * Sets the default buttons
	 *
	 * @return $this
	 */
	public function defaults(string ...$defaults): static
	{
		$this->buttons ??= $defaults;
		return $this;
	}

	/**
	 * Returns array of button component-props definitions
	 */
	public function render(): array
	{
		$buttons = A::map(
			$this->buttons ?? [],
			fn ($button) =>
				ViewButton::factory(
					$button,
					$this->view,
					$this->data
				)?->render()
		);

		return array_values(array_filter($buttons));
	}

	/**
	 * Creates new instance for a view
	 * with special support for model views
	 */
	public static function view(string|Model $view): static
	{
		if ($view instanceof Model) {
			$blueprint = $view->model()->blueprint()->buttons();
			$view      = $view->model()::CLASS_ALIAS;
		}

		return new static(
			view: $view,
			buttons: $blueprint ?? null
		);
	}
}
