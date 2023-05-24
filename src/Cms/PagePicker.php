<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The PagePicker class helps to
 * fetch the right pages and the parent
 * model for the API calls for the
 * page picker component in the panel.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PagePicker extends Picker
{
	// TODO: null only due to our Properties setters,
	// remove once our implementation is better
	protected Pages|null $items = null;
	protected Pages|null $itemsForQuery = null;
	protected Page|Site|null $parent;

	/**
	 * Extends the basic defaults
	 */
	public function defaults(): array
	{
		return array_merge(parent::defaults(), [
			// Page ID of the selected parent. Used to navigate
			'parent' => null,
			// enable/disable subpage navigation
			'subpages' => true,
		]);
	}

	/**
	 * Returns the parent model object that
	 * is currently selected in the page picker.
	 * It normally starts at the site, but can
	 * also be any subpage. When a query is given
	 * and subpage navigation is deactivated,
	 * there will be no model available at all.
	 */
	public function model(): Page|Site|null
	{
		// no subpages navigation = no model
		if ($this->options['subpages'] === false) {
			return null;
		}

		// the model for queries is a bit more tricky to find
		if (empty($this->options['query']) === false) {
			return $this->modelForQuery();
		}

		return $this->parent();
	}

	/**
	 * Returns a model object for the given
	 * query, depending on the parent and subpages
	 * options.
	 */
	public function modelForQuery(): Page|Site|null
	{
		if ($this->options['subpages'] === true && empty($this->options['parent']) === false) {
			return $this->parent();
		}

		return $this->items()?->parent();
	}

	/**
	 * Returns basic information about the
	 * parent model that is currently selected
	 * in the page picker.
	 */
	public function modelToArray(Page|Site $model = null): array|null
	{
		if ($model === null) {
			return null;
		}

		// the selected model is the site. there's nothing above
		if ($model instanceof Site) {
			return [
				'id'     => null,
				'parent' => null,
				'title'  => $model->title()->value()
			];
		}

		// the top-most page has been reached
		// the missing id indicates that there's nothing above
		if ($model->id() === $this->start()->id()) {
			return [
				'id'     => null,
				'parent' => null,
				'title'  => $model->title()->value()
			];
		}

		// the model is a regular page
		return [
			'id'     => $model->id(),
			'parent' => $model->parentModel()->id(),
			'title'  => $model->title()->value()
		];
	}

	/**
	 * Search all pages for the picker
	 */
	public function items(): Pages|null
	{
		// cache
		if ($this->items !== null) {
			return $this->items;
		}

		// no query? simple parent-based search for pages
		if (empty($this->options['query']) === true) {
			$items = $this->itemsForParent();

		// when subpage navigation is enabled, a parent
		// might be passed in addition to the query.
		// The parent then takes priority.
		} elseif ($this->options['subpages'] === true && empty($this->options['parent']) === false) {
			$items = $this->itemsForParent();

		// search by query
		} else {
			$items = $this->itemsForQuery();
		}

		// filter protected and hidden pages
		$items = $items->filter('isListable', true);

		// search
		$items = $this->search($items);

		// paginate the result
		return $this->items = $this->paginate($items);
	}

	/**
	 * Search for pages by parent
	 */
	public function itemsForParent(): Pages
	{
		return $this->parent()->children();
	}

	/**
	 * Search for pages by query string
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function itemsForQuery(): Pages
	{
		// cache
		if ($this->itemsForQuery !== null) {
			return $this->itemsForQuery;
		}

		$model = $this->options['model'];
		$items = $model->query($this->options['query']);

		// help mitigate some typical query usage issues
		// by converting site and page objects to proper
		// pages by returning their children
		$items = match (true) {
			$items instanceof Site,
			$items instanceof Page  => $items->children(),
			$items instanceof Pages => $items,

			default => throw new InvalidArgumentException('Your query must return a set of pages')
		};

		return $this->itemsForQuery = $items;
	}

	/**
	 * Returns the parent model.
	 * The model will be used to fetch
	 * subpages unless there's a specific
	 * query to find pages instead.
	 */
	public function parent(): Page|Site
	{
		return $this->parent ??= $this->kirby->page($this->options['parent']) ?? $this->site;
	}

	/**
	 * Calculates the top-most model (page or site)
	 * that can be accessed when navigating
	 * through pages.
	 */
	public function start(): Page|Site
	{
		if (empty($this->options['query']) === false) {
			return $this->itemsForQuery()?->parent() ?? $this->site;
		}

		return $this->site;
	}

	/**
	 * Returns an associative array
	 * with all information for the picker.
	 * This will be passed directly to the API.
	 */
	public function toArray(): array
	{
		$array = parent::toArray();
		$array['model'] = $this->modelToArray($this->model());

		return $array;
	}
}
