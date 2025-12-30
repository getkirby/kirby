<?php

namespace Kirby\Auth;

use InvalidArgumentException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;
use Kirby\Toolkit\A;

/**
 * Registry and orchestrator for available auth methods
 */
class Methods
{
	public function __construct(
		protected App $kirby
	) {
	}

	public function available(): array
	{
		return $this->enabled();
	}

	public function authenticate(
		string $type,
		string $email,
		string|null $password = null,
		bool $long = false
	): User|Status|null {
		$config = $this->enabled()[$type];
		$method = $this->class($type);
		$method = new $method(options: $config);
		return $method->authenticate($email, $password, $long);
	}

	/**
	 * Returns the method handler class for the provided type
	 */
	public function class(string $type): string|null
	{
		if (
			($class = Auth::$methods[$type] ?? null) &&
			is_subclass_of($class, Method::class) === true
		) {
			return $class;
		}

		return null;
	}

	/**
	 * Returns normalized array of enabled methods
	 * by the `auth.methods` config option
	 */
	public function enabled(): array
	{
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

		return $normalized;
	}

	public function firstAvailable(User $user): string|null
	{
		return $this->available($user)[0] ?? null;
	}
}
