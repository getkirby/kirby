<?php

namespace Kirby\Auth;

use Kirby\Cms\User;
use Kirby\Panel\Ui\Component;
use Kirby\Toolkit\HasI18n;
use SensitiveParameter;

/**
 * Base class for authentication methods
 *
 * Each method either logs the user in or returns a
 * pending status that expects a follow-up challenge
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Method
{
	use HasI18n;

	/**
	 * Must not throw or have side effects, as methods
	 * are also created to check if they are enabled
	 */
	public function __construct(
		protected Auth $auth,
		protected string $type,
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
		string|null $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User|Status;

	/**
	 * Returns the form component definition for this method's login step
	 * @codeCoverageIgnore
	 */
	public function form(): Component|array
	{
		return [];
	}

	/**
	 * Returns an icon name used to represent
	 * the method, e.g. on the login view
	 */
	public function icon(): string
	{
		return 'key';
	}

	/**
	 * Checks if this method can be used in the current context
	 */
	public function isEnabled(): bool
	{
		return true;
	}

	/**
	 * Returns the label used to represent
	 * the method, e.g. on the login view
	 */
	public function label(): string
	{
		return static::i18n('login.method.' . $this->type() . '.label');
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
	public function settings(User $user): array
	{
		return [];
	}

	/**
	 * Returns the identifier the method
	 * is registered with (e.g. 'password')
	 */
	public function type(): string
	{
		return $this->type;
	}
}
