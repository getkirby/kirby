<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;

/**
 * Wrapper to support legacy challenge implementations that
 * still extend the old `Kirby\Cms\Auth\Challenge` interface.
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @deprecated 6.0.0 Will be removed in a future major version.
 */
class LegacyChallenge extends Challenge
{
	public function __construct(
		protected string $type,
		protected string $class,
		User $user,
		string $mode,
		int $timeout,
	) {
		parent::__construct($user, $mode, $timeout);
	}

	/**
	 * Delegates to the legacy challenge class to create data
	 * and keeps compatibility by storing the hashed code in
	 * the session for the legacy verify implementation.
	 */
	public function create(): Pending|null
	{
		$code = $this->class::create($this->user, [
			'mode'    => $this->mode,
			'timeout' => $this->timeout
		]);

		if ($code === null) {
			$this->kirby->session()->remove('kirby.challenge.code');
			return null;
		}

		$hash = password_hash($code, PASSWORD_DEFAULT);
		$this->kirby->session()->set('kirby.challenge.code', $hash);

		return new Pending(secret: $hash);
	}

	public static function isAvailable(User $user, string $mode): bool
	{
		// needs to be resolved against the legacy class
		// before instantiation
		return true;
	}

	/**
	 * Proxies verification to the legacy challenge
	 */
	public function verify(mixed $input, Pending $data): bool
	{
		return $this->class::verify($this->user, (string)$input);
	}

	public function type(): string
	{
		return $this->type;
	}
}
