<?php

namespace Kirby\Panel;

use Kirby\Cms\Collection;
use Kirby\Cms\Languages;
use Kirby\Content\Changes;
use Kirby\Content\VersionId;
use Kirby\Toolkit\Str;

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
	protected Changes $changes;

	public function __construct()
	{
		$this->changes = new Changes();
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
		$languages = Languages::ensure();
		$items     = [];

		foreach ($models as $model) {
			foreach ($languages as $language) {
				if ($model->version(VersionId::changes())->exists($language) === true) {
					$item = $model->panel()->dropdownOption();

					if ($languages->count() > 1) {
						$item['info']  = Str::upper($language->code());
						$item['link'] .= '?language=' . $language->code();
					}

					$items[] = $item;
				}
			}
		}

		return $items;
	}

	/**
	 * Returns the backend full definition for dialog
	 */
	public function load(): array
	{
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
