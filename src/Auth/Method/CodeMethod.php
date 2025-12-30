<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth\Status;

/**
 * Passwordless login via one-time code
 * or any other available challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class CodeMethod extends Method
{
	public function authenticate(
		string $email,
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
}
