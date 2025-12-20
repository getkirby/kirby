<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * Controls the Panel dialog for selecting pages
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PagePickerDialogController extends ModelPickerDialogController
{
	protected PagesCollector $collector;

	public function __construct(
		ModelWithContent $model,
		bool $hasSearch = true,
		array|null $image = [],
		string|null $info = null,
		string $layout = 'list',
		public int|null $limit = null,
		int|null $max = null,
		bool $multiple = true,
		public string|null $query = null,
		public bool|null $subpages = true,
		string|null $size = null,
		string|null $text = null,
	) {
		parent::__construct(
			model:     $model,
			hasSearch: $hasSearch,
			image:     $image,
			info:      $info,
			layout:    $layout,
			max:       $max,
			multiple:  $multiple,
			size:      $size,
			text:      $text
		);
	}

	public function collector(): PagesCollector
	{
		return $this->collector ??= new class (
			limit:  $this->limit,
			page:   $this->page,
			parent: $this->model,
			query:  $this->query(),
			search: $this->search,
		) extends PagesCollector {
			// if the query only returns a site or page object
			// instead of a pages collection, use its children
			protected function collectByQuery(): Pages
			{
				$pages = $this->parent()->query($this->query);

				if ($pages instanceof Site || $pages instanceof Page) {
					return $pages->children();
				}

				return $pages ?? new Pages([]);
			}
		};
	}

	protected function empty(): array
	{
		return [
			'icon' => 'page',
			'text' => $this->i18n('dialog.pages.empty')
		];
	}

	public function find(string $id): Page|null
	{
		return $this->kirby->page($id);
	}

	/**
	 * Returns the item data for a page
	 * @param \Kirby\Cms\Page $model
	 */
	public function item(ModelWithContent $model): array
	{
		return [
			...(new PageItem(
				page: $model,
				image: $this->image,
				info: $this->info,
				layout: $this->layout,
				text: $this->text
			))->props(),
			'hasChildren' => $model->hasChildren()
		];
	}

	/**
	 * Returns the parent model object that
	 * is currently selected in the page picker.
	 * It normally starts at the site, but can
	 * also be any subpage. When a query is given
	 * and subpage navigation is deactivated,
	 * there will be no model available at all.
	 */
	public function parent(): array|null
	{
		// no subpages navigation = no current parent
		if ($this->subpages === false) {
			return null;
		}

		if ($parent = $this->request->get('parent')) {
			$parent = $this->find($parent);
		} else {
			$parent = $this->root();
		}

		if ($parent === null) {
			return null;
		}

		// the current parent is the site or the top-most page has been reached.
		// the missing id indicates that there's nothing above
		if (
			$parent instanceof Site ||
			$parent->id() === $this->root()->id()
		) {
			return [
				'id'     => null,
				'parent' => null,
				'title'  => $parent->title()->value()
			];
		}

		return [
			'id'     => $parent->id(),
			'parent' => $parent->parentModel()->id(),
			'title'  => $parent->title()->value()
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'component' => 'k-page-picker-dialog',
			'parent'    => $this->parent()
		];
	}

	public function query(): string
	{
		// if a current parent is present, use its children
		if ($current = $this->request->get('parent')) {
			return 'page("' . $current . '").children';
		}

		return $this->query ?? 'site.children';
	}

	/**
	 * Calculates the top-most model (page or site)
	 * that can be accessed when navigating
	 * through pages.
	 */
	public function root(): Page|Site
	{
		if ($this->query === null) {
			return $this->site;
		}

		$parent = $this->model->query($this->query);

		if ($parent instanceof Pages) {
			$parent = $parent->parent();
		}

		return $parent ?? $this->site;
	}
}
