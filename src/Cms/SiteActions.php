<?php

namespace Kirby\Cms;

use Closure;

/**
 * SiteActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait SiteActions
{
	/**
	 * Commits a site action, by following these steps
	 *
	 * 1. applies the `before` hook
	 * 2. checks the action rules
	 * 3. commits the store action
	 * 4. applies the `after` hook
	 * 5. returns the result
	 */
	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
		$commit = new ModelCommit(
			model: $this,
			action: $action
		);

		return $commit->call($arguments, $callback);
	}

	/**
	 * Change the site title
	 */
	public function changeTitle(
		string $title,
		string|null $languageCode = null
	): static {
		// if the `$languageCode` argument is not set and is not the default language
		// the `$languageCode` argument is sent as the current language
		if (
			$languageCode === null &&
			$language = $this->kirby()->language()
		) {
			if ($language->isDefault() === false) {
				$languageCode = $language->code();
			}
		}

		$arguments = ['site' => $this, 'title' => trim($title), 'languageCode' => $languageCode];

		return $this->commit(
			'changeTitle',
			$arguments,
			fn ($site, $title, $languageCode) => $site->save(['title' => $title], $languageCode)
		);
	}

	/**
	 * Creates a main page
	 */
	public function createChild(array $props): Page
	{
		return Page::create([
			...$props,
			'url'    => null,
			'num'    => null,
			'parent' => null,
			'site'   => $this,
		]);
	}

	/**
	 * Clean internal caches
	 *
	 * @return $this
	 */
	public function purge(): static
	{
		parent::purge();

		$this->blueprint         = null;
		$this->children          = null;
		$this->childrenAndDrafts = null;
		$this->drafts            = null;
		$this->files             = null;
		$this->inventory         = null;

		return $this;
	}
}
