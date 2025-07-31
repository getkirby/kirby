<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Pages;
use Kirby\Toolkit\Escape;

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
	public function models(): Pages
	{
		return $this->kirby->site()
			->index(true)
			->search($this->query)
			->filter('isListable', true);
	}

	/**
	 * @param \Kirby\Cms\Page $model
	 */
	public function result($model): array
	{
		return [
			'image' => $model->panel()->image(),
			'text'  => Escape::html($model->title()->value()),
			'link'  => $model->panel()->url(true),
			'info'  => Escape::html($model->id()),
			'uuid'  => $model->uuid()?->toString(),
		];
	}
}
