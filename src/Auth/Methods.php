<?php

namespace Kirby\Auth;

use InvalidArgumentException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User;

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
		return $this->kirby->system()->loginMethods();
	}

	/**
	 * Resolves and instantiates a method handler by name
	 */
	public function handler(string $type, string $mode = 'login'): Method|null
	{
		$config = $this->available()[$type] ?? null;

		if ($config === null) {
			return null;
		}

		$class = Auth::$methods[$type] ?? null;

		if (
			$class === null ||
			is_subclass_of($class, Method::class) === false ||
			$class::isAvailable($mode) !== true
		) {
			return null;
		}

		return new $class(
			type:    $type,
			options: $config
		);
	}

	public function attempt(
		string $type,
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): User|Status|null {
		$handler = $this->handler($type, $mode);

		if ($handler === null) {
			throw new InvalidArgumentException(
				message: 'Login method is not enabled: ' . $type
			);
		}

		return $handler?->attempt($email, $password, $long, $mode);
	}
}
