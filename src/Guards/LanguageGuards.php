<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\Model;
use Kirby\Cms\User;

/**
 * Bundles all guards for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @method LanguageAbilities abilities()
 * @method LanguagePermissions permissions()
 * @method LanguageValidators validators()
 */
class LanguageGuards extends ModelGuards
{
	/**
	 * @var Language
	 */
	protected Model $model;

	/**
	 * @var LanguageAbilities
	 */
	protected ModelAbilities $abilities;

	/**
	 * @var LanguagePermissions
	 */
	protected ModelPermissions $permissions;

	/**
	 * @var LanguageValidators
	 */
	protected ModelValidators $validators;

	public function __construct(
		Language $model,
		User $user
	) {
		parent::__construct(
			abilities: new LanguageAbilities(
				model: $model,
				user: $user
			),
			model: $model,
			permissions: new LanguagePermissions(
				model: $model,
				user: $user
			),
			user: $user,
			validators: new LanguageValidators(
				model: $model,
				user: $user
			),
		);
	}
}
