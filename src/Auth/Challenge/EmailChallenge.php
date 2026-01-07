<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use SensitiveParameter;

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
	 * Generates a random one-time auth code and
	 * returns that code for later verification
	 *
	 * @return string The generated and sent code
	 */
	public function create(): Pending
	{
		$code = Str::random(6, 'num');

		$this->send(
			// insert a space in the middle for easier readability
			code: substr($code, 0, 3) . ' ' . substr($code, 3, 3)
		);

		return new Pending(
			secret: User::hashPassword($code)
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
			'from'     => $this->senderFrom(),
			'fromName' => $this->senderFromName(),
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
	 * Returns sender email address for the email
	 */
	protected function senderFrom(): string
	{
		return $this->kirby->option(
			'auth.challenge.email.from',
			'noreply@' . $this->kirby->url('index', true)->host()
		);
	}

	/**
	 * Returns sender name for the email
	 */
	protected function senderFromName(): string
	{
		return $this->kirby->option(
			'auth.challenge.email.fromName',
			$this->kirby->site()->title()
		);
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

	/**
	 * Verifies the provided input against the code
	 * that was returned from the `create()` method
	 */
	public function verify(
		#[SensitiveParameter]
		mixed $input,
		Pending $data
	): bool {
		// normalize the formatting in the user-provided code
		$input = str_replace(' ', '', $input);
		$hash  = $data->secret();

		if (is_string($hash) !== true) {
			return false;
		}

		return password_verify($input, $hash);
	}
}
