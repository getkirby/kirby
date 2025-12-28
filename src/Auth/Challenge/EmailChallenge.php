<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Cms\User;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Creates and verifies one-time auth codes
 * that are sent via email
 *
 * @package   Kirby Auth
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class EmailChallenge extends Challenge
{
	/**
	 * Checks whether the challenge is available
	 * for the passed user and purpose
	 *
	 * @param \Kirby\Cms\User $user User the code will be generated for
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 */
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'password-reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @return string The generated and sent code
	 */
	public function create(array $options): string
	{
		$code = Str::random(6, 'num');

		$this->send(
			// insert a space in the middle for easier readability
			code: substr($code, 0, 3) . ' ' . substr($code, 3, 3)
		);

		return $code;
	}

	/**
	 * Returns sender email address for the email
	 */
	protected function from(): string
	{
		return $this->kirby->option(
			'auth.challenge.email.from',
			'noreply@' . $this->kirby->url('index', true)->host()
		);
	}

	/**
	 * Returns sender name for the email
	 */
	protected function fromName(): string
	{
		return $this->kirby->option(
			'auth.challenge.email.fromName',
			$this->kirby->site()->title()
		);
	}

	/**
	 * Sends the email with the code to the user
	 */
	protected function send(string $code): void
	{
		// use the login templates for 2FA
		$mode = match ($this->mode) {
			'2fa'   => 'login',
			default => $this->mode
		};

		$this->kirby->email([
			'from'     => $this->from(),
			'fromName' => $this->fromName(),
			'to'       => $this->user,
			'subject'  => $this->subject($mode),
			'template' => 'auth/' . $mode,
			'data' => [
				'user'    => $this->user,
				'site'    => $this->kirby->system()->title(),
				'code'    => $code,
				'timeout' => round($this->timeout / 60)
			]
		]);
	}

	/**
	 * Returns subject for the email
	 */
	protected function subject(string $mode): string
	{
		return $this->kirby->option(
			'auth.challenge.email.subject',
			I18n::translate(
				key: 'login.email.' . $mode . '.subject',
				locale: $this->user->language()
			)
		);
	}
}
