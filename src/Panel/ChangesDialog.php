<?php

namespace Kirby\Panel;

use Kirby\Cms\Collection;
use Kirby\Content\Changes;

/**
 * Manages the Panel dialog for content changes in
 * pages, users and files
 * @since 5.0.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ChangesDialog
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
	 * Returns the backend full definition for dialog
	 */
	public function load(): array
	{
		if ($this->changes->cacheExists() === false) {
			$this->changes->generateCache();
		}

		return [
			'component' => 'k-changes-dialog',
			'props'     => [
				'files' => $this->files(),
				'pages' => $this->pages(),
				'users' => $this->users(),
			]
		];
	}

	/**
	 * Returns the item props for all changed pages
	 */
	public function pages(): array
	{
		return $this->items($this->changes->pages());
	}

	/**
	 * Returns the item props for all changed users
	 */
	public function users(): array
	{
		return $this->items($this->changes->users());
	}
}
