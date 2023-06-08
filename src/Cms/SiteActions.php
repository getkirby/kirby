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
	 * 1. checks the action rules
	 * 2. sends the before hook
	 * 3. commits the store action
	 * 4. sends the after hook
	 * 5. returns the result
	 */
	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
		$old            = $this->hardcopy();
		$kirby          = $this->kirby();
		$argumentValues = array_values($arguments);

		$this->rules()->$action(...$argumentValues);
		$kirby->trigger('site.' . $action . ':before', $arguments);

		$result = $callback(...$argumentValues);

		$kirby->trigger('site.' . $action . ':after', ['newSite' => $result, 'oldSite' => $old]);

		$kirby->cache('pages')->flush();
		return $result;
	}

	/**
	 * Change the site title
	 */
	public function changeTitle(
		string $title,
		string $languageCode = null
	): static {
		$site      = $this;
		$title     = trim($title);
		$arguments = compact('site', 'title', 'languageCode');

		return $this->commit('changeTitle', $arguments, function ($site, $title, $languageCode) {
			return $site->save(['title' => $title], $languageCode);
		});
	}

	/**
	 * Creates a main page
	 */
	public function createChild(array $props): Page
	{
		$props = array_merge($props, [
			'url'    => null,
			'num'    => null,
			'parent' => null,
			'site'   => $this,
		]);

		return Page::create($props);
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
