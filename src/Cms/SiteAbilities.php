<?php

namespace Kirby\Cms;

/**
 * Site Abilities
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
