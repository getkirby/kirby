<?php

namespace Kirby\Guards;

use Kirby\Cms\Site;
use Kirby\Cms\User;

/**
 * Bundles all guards for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelGuards<Site, SiteAbilities, SitePermissions, SiteValidators>
 */
class SiteGuards extends ModelGuards
{
	public function __construct(
		Site $model,
		User $user
	) {
		parent::__construct($model, $user);

		$this->abilities   = new SiteAbilities($model, $user);
		$this->permissions = new SitePermissions($model, $user);
		$this->validators  = new SiteValidators($model, $user);
	}
}
