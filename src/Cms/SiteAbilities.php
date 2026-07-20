<?php

namespace Kirby\Cms;

/**
 * Abilities for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SiteAbilities extends ModelAbilities
{
	public function __construct(
		protected Site $site
	) {
	}
}
