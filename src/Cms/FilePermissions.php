<?php

namespace Kirby\Cms;

/**
 * FilePermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FilePermissions extends ModelPermissions
{
	protected const CATEGORY = 'files';

	/**
	 * Used to cache once determined permissions in memory
	 */
	protected static function cacheKey(ModelWithContent|Language $model): string
	{
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
