<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
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

	public function config(): array
	{
		$config     = $this->kirby->system()->loginMethods();
		$normalized = [];

		foreach ($config as $key => $value) {
			if (is_int($key) === true) {
				// ['password', 'code']
				$normalized[$value] = [];
			} elseif ($value === true) {
				// ['password' => true]
				$normalized[$key] = [];
			} else {
				// ['password' => ['2fa' => true]]
				$normalized[$key] = $value;
			}
		}

		return $normalized;
	}

	/**
	 * Resolves and instantiates a method handler by name
	 */
	public function handler(string $type, string $mode = 'login'): Method|null
	{
		$config = $this->config();

		if (isset($config[$type]) === false) {
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

		// deliberate constructor parameters per handler
		return $class::factory(
			type:    $type,
			options: $config[$type] ?? []
		);
	}

	public function attempt(
		string $type,
		string $email,
		string|null $password = null,
		bool $long = false,
		string $mode = 'login'
	): User|Status|null {
		$method = $this->handler($type, $mode);
		return $method?->attempt($email, $password, $long, $mode);
	}
}
