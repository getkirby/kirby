<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Site;
use Kirby\Panel\Model;
use Kirby\Panel\Ui\Button\ViewButtons;
use Override;

/**
 * Controls the site view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SiteViewController extends ModelViewController
{
	/**
	 * @var \Kirby\Cms\Site $model
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\Site
	 */
	protected Model $panel;

	public function __construct(
		Site $model
	) {
		parent::__construct($model);
	}

	#[Override]
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
		return new static(model: App::instance()->site());
	}

	#[Override]
	public function props(): array
	{
		$props = parent::props();

		return [
			...$props,
			'blueprint'   => 'site',
			'id'          => '/',
			'permissions' => [
				...$props['permissions'],
				'preview' => $this->model->homePage()?->permissions()->can('preview') === true,
			],
		];
	}

	#[Override]
	public function title(): string
	{
		$title = $this->model->title();
		$title = $title->or($this->model->blueprint()->title());
		return $title->toString();
	}
}
