<?php

namespace Kirby\Auth;

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

	protected $enabled;

	public function __construct(
		protected Auth $auth,
		protected App $kirby
	) {
	}

	/**
	 * Authenticates via the specific auth method
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If auth method type does not exists
	 */
	public function authenticate(
		string $type,
		string $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		$method = $this->get($type);
		return $method->authenticate($email, $password, $long);
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
			message: 'Unsupported auth method: ' . $type
		);
	}

	/**
	 * Returns normalized array of enabled methods
	 * by the `auth.methods` config option
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If config is invalid (only in debug mode)
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
		$uses2fa    = false;

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

				if (isset($value['2fa']) === true && $value['2fa'] === true) {
					$uses2fa = true;
				}
			}
		}

		// 2FA must not be circumvented by code-based modes
		foreach (['code', 'password-reset'] as $method) {
			if ($uses2fa === true && isset($normalized[$method]) === true) {
				unset($normalized[$method]);

				if ($this->kirby->option('debug') === true) {
					throw new InvalidArgumentException(
						message: 'The "' . $method . '" login method cannot be enabled when 2FA is required'
					);
				}
			}
		}

		// only one code-based mode can be active at once
		if (
			isset($normalized['code']) === true &&
			isset($normalized['password-reset']) === true
		) {
			unset($normalized['code']);

			if ($this->kirby->option('debug') === true) {
				throw new InvalidArgumentException(
					message: 'The "code" and "password-reset" login methods cannot be enabled together'
				);
			}
		}

		return $this->enabled = $normalized;
	}

	/**
	 * Returns an instance of the requested auth method
	 */
	public function get(string $type): Method
	{
		$method = $this->class($type);
		return new $method(
			auth:    $this->auth,
			options: $this->enabled()[$type] ?? []
		);
	}
}
