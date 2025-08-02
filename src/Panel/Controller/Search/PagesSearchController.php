<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Pages;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * Controls the search requests for pages
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PagesSearchController extends ModelsSearchController
{
	/**
	 * @param \Kirby\Cms\Page $model
	 */
	public function item($model): PageItem
	{
		return new PageItem(page: $model, info: '{{ page.id }}');
	}

	public function models(): Pages
	{
		return $this->kirby->site()
			->index(true)
			->search($this->query)
			->filter('isListable', true);
	}
}
