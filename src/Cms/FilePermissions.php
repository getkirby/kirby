<?php

namespace Kirby\Cms;

/**
 * FilePermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends ModelPermissions<File>
 * @deprecated 6.0.0 Use `$file->guards()` instead
 */
class FilePermissions extends ModelPermissions
{
	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param File $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->template() ?? '__none__';
	}
}
