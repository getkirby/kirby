<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\Site;
use Kirby\Panel\Ui\Button\ViewButtons;

/**
 * Controls the site view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelViewController<Site, \Kirby\Panel\Site>
 */
class SiteViewController extends ModelViewController
{
	public function __construct(
		Site $model
	) {
		parent::__construct($model);
	}

	public function buttons(): ViewButtons
	{
		return parent::buttons()->defaults(
			'open',
			'preview',
			'languages'
		);
	}

	public static function factory(): static
	{
		return new static(model: Find::site());
	}

	public function props(): array
	{
		$props = parent::props();

		return [
			...$props,
			'blueprint'   => 'site',
			'id'          => '/',
			'permissions' => [
				...$this->model->guards()->availability(),
				// the preview renders the home page with its preview token
				// @todo 7.0 Only check `site.preview` with a site preview token
				'preview' =>
					$this->model->guards()->isAvailable('preview') === true &&
					$this->model->homePage()?->guards()->isAvailable('preview') === true,
			],
		];
	}

	public function title(): string
	{
		$title = $this->model->title();
		$title = $title->or($this->model->blueprint()->title());
		return $title->toString();
	}
}
