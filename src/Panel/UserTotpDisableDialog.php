<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
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
class UserTotpDisableDialog
{
	public App $kirby;
	public User $user;

	public function __construct(
		string|null $id = null
	) {
		$this->kirby = App::instance();
		$this->user  = $id ? Find::user($id) : $this->kirby->user();
	}

	/**
	 * Returns the Panel dialog state when opening the dialog
	 */
	public function load(): array
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

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::template('login.totp.disable.admin', ['user' => Escape::html($name)]),
					'submitButton' => $submitBtn,
				]
			];
		}

		// everybody else
		return [
			'component' => 'k-form-dialog',
			'props' => [
				'fields' => [
					'password' => [
						'type'     => 'password',
						'required' => true,
						'counter'  => false,
						'label'    => I18n::translate('login.totp.disable.label'),
						'help'     => I18n::translate('login.totp.disable.help'),
					]
				],
				'submitButton' => $submitBtn,
			]
		];
	}

	/**
	 * Removes the user's TOTP secret when the dialog is submitted
	 */
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
