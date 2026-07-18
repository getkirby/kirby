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

	protected function canChangeTemplate(): bool
	{
		if (count($this->model->blueprints()) <= 1) {
			return false;
		}

		return true;
	}
}
