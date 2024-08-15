<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Ui\Buttons\ViewButtons;

/**
 * Provides information about the site model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Site extends Model
{
	/**
	 * @var \Kirby\Cms\Site
	 */
	protected ModelWithContent $model;

	/**
	 * Returns header buttons which should be displayed
	 * on the site view
	 */
	public function buttons(): array
	{
		return ViewButtons::view($this)->defaults(
			'preview',
			'languages'
		)->bind(['site' => $this->model()])
			->render();
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	public function dropdownOption(): array
	{
		return [
			'icon' => 'home',
			'text' => $this->model->title()->value(),
		] + parent::dropdownOption();
	}

	/**
	 * Returns the image file object based on provided query
	 *
	 * @internal
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$query ??= 'site.image';
		return parent::imageSource($query);
	}

	/**
	 * Returns the full path without leading slash
	 */
	public function path(): string
	{
		return 'site';
	}

	/**
	 * Returns the data array for the
	 * view's component props
	 *
	 * @internal
	 */
	public function props(): array
	{
		return [
			...parent::props(),
			'blueprint' => 'site',
			'model' => [
				'content'    => $this->content(),
				'link'       => $this->url(true),
				'previewUrl' => $this->model->previewUrl(),
				'title'      => $this->model->title()->toString(),
				'uuid'       => fn () => $this->model->uuid()?->toString(),
			]
		];
	}

	/**
	 * Returns the data array for
	 * this model's Panel view
	 *
	 * @internal
	 */
	public function view(): array
	{
		return [
			'component' => 'k-site-view',
			'props'     => $this->props()
		];
	}
}
