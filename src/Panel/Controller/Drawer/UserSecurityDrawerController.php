<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Challenge;
use Kirby\Cms\Auth;
use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Ui\Drawer;
use Kirby\Toolkit\A;

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
		parent::__construct();
	}

	public function auth(): Auth
	{
		return $this->kirby->auth();
	}

	public function challenges(): array
	{
		$buttons = [];

		$challenges = $this->auth()->challenges()->enabled();

		foreach ($challenges as $challenge) {
			$challenge = Challenge::handler($challenge);
			$buttons   = [...$buttons, ...A::map(
				$challenge::settings($this->user),
				fn ($button) => [
					...$button,
					'theme' => $challenge::isAvailable($this->user) ? 'info-icon' : 'passive-icon'
				]
			)];
		}

		return $buttons;
	}

	public static function factory(string $id): static
	{
		return new static(user: Find::user($id));
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

	public function methods(): array
	{
		$buttons = [
			[
				'text'     => 'Email',
				'icon'     => 'email',
				'disabled' => !$this->user->permissions()->can('changeEmail'),
				'dialog'   => $this->user->panel()->url(true) . '/changeEmail'
			]
		];

		$methods = array_keys($this->auth()->methods()->available());

		foreach ($methods as $method) {
			$method  = $this->auth()->methods()->handler($method);
			$buttons = [...$buttons, ...$method::settings($this->user)];
		}

		return $buttons;
	}
}
