<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;
use SensitiveParameter;

/**
 * Base class for authentication methods
 *
 * Each method decides whether it can handle the current
 * login flow and either logs the user in or returns a
 * pending status that expects a follow-up challenge.
 *
 * @package Kirby Auth
 * @since   6.0.0
 */
abstract class Method
{
	protected App $kirby;

	public function __construct(
		protected array $options = []
	) {
		$this->kirby = App::instance();
	}

	/**
	 * Attempts to authenticate the given user credentials
	 *
	 * Implementations should either return a logged-in user,
	 * a pending status if a challenge is required, or `null`
	 * if the method cannot handle the provided input.
	 */
	abstract public function attempt(
		string $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): User|Status|null;

	protected function auth(): Auth
	{
		return $this->kirby->auth();
	}

	abstract public static function form(): string;

	/**
	 * Checks whether the method is available for the current setup
	 */
	public static function isAvailable(string $mode = 'login'): bool
	{
		return true;
	}

	public static function settings(User $user): array
	{
		return [];
	}

	/**
	 * Returns the identifier of the method (e.g. 'password')
	 */
	public static function type(): string
	{
		return Str::camelToKebab(lcfirst(basename(str_replace(['\\', 'Method'], ['/', ''], static::class))));
	}
}
