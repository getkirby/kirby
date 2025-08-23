<?php

namespace Kirby\Panel\Ui;

use Kirby\Panel\Ui\Button\ViewButtons;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class View extends Component
{
	public function __construct(
		string $component,
		public ViewButtons|array $buttons = [],
		public array $breadcrumb = [],
		string|null $class = null,
		public string|null $search = null,
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

	public function buttons(): ViewButtons
	{
		if ($this->buttons instanceof ViewButtons === false) {
			return new ViewButtons($this->buttons);
		}

		return $this->buttons;
	}

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'buttons' => $this->buttons()->render(),
			'title'   => $this->title
		];
	}

	#[Override]
	public function render(): array|null
	{
		$view = [
			...parent::render(),
			'breadcrumb' => $this->breadcrumb
		];

		// only set search and title if they exist;
		// this will let the vue component define
		// a proper default values
		if ($this->search) {
			$view['search'] = $this->search;
		}

		if ($this->title) {
			$view['title'] = $this->title;
		}

		return $view;
	}
}
