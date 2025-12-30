<?php

namespace Kirby\Auth;

use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;
use SensitiveParameter;

/**
 * Base class for authentication methods
 *
 * Each method either logs the user in or returns a
 * pending status that expects a follow-up challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Method
{
	public function __construct(
		protected Auth $auth,
		protected array $options = []
	) {
	}

	/**
	 * Attempts to authenticate the given user credentials
	 *
	 * Implementations should either return a logged-in user,
	 * a pending status if a challenge is required.
	 */
	abstract public function authenticate(
		string $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User|Status;

	/**
	 * Returns the identifier of the method (e.g. 'password')
	 */
	public static function type(): string
	{
		return Str::camelToKebab(lcfirst(basename(str_replace(['\\', 'Method'], ['/', ''], static::class))));
	}
}
