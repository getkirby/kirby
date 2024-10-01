<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;

/**
 * The Lock class provides information about the
 * locking state of a content version, depending
 * on the timestamp and locked user id
 *
 * @internal
 * @since 5.0.0
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Lock
{
	public function __construct(
		protected User|null $user = null,
		protected int|null $modified = null,
	) {
	}

	/**
	 * Creates a lock for the given version by
	 * reading the modification timestamp and
	 * lock user id from the version.
	 */
	public static function for(
		Version $version
	): static {
		// if the version does not exist, it cannot be locked
		if ($version->exists() === false) {
			// create an open lock for the current user
			return new static(
				user: App::instance()->user(),
			);
		}

		// Read the locked user id from the version
		if ($userId = ($version->read('default')['lock'] ?? null)) {
			$user = App::instance()->user($userId);
		}

		return new static(
			user: $user ?? null,
			modified: $version->modified()
		);
	}

	/**
	 * Checks if the lock is still active because
	 * recent changes have been made to the content
	 */
	public function isActive(): bool
	{
		$minutes = 10;
		return $this->modified > time() - (60 * $minutes);
	}

	/**
	 * Check if content locking is enabled at all
	 */
	public static function isEnabled(): bool
	{
		return App::instance()->option('content.locking', true) !== false;
	}

	/**
	 * Checks if the lock is actually locked
	 */
	public function isLocked(): bool
	{
		// if locking is disabled globally,
		// the lock is always open
		if (static::isEnabled() === false) {
			return false;
		}

		if ($this->user === null) {
			return false;
		}

		// the version is not locked if the editing user
		// is the currently logged in user
		if ($this->user === App::instance()->user()) {
			return false;
		}

		// check if the lock is still active due to the
		// content currently being edited.
		if ($this->isActive() === false) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the timestamp when the locked content has
	 * been updated. You can pass a format to get a useful,
	 * formatted date back.
	 */
	public function modified(
		string|null $format = null,
		string|null $handler = null
	): int|string|false|null {
		if ($this->modified === null) {
			return null;
		}

		return Str::date($this->modified, $format, $handler);
	}

	/**
	 * Converts the lock info to an array. This is directly
	 * usable for Panel view props.
	 */
	public function toArray(): array
	{
		return [
			'isLocked' => $this->isLocked(),
			'modified' => $this->modified('c'),
			'user'     => [
				'id'    => $this->user?->id(),
				'email' => $this->user?->email()
			]
		];
	}

	/**
	 * Returns the user to whom this lock belongs
	 */
	public function user(): User|null
	{
		return $this->user;
	}
}
