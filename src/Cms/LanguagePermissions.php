<?php

namespace Kirby\Cms;

/**
 * LanguagePermissions
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguagePermissions extends ModelPermissions
{
	protected const CATEGORY = 'languages';

	protected function canDelete(): bool
	{
		return $this->model->isDeletable() === true;
	}
}
