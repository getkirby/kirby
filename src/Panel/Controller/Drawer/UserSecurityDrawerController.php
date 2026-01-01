<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\Auth;
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
		parent::__construct();
	}

	public function auth(): Auth
	{
		return $this->kirby->auth();
	}

	public function challenges(): array
	{
		$methods = $this->auth()->methods();

		if ($methods->hasAnyAvailableUsingChallenges() === false) {
			return [];
		}

		$buttons    = [];
		$challenges = $this->auth()->challenges();

		foreach ($challenges->enabled() as $type) {
			$challenge = $challenges->class($type);
			$buttons = [...$buttons, ...$challenge::settings($this->user)];
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
			component:  'k-user-security-drawer',
			icon:       'lock',
			title:      $this->i18n('security'),
			challenges: $this->challenges(),
			methods:    $this->methods()
		);
	}

	public function methods(): array
	{
		$buttons = [
			// always feature email as it is needed for most methods
			[
				'text'     => $this->i18n('email'),
				'icon'     => 'email',
				'dialog'   => $this->user->panel()->url(true) . '/changeEmail',
				'disabled' => !$this->user->permissions()->can('changeEmail'),
			]
		];

		$methods = $this->auth()->methods()->available();

		foreach ($methods as $type => $options) {
			$method  = $this->auth()->methods()->class($type);
			$buttons = [...$buttons, ...$method::settings($this->user)];
		}

		return $buttons;
	}
}
