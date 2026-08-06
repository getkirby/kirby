<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Validators for all page actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @deprecated 6.0.0 Use `$page->guards()` instead
 */
class PageRules
{
	public static function changeNum(Page $page, int|null $num = null): void
	{
		$page->guards()->ensureExecutable('changeNum', $num);
	}

	public static function changeSlug(Page $page, string $slug): void
	{
		$page->guards()->ensureExecutable('changeSlug', $slug);
	}

	public static function changeStatus(
		Page $page,
		string $status,
		int|null $position = null
	): void {
		$page->guards()->ensureExecutable('changeStatus', $status, $position);
	}

	public static function changeStatusToDraft(Page $page): void
	{
		$page->guards()->ensureExecutable('changeStatusToDraft');
	}

	public static function changeStatusToListed(Page $page, int $position): void
	{
		$page->guards()->ensureExecutable('changeStatusToListed', $position);
	}

	public static function changeStatusToUnlisted(Page $page)
	{
		$page->guards()->ensureExecutable('changeStatusToUnlisted');
	}

	public static function changeTemplate(Page $page, string $template): void
	{
		$page->guards()->ensureExecutable('changeTemplate', $template);
	}

	public static function changeTitle(Page $page, string $title): void
	{
		$page->guards()->ensureExecutable('changeTitle', $title);
	}

	public static function create(Page $page): void
	{
		$page->guards()->ensureExecutable('create');
	}

	public static function delete(Page $page, bool $force = false): void
	{
		$page->guards()->ensureExecutable('delete', $force);
	}

	public static function duplicate(
		Page $page,
		string $slug,
		array $options = []
	): void {
		$page->guards()->ensureExecutable('duplicate', $slug, $options);
	}

	public static function move(Page $page, Site|Page $parent): void
	{
		$page->guards()->ensureExecutable('move', $parent);
	}

	public static function publish(Page $page): void
	{
		$page->guards()->ensureExecutable('publish');
	}

	public static function update(Page $page, array $content = []): void
	{
		$page->guards()->ensureExecutable('update', $content);
	}

	public static function validateSlugLength(string $slug): void
	{
		$slugLength = Str::length($slug);

		if ($slugLength === 0) {
			throw new InvalidArgumentException(key: 'page.slug.invalid');
		}

		if ($slugsMaxlength = App::instance()->option('slugs.maxlength', 255)) {
			$maxlength = (int)$slugsMaxlength;

			if ($slugLength > $maxlength) {
				throw new InvalidArgumentException(
					key: 'page.slug.maxlength',
					data: ['length' => $maxlength]
				);
			}
		}
	}

	public static function validateTitleLength(string $title): void
	{
		if (Str::length($title) === 0) {
			throw new InvalidArgumentException(key: 'page.changeTitle.empty');
		}
	}
}
