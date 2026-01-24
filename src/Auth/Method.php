<?php

namespace Kirby\Auth;

use Kirby\Cms\User;
use Kirby\Toolkit\HasI18n;
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
	use HasI18n;

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
	 * Returns an icon name used to represent
	 * the method, e.g. on the login view
	 */
	public static function icon(): string
	{
		return 'key';
	}

	/**
	 * Checks if this method can be used in the current context
	 */
	public static function isEnabled(
		Auth $auth,
		array $options = []
	): bool {
		return true;
	}

	/**
	 * Checks if this method uses challenges
	 */
	public static function isUsingChallenges(
		Auth $auth,
		array $options = []
	): bool {
		return false;
	}

	/**
	 * Returns the config options for this method
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Returns an array of buttons that will be
	 * used as entries in the user security settings drawer
	 * @codeCoverageIgnore
	 */
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
