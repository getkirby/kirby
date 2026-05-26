<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Ui\Item\PageItem;

/**
 * Controls the Panel dialog for selecting pages
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
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
	 * Resolves the parent model that is currently
	 * selected in the page picker. It normally starts
	 * at the site, but can also be any subpage. When
	 * a query is given and subpage navigation is
	 * deactivated, there will be no model available
	 * at all. Falls back to the root when the
	 * requested parent is missing or not accessible
	 * for the current user.
	 *
	 * @throws \Kirby\Exception\PermissionException if neither
	 *                                              the requested parent nor the root are accessible
	 */
	public function parent(): Page|Site|null
	{
		// no subpages navigation = no current parent
		if ($this->subpages === false) {
			return null;
		}

		if ($id = $this->request->get('parent')) {
			$parent = $this->find($id);

			if ($parent?->isAccessible() === true) {
				return $parent;
			}
		}

		try {
			$root = $this->root();

			if ($root->isAccessible() === true) {
				return $root;
			}
		} catch (NotFoundException) {
			// fall through to throw PermissionException
		}

		throw new PermissionException(
			key: 'page.undefined'
		);
	}

	/**
	 * Returns the parent representation for the
	 * dialog props: `null` when no parent applies,
	 * or an array with the parent's `id`, `parent`
	 * and `title`. The `id` is `null` when the
	 * top-most reachable model has been reached.
	 */
	protected function parentProps(): array|null
	{
		$parent = $this->parent();

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
			'parent'    => $this->parentProps()
		];
	}

	public function query(): string
	{
		$parent = $this->parent();

		// if a specific parent is currently selected and accessible,
		// use its children for the picker query; otherwise (no parent
		// requested, or the requested parent fell back to the root)
		// use the default query
		if (
			$parent !== null &&
			$parent->id() !== $this->root()->id()
		) {
			return 'page("' . $parent->id() . '").children';
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
			return Find::site();
		}

		$parent = $this->model->query($this->query);

		if ($parent instanceof Pages) {
			$parent = $parent->parent();
		}

		return $parent ?? Find::site();
	}
}
