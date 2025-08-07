<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Controller\View\SiteViewController;

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
	 * @codeCoverageIgnore
	 */
	protected function viewController(): SiteViewController
	{
		return new SiteViewController($this->model);
	}
}
