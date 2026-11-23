<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Content\Changes;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\Panel\Ui\Item\ModelItem;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * Controls the Panel dialog for displaying
 * content changes in the site, pages, users and files
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class ChangesDialogController extends DialogController
{
	public function __construct(
		protected Changes $changes = new Changes()
	) {
	}

	/**
	 * Returns the item props for all changed files
	 */
	public function files(): array
	{
		return $this->items($this->changes->files()->filter('isListable', true));
	}

	/**
	 * Helper method to return item props for a single given model
	 */
	public function item(File|Page|Site|User $model): array
	{
		$item = match (true) {
			$model instanceof File => new FileItem(file: $model),
			$model instanceof Page => new PageItem(page: $model),
			$model instanceof Site => new ModelItem(model: $model, image: ['icon' => 'home']),
			$model instanceof User => new UserItem(user: $model),
		};

		return $item->props();
	}

	/**
	 * Helper method to return item props for the given models
	 */
	public function items(Collection $models): array
	{
		return $models->values($this->item(...));
	}

	/**
	 * Returns the backend full definition for dialog
	 */
	public function load(): Dialog
	{
		if ($this->changes->cacheExists() === false) {
			$this->changes->generateCache();
		}

		return new Dialog(
			component: 'k-changes-dialog',
			files: $this->files(),
			pages: $this->pages(),
			site: $this->site(),
			users: $this->users(),
		);
	}

	/**
	 * Returns the item props for all changed pages
	 */
	public function pages(): array
	{
		return $this->items($this->changes->pages()->filter('isListable', true));
	}

	/**
	 * Returns the item props for the site if it has changes
	 */
	public function site(): array|null
	{
		$site = $this->changes->site();

		if ($site?->isAccessible() !== true) {
			return null;
		}

		return $this->item($site);
	}

	/**
	 * Returns the item props for all changed users
	 */
	public function users(): array
	{
		return $this->items($this->changes->users()->filter('isListable', true));
	}
}
