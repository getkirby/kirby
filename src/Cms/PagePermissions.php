<?php

namespace Kirby\Cms;

/**
 * PagePermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends ModelPermissions<Page>
 * @deprecated 6.0.0 Use `$page->guards()` instead
 */
class PagePermissions extends ModelPermissions
{
	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param Page $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->intendedTemplate()->name();
	}
}
