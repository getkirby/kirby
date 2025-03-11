<?php

namespace Kirby\Cms;

/**
 * The ModelState class is used to update app-wide model states.
 * It's mainly used in the `ModelCommit` class to update the
 * state of the given model after the action has been
 * executed.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ModelState
{
	/**
	 * Updates the state of the given model.
	 */
	public static function update(
		string $method,
		ModelWithContent $current,
		ModelWithContent|bool|null $next = null,
		ModelWithContent|Site|null $parent = null
	): void {
		// normalize the method
		$method = match ($method) {
			'append', 'create' => 'append',
			'remove', 'delete' => 'remove',
			'duplicate'        => false, // The models need to take care of this
			default            => 'update'
		};

		if ($method === false) {
			return;
		}

		match (true) {
			$current instanceof File => static::updateFile($method, $current, $next),
			$current instanceof Page => static::updatePage($method, $current, $next, $parent),
			$current instanceof Site => static::updateSite($current, $next),
			$current instanceof User => static::updateUser($method, $current, $next),
		};
	}

	/**
	 * Updates the state of the given file.
	 */
	protected static function updateFile(
		string $method,
		File $current,
		File|bool|null $next = null
	): void {
		$next = $next instanceof File ? $next : $current;

		// update the files collection
		$next->parent()->files()->$method($next);
	}

	/**
	 * Updates the state of the given page.
	 */
	protected static function updatePage(
		string $method,
		Page $current,
		Page|bool|null $next = null,
		Page|Site|null $parent = null
	): void {
		$next     = $next instanceof Page ? $next : $current;
		$parent ??= $next->parentModel();

		if ($next->isDraft() === true) {
			$parent->drafts()->$method($next);
		} else {
			$parent->children()->$method($next);
		}

		// update the childrenAndDrafts() cache
		$parent->childrenAndDrafts()->$method($next);
	}

	/**
	 * Updates the state of the given site.
	 */
	protected static function updateSite(
		Site $current,
		Site|null $next = null
	): void {
		App::instance()->setSite($next ?? $current);
	}

	/**
	 * Updates the state of the given user.
	 */
	protected static function updateUser(
		string $method,
		User $current,
		User|bool|null $next = null
	): void {
		$next = $next instanceof User ? $next : $current;

		// update the users collection
		App::instance()->users()->$method($next);
	}
}
