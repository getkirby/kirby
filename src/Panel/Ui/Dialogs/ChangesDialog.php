<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Collection;
use Kirby\Content\Changes;
use Kirby\Panel\Ui\Dialog;

/**
 * Panel dialog listing content changes in
 * pages, users and files
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class ChangesDialog extends Dialog
{
	public function __construct(
		protected Changes $changes = new Changes()
	) {
		parent::__construct(
			component: 'k-changes-dialog'
		);
	}

	/**
	 * Returns the item props for all changed files
	 */
	public function files(): array
	{
		return $this->items($this->changes->files());
	}

	/**
	 * Helper method to return item props for the given models
	 */
	public function items(Collection $models): array
	{
		return $models->values(
			fn ($model) => $model->panel()->dropdownOption()
		);
	}

	/**
	 * Returns the item props for all changed pages
	 */
	public function pages(): array
	{
		return $this->items($this->changes->pages());
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'files' => $this->files(),
			'pages' => $this->pages(),
			'users' => $this->users(),
		];
	}

	public function render(): array
	{
		if ($this->changes->cacheExists() === false) {
			$this->changes->generateCache();
		}

		return parent::render();
	}

	/**
	 * Returns the item props for all changed users
	 */
	public function users(): array
	{
		return $this->items($this->changes->users());
	}
}
