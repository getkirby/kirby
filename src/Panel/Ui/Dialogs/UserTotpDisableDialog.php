<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Ui\Renderable;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Manages the Panel dialog to disable TOTP auth for a user
 * @since 4.0.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserTotpDisableDialog extends Renderable
{
	use IsForUser;

	public App $kirby;

	public function __construct(
		public User $user
	) {
		$this->kirby = App::instance();
	}

	public function render(): array
	{
		$currentUser = $this->kirby->user();
		$submitBtn   = [
			'text'  => I18n::translate('disable'),
			'icon'  => 'protected',
			'theme' => 'negative'
		];

		// admins can disable TOTP for other users without
		// entering their password (but not for themselves)
		if (
			$currentUser->isAdmin() === true &&
			$currentUser->is($this->user) === false
		) {
			$name = $this->user->name()->or($this->user->email());

			return (new RemoveDialog(
				text: I18n::template('login.totp.disable.admin', [
					'user' => Escape::html($name)
				]),
				submitButton: $submitBtn
			))->render();
		}

		// everybody else
		return (new FormDialog(
			fields: [
				'password' => [
					'type'     => 'password',
					'required' => true,
					'counter'  => false,
					'label'    => I18n::translate('login.totp.disable.label'),
					'help'     => I18n::translate('login.totp.disable.help'),
				]
			],
			submitButton: $submitBtn
		))->render();
	}

	public function submit(): array
	{
		$password = $this->kirby->request()->get('password');

		try {
			if ($this->kirby->user()->is($this->user) === true) {
				$this->user->validatePassword($password);
			} elseif ($this->kirby->user()->isAdmin() === false) {
				throw new PermissionException(
					message: 'You are not allowed to disable TOTP for other users'
				);
			}

			// Remove the TOTP secret from the account
			$this->user->changeTotp(null);

			return [
				'message' => I18n::translate('login.totp.disable.success')
			];

		} catch (InvalidArgumentException $e) {
			// Catch and re-throw exception so that any
			// Unauthenticated exception for incorrect passwords
			// does not trigger a logout
			throw new InvalidArgumentException(
				key: $e->getKey(),
				data: $e->getData(),
				fallback: $e->getMessage(),
				previous: $e
			);
		}
	}
}
