<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Auth;
use Kirby\Panel\Ui\Button;
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
class UserSecurityDrawerController extends UserDrawerController
{
	public function auth(): Auth
	{
		return $this->kirby->auth();
	}

	public function challenges(): array
	{
		$methods = $this->auth()->methods();

		if ($methods->hasAnyUsingChallenges() === false) {
			return [];
		}

		$buttons    = [];
		$challenges = $this->auth()->challenges();

		foreach ($challenges->enabled() as $type) {
			$challenge = $challenges->class($type);
			$buttons = [
				...$buttons,
				...A::map(
					$challenge::settings($this->user),
					fn (Button $button) => $button->render()['props']
				)
			];
		}

		return $buttons;
	}

	public function load(): Drawer
	{
		return new Drawer(
			component:  'k-user-security-drawer',
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

		$methods = $this->auth()->methods()->enabled();

		foreach ($methods as $type => $options) {
			$method  = $this->auth()->methods()->class($type);
			$buttons = [
				...$buttons,
				...A::map(
					$method::settings($this->user),
					fn (Button $button) => $button->render()['props']
				)
			];
		}

		return $buttons;
	}
}
