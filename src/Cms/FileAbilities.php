<?php

namespace Kirby\Cms;

/**
 * Abilities for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileAbilities extends ModelAbilities
{
	public function __construct(
		protected File $file
	) {
	}

	public function changeTemplate(): bool
	{
		if (count($this->file->blueprints()) <= 1) {
			return false;
		}

		return true;
	}
}
