<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Challenge\EmailChallenge;
use Kirby\Cms\User;
use Kirby\Cms\UserRules;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Drawer;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserEmailChallengeDrawerController extends UserCredentialDrawerController
{
	public function __construct(User $user)
	{
		parent::__construct($user, 'email');

		// ensure user has the necessary permissions
		UserRules::changeSecret($user, 'email', null);
	}

	protected function create(): User
	{
		parent::create();

		// completing the challenge proves that the address is
		// actually reachable, so that nobody can lock themselves
		// out of their account with undeliverable emails
		$this->authorizeCurrentUser();

		return $this->user->changeSecret('email', true);
	}

	protected function isEnabled(): bool
	{
		return $this->user->secret('email') === true;
	}

	public function load(): Drawer
	{
		return new Drawer(
			component: 'k-user-email-challenge-drawer',
			icon:      EmailChallenge::icon(),
			title:     $this->i18n('login.challenge.email.label'),
			isAccount: $this->isCurrentUser(),
			isEnabled: $this->isEnabled(),
			size:      'tiny',
			user:      $this->user->panel()->info()
		);
	}

	protected function remove(): User
	{
		$this->authorize();
		return $this->user->changeSecret('email', null);
	}

	/**
	 * Sends a one-time code to the user's email address and
	 * remembers it for the following create/remove action.
	 */
	protected function send(): User
	{
		$this->authorization();
		return $this->user;
	}

	public function submit(): bool
	{
		$this->user = match ($action = $this->request->get('action')) {
			'code'   => $this->send(),
			'create' => $this->create(),
			'remove' => $this->remove(),
			default  => throw new InvalidArgumentException(
				message: 'Invalid action: ' . $action
			)
		};

		return true;
	}

}
