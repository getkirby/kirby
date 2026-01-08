<?php

namespace Kirby\Auth;

use Kirby\Api\Api;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
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

	protected array $enabled;

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
		if ($this->has($type) === false) {
			throw new InvalidArgumentException(
				message: 'Auth method "' . $type . '" is not enabled'
			);
		}

		return $this->get($type)->authenticate($email, $password, $long);
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
			$this->has('code')                      => 'code',
			$this->has('password-reset')            => 'password-reset',
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
	 * Returns normalized array of configured methods
	 */
	public function config(): array
	{
		$default    = ['password' => []];
		$methods    = $this->kirby->option('auth.methods', $default);
		$normalized = [];

		foreach (A::wrap($methods) as $type => $options) {
			if (is_int($type) === true) {
				// ['password']
				$type    = $options;
				$options = [];
			} elseif ($options === true) {
				// ['password' => true]
				$options = [];
			}

			$normalized[$type] = $options;
		}

		return $normalized;
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

		$config  = $this->config();
		$enabled = [];

		foreach ($config as $type => $options) {
			$class = $this->class($type);

			try {
				if ($class::isEnabled($this->auth, $options) === true) {
					$enabled[$type] = $options;
				}

			} catch (Exception $e) {
				// consider methods that throw an exception
				// from their `::isEnabled()` method as not enabled

				// when running in debug, re-throw exception
				if ($this->kirby->option('debug') === true) {
					throw $e;
				}
			}
		}

		return $this->enabled = $enabled;
	}

	/**
	 * Returns the first enabled auth method
	 * for the current context
	 */
	public function firstEnabled(): Method|null
	{
		$enabled = $this->enabled();
		$type    = array_key_first($enabled);
		return $type ? $this->get($type) : null;
	}

	/**
	 * Returns an instance of the requested auth method.
	 * (You might still need to check yourself
	 * if the method is actually enabled in your context)
	 */
	public function get(string $type): Method
	{
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
		return array_key_exists($type, $this->enabled());
	}

	/**
	 * Checks if any method is using challenges
	 */
	public function hasAnyUsingChallenges(): bool
	{
		foreach ($this->enabled() as $method => $options) {
			$class = $this->class($method);

			if ($class::isUsingChallenges($this->auth, $options) === true) {
				return true;
			}
		}

		return false;
	}
}
