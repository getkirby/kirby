<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Site;
use Kirby\Cms\User;

/**
 * Bundles all guards for the `$site` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @method SiteAbilities abilities()
 * @method SitePermissions permissions()
 * @method SiteValidators validators()
 */
class SiteGuards extends ModelGuards
{
	/**
	 * @var Site
	 */
	protected Model $model;

	/**
	 * @var SiteAbilities
	 */
	protected ModelAbilities $abilities;

	/**
	 * @var SitePermissions
	 */
	protected ModelPermissions $permissions;

	/**
	 * @var SiteValidators
	 */
	protected ModelValidators $validators;

	public function __construct(
		Site $model,
		User $user
	) {
		parent::__construct(
			abilities: new SiteAbilities(
				model: $model,
				user: $user
			),
			model: $model,
			permissions: new SitePermissions(
				model: $model,
				user: $user
			),
			user: $user,
			validators: new SiteValidators(
				model: $model,
				user: $user
			),
		);
	}
}
