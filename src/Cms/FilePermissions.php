<?php

namespace Kirby\Cms;

/**
 * FilePermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\ModelPermissions<\Kirby\Cms\File>
 */
class FilePermissions extends ModelPermissions
{
	protected const string CATEGORY = 'files';

	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param \Kirby\Cms\File $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->template() ?? '__none__';
	}

	protected function canChangeTemplate(): bool
	{
		if (count($this->model->blueprints()) <= 1) {
			return false;
		}

		return true;
	}
}
