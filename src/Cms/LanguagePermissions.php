<?php

namespace Kirby\Cms;

/**
 * LanguagePermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends ModelPermissions<Language>
 */
class LanguagePermissions extends ModelPermissions
{
	protected const string CATEGORY = 'languages';

	protected function canDelete(): bool
	{
		return $this->model->isDeletable() === true;
	}
}
