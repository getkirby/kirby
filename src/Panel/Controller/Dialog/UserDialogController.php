<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Panel\Controller\DialogController;

/**
 * Controls a Panel dialog for a specific user
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
abstract class UserDialogController extends DialogController
{
	public function __construct(
		public User $user
	) {
		parent::__construct();
	}

	public static function factory(string $id): static
	{
		return new static(user: Find::user($id));
	}
}
