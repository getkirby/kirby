<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\RateLimitException;
use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;

/**
 * Handler to enforce the auth rate limits
 *
 * @package   Kirby Auth
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Limits
{
	public function __construct(
		protected App $kirby
	) {
	}

	public function ensure(string $email): void
	{
		if ($this->isBlocked($email) === true) {
			$this->kirby->trigger('user.login:failed', ['email' => $email]);
			throw new RateLimitException();
		}
	}

	public function file(): string
	{
		return $this->kirby->root('accounts') . '/.logins';
	}

	public function isBlocked(string $email): bool
	{
		$log    = $this->log();
		$ip     = $this->kirby->visitor()->ip(hash: true);
		$trials = $this->kirby->option('auth.trials', 10);

		if (($log['by-ip'][$ip]['trials'] ?? null) >= $trials) {
			return true;
		}

		if ($this->kirby->users()->find($email)) {
			if (($log['by-email'][$email]['trials'] ?? null) >= $trials) {
				return true;
			}
		}

		return false;
	}

	public function log(): array
	{
		$log  = Data::read($this->file(), 'json', fail: false);

		// ensure that the category arrays are defined
		$log['by-ip']    ??= [];
		$log['by-email'] ??= [];

		// remove all elements on the top level
		// with different keys (old structure)
		$log = array_intersect_key($log, array_flip(['by-ip', 'by-email']));

		// remove entries that are no longer needed
		$time    = time() - $this->kirby->option('auth.timeout', 3600);
		$updated = A::map(
			$log,
			fn ($category) => A::filter(
				$category,
				fn ($entry) => $entry['time'] > $time
			)
		);

		// write new log to the file system if it changed
		if ($updated['by-ip'] === [] && $updated['by-email'] === []) {
			F::remove($this->file());
		} elseif ($updated !== $log) {
			Data::write($this->file(), $updated, 'json');
		}

		return $updated;
	}

	public function track(string|null $email, bool $triggerHook = true): bool
	{
		if ($triggerHook === true) {
			$this->kirby->trigger('user.login:failed', ['email' => $email]);
		}

		$log  = $this->log();
		$ip   = $this->kirby->visitor()->ip(hash: true);
		$time = time();

		$log['by-ip'][$ip]          ??= ['trials' => 0];
		$log['by-ip'][$ip]['time']    = $time;
		$log['by-ip'][$ip]['trials'] += 1;

		if ($email !== null && $this->kirby->users()->find($email)) {
			$log['by-email'][$email]          ??= ['trials' => 0];
			$log['by-email'][$email]['time']    = $time;
			$log['by-email'][$email]['trials'] += 1;
		}

		return Data::write($this->file(), $log, 'json');
	}
}
