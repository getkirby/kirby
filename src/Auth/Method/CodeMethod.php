<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use SensitiveParameter;

/**
 * Passwordless login via one-time code
 * or any other available challenge
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class CodeMethod extends Method
{
	public function authenticate(
		string|null $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): Status {
		// no password required; directly create challenge
		return $this->auth->createChallenge(
			mode: 'login',
			email: $email,
			long:  $long,
		);
	}

	public function form(): Component
	{
		return new Component(
			component: 'k-login-code-method-form',
			submit: [
				'icon'  => $this->icon(),
				'label' => static::i18n('login'),
			],
		);
	}

	public function icon(): string
	{
		return 'hashtag';
	}

	public function isEnabled(): bool
	{
		// don't allow to circumvent 2FA by 1FA code method
		$this->isWithoutAny2FA();

		// only one code-based mode can be active at once
		$this->isWithoutPasswordReset();

		return true;
	}

	/**
	 * Don't allow to circumvent 2FA by 1FA code method
	 */
	protected function isWithoutAny2FA(): bool
	{
		if ($this->auth->methods()->hasAnyRequiring2FA() === true) {
			throw new InvalidArgumentException(
				message: 'The "' . $this->type() . '" login method cannot be enabled when 2FA is required'
			);
		}

		return true;
	}

	/**
	 * Only one code-based mode can be active at once
	 */
	protected function isWithoutPasswordReset(): bool
	{
		if (array_key_exists('password-reset', $this->auth->methods()->config()) === true) {
			throw new InvalidArgumentException(
				message: 'The "code" and "password-reset" login methods cannot be enabled together'
			);
		}

		return true;
	}

	public function settings(User $user): array
	{
		return [
			new Button(
				icon: $this->icon(),
				text: $this->label(),
				drawer: $user->panel()->url(true) . '/security/method/code'
			)
		];
	}
}
