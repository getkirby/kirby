<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\RemoveDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for deleting a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserDeleteDialogController extends UserDialogController
{
	public function load(): Dialog
	{
		$i18nPrefix = $this->user->isLoggedIn() ? 'account' : 'user';

		return new RemoveDialog(
			text: I18n::template($i18nPrefix . '.delete.confirm', [
				'email' => Escape::html($this->user->email())
			])
		);
	}

	public function submit(): array
	{
		$referrer = $this->kirby->panel()->referrer();
		$url      = $this->user->panel()->url(true);

		$this->user->delete();

		// redirect to the users view
		// if the dialog has been opened in the user view
		if ($referrer === $url) {
			$redirect = '/users';
		}

		// logout the user if they deleted themselves
		if ($this->user->isLoggedIn()) {
			$redirect = '/logout';
		}

		return [
			'event'    => 'user.delete',
			'redirect' => $redirect ?? null
		];
	}
}
