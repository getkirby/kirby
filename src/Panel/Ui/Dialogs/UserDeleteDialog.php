<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog to delete a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class UserDeleteDialog extends RemoveDialog
{
	use IsForUser;

	public function __construct(
		public User $user
	) {
		$i18nPrefix = $this->user->isLoggedIn() ? 'account' : 'user';

		parent::__construct(
			text: I18n::template($i18nPrefix . '.delete.confirm', [
				'email' => Escape::html($user->email())
			])
		);
	}

	public function submit(): array
	{
		$referrer = Panel::referrer();
		$url      = $this->user->panel()->url(true);

		$response = [
			'event' => 'user.delete'
		];

		// redirect to the users view
		// if the dialog has been opened in the user view
		if ($referrer === $url) {
			$response['redirect'] = '/users';
		}

		// logout the user if they deleted themselves
		// (this check needs to happen before the actual deletion)
		if ($this->user->isLoggedIn() === true) {
			$response['redirect'] = '/logout';
		}

		$this->user->delete();

		return $response;
	}
}
