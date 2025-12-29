<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Ui\Drawer;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserSecurityDrawerController extends DrawerController
{
	public function __construct(
		protected User $user
	) {
	}

	public static function factory(string $id): static
	{
		return new static(user: Find::user($id));
	}

	public function challenges(): array
	{
		return [
			[ 'text' => 'Authenticator app', 'icon' => 'qr-code' ],
			[ 'text' => 'Security keys', 'icon' => 'shield' ]
		];
	}

	public function load(): Drawer
	{
		return new Drawer(
			component: 'k-user-security-drawer',
			icon: 'key',
			title: $this->i18n('security'),
			challenges: $this->challenges(),
			methods: $this->methods()
		);
	}
}
