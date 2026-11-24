<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\User;

/**
 * Bundles all guards for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelGuards<Language, LanguageAbilities, LanguagePermissions, LanguageValidators>
 */
class LanguageGuards extends ModelGuards
{
	public function __construct(
		Language $model,
		User $user
	) {
		parent::__construct($model, $user);

		$this->abilities   = new LanguageAbilities($model, $user);
		$this->permissions = new LanguagePermissions($model, $user);
		$this->validators  = new LanguageValidators($model, $user);
	}
}
