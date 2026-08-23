<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Site;
use Kirby\Toolkit\Str;

/**
 * Validators for the input of `$site` actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SiteValidators extends ModelValidators
{
	/**
	 * @var Site
	 */
	protected Model $model;

	/**
	 * Validates the input of the `changeTitle` action
	 */
	protected function ensureToChangeTitle(string $title): void
	{
		$this->validateTitle($title);
	}

	/**
	 * Validates that the site title is not empty
	 */
	public function validateTitle(string $title): void
	{
		if (Str::length($title) === 0) {
			$this->error(
				key: 'site.changeTitle.empty'
			);
		}
	}
}
