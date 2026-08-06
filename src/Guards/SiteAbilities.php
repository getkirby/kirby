<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Site;

/**
 * Abilities for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SiteAbilities extends ModelAbilities
{
	/**
	 * @var Site
	 */
	protected Model $model;

	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		parent::error(
			key: 'site.' . $key,
			data: $data,
			details: $details
		);
	}
}
