<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Page;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Toolkit\I18n;

/**
 * Controls the preview view for a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PagePreviewViewController extends ModelPreviewViewController
{
	public function __construct(
		Page $model,
		string $mode
	) {
		parent::__construct($model, $mode);
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view(view: $this->id(), model: $this->model)->defaults(
			'languages',
			'page.settings',
			'page.versions',
		)
			->bind(['mode' => $this->mode]);
	}

	public function props(): array
	{
		$view  = new PageViewController($this->model);
		$props = $view->props();

		return [
			...$props,
			...parent::props(),
			'back'  => $props['link'],
			'title' => $props['title'] . ' | ' . I18n::translate('preview'),
		];
	}
}
