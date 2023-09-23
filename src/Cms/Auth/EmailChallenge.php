<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\User;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Creates and verifies one-time auth codes
 * that are sent via email
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
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
	 * @param \Kirby\Cms\User $user User to generate the code for
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'password-reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @return string The generated and sent code
	 */
	public static function create(User $user, array $options): string
	{
		$code = Str::random(6, 'num');

		// insert a space in the middle for easier readability
		$formatted = substr($code, 0, 3) . ' ' . substr($code, 3, 3);

		// use the login templates for 2FA
		$mode = $options['mode'];
		if ($mode === '2fa') {
			$mode = 'login';
		}

		$kirby = $user->kirby();
		$kirby->email([
			'from' => $kirby->option('auth.challenge.email.from', 'noreply@' . $kirby->url('index', true)->host()),
			'fromName' => $kirby->option('auth.challenge.email.fromName', $kirby->site()->title()),
			'to' => $user,
			'subject' => $kirby->option(
				'auth.challenge.email.subject',
				I18n::translate('login.email.' . $mode . '.subject', null, $user->language())
			),
			'template' => 'auth/' . $mode,
			'data' => [
				'user'    => $user,
				'site'    => $kirby->system()->title(),
				'code'    => $formatted,
				'timeout' => round($options['timeout'] / 60)
			]
		]);

		return $code;
	}
}
