<?php

namespace Kirby\Cms;

/**
 * PagePermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\ModelPermissions<\Kirby\Cms\Page>
 */
class PagePermissions extends ModelPermissions
{
	protected const string CATEGORY = 'pages';

	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param \Kirby\Cms\Page $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->intendedTemplate()->name();
	}
}
