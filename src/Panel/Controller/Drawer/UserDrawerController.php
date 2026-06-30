<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Panel\Controller\DrawerController;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class UserDrawerController extends DrawerController
{
	public function __construct(
		protected User $user
	) {
		parent::__construct();
	}

	public static function factory(string $id): static
	{
		return new static(user: Find::user($id));
	}
}
