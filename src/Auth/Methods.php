<?php

namespace Kirby\Auth;

use Kirby\Api\Api;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;

/**
 * Handler for all auth methods
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Methods
{
	/**
	 * Available auth method classes
	 * from the core and plugins
	 */
	public static array $methods = [];

	protected $available;
	protected $enabled;

	public function __construct(
		protected Auth $auth,
		protected App $kirby
	) {
	}

	/**
	 * Authenticates via the specific auth method
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If auth method type does not exists or is not available
	 */
	public function authenticate(
		string $type,
		string $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		$method = $this->get($type);

		if (
			$method === null ||
			$method::isAvailable($this->auth, $method->options()) === false) {
			throw new InvalidArgumentException(
				message: 'Auth method "' . $type . '" is not available'
			);
		}

		return $method->authenticate($email, $password, $long);
	}

	/**
	 * @internal
	 * @todo Refactor/remove when refactoring login view
	 */
	public function authenticateApiRequest(Api $api): User|Status
	{
		$email    = $api->requestBody('email');
		$long     = $api->requestBody('long');
		$password = $api->requestBody('password');

		$method = match (true) {
			$password !== ''  || $password === null => 'password',
			$this->hasAvailable('code')             => 'code',
			$this->hasAvailable('password-reset')   => 'password-reset',
			default => throw new InvalidArgumentException(
				message: 'Login without password is not enabled'
			)
		};

		return $this->auth->authenticate(
			method:   $method,
			email:    $email,
			password: $password,
			long:     $long
		);
	}

	/**
	 * Returns enabled methods that are usable
	 * in the current context
	 */
	public function available(): array
	{
		if (isset($this->available) === true) {
			return $this->available; // @codeCoverageIgnore
		}

		$available = [];

		foreach ($this->enabled() as $type => $options) {
			$class = $this->class($type);

			if ($class::isAvailable($this->auth, $options) === true) {
				$available[$type] = $options;
			}
		}

		return $this->available = $available;
	}

	/**
	 * Returns the auth method class for the provided type
	 */
	public function class(string $type): string
	{
		if (
			($class = static::$methods[$type] ?? null) &&
			is_subclass_of($class, Method::class) === true
		) {
			return $class;
		}

		throw new NotFoundException(
			message: 'No auth method class for: ' . $type
		);
	}

	/**
	 * Returns normalized array of enabled/configured methods
	 * by the `auth.methods` config option
	 */
	public function enabled(): array
	{
		if (isset($this->enabled) === true) {
			return $this->enabled; // @codeCoverageIgnore
		}

		$default = ['password' => []];
		$methods = A::wrap($this->kirby->option('auth.methods', $default));

		// normalize the syntax variants
		$normalized = [];

		foreach ($methods as $key => $value) {
			if (is_int($key) === true) {
				// ['password']
				$normalized[$value] = [];
			} elseif ($value === true) {
				// ['password' => true]
				$normalized[$key] = [];
			} else {
				// ['password' => [...]]
				$normalized[$key] = $value;
			}
		}

		return $this->enabled = $normalized;
	}

	/**
	 * Returns the first available auth method
	 * for the current context
	 */
	public function firstAvailable(): Method|null
	{
		$available = $this->available();
		$type      = array_key_first($available);
		return $type ? $this->get($type) : null;
	}

	/**
	 * Returns an instance of the requested auth method.
	 * (This is based on the config. You might need to check
	 * yourself if the method should be available in your context)
	 */
	public function get(string $type): Method|null
	{
		if ($this->has($type) === false) {
			return null;
		}

		$method = $this->class($type);

		return new $method(
			auth:    $this->auth,
			options: $this->enabled()[$type] ?? []
		);
	}

	/**
	 * Checks if the method type is enabled/configured
	 */
	public function has(string $type): bool
	{
		return in_array($type, array_keys($this->enabled()), true);
	}

	/**
	 * Checks if any available method is using challenges
	 */
	public function hasAnyAvailableUsingChallenges(): bool
	{
		foreach ($this->available() as $method => $options) {
			$class = $this->class($method);

			if ($class::isUsingChallenges($this->auth, $options) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if any enabled/configured method is using 2FA
	 */
	public function hasAnyWith2FA(): bool
	{
		foreach ($this->enabled() as $options) {
			if (isset($options['2fa']) === true && $options['2fa'] === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the method type is available
	 */
	public function hasAvailable(string $type): bool
	{
		return in_array($type, array_keys($this->available()), true);
	}
}
