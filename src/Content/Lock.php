<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Toolkit\Str;

/**
 * The Lock class provides information about the
 * locking state of a content version, depending
 * on the timestamp and locked user id
 *
 * @since 5.0.0
 * @unstable
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
		protected bool $legacy = false
	) {
	}

	/**
	 * Creates a lock for the given version by
	 * reading the modification timestamp and
	 * lock user id from the version.
	 */
	public static function for(
		Version $version,
		Language|string $language = 'default'
	): static {

		if ($legacy = static::legacy($version->model())) {
			return $legacy;
		}

		// wildcard to search for a lock in any language
		// the first locked one will be preferred
		if ($language === '*') {
			foreach (Languages::ensure() as $language) {
				$lock = static::for($version, $language);

				// return the first locked lock if any exists
				if ($lock->isLocked() === true) {
					return $lock;
				}
			}

			// return the last lock if no lock was found
			return $lock;
		}

		$language = Language::ensure($language);

		// if the version does not exist, it cannot be locked
		if ($version->exists($language) === false) {
			// create an open lock for the current user
			return new static(
				user: App::instance()->user(),
			);
		}

		// Read the locked user id from the version
		if ($userId = ($version->read($language)['lock'] ?? null)) {
			$user = App::instance()->user($userId);
		}

		return new static(
			user: $user ?? null,
			modified: $version->modified($language)
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
	 * Checks if content locking is enabled at all
	 */
	public static function isEnabled(): bool
	{
		return App::instance()->option('content.locking', true) !== false;
	}

	/**
	 * Checks if the lock is coming from an old .lock file
	 */
	public function isLegacy(): bool
	{
		return $this->legacy;
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
	 * Looks for old .lock files and tries to create a
	 * usable lock instance from them
	 */
	public static function legacy(ModelWithContent $model): static|null
	{
		$kirby = $model->kirby();
		$file  = static::legacyFile($model);
		$id    = '/' . $model->id();

		// no legacy lock file? no lock.
		if (file_exists($file) === false) {
			return null;
		}

		$data = Data::read($file, 'yml', fail: false)[$id] ?? [];

		// no valid lock entry? no lock.
		if (isset($data['lock']) === false) {
			return null;
		}

		// has the lock been unlocked? no lock.
		if (isset($data['unlock']) === true) {
			return null;
		}

		return new static(
			user: $kirby->user($data['lock']['user']),
			modified: $data['lock']['time'],
			legacy: true
		);
	}

	/**
	 * Returns the absolute path to a legacy lock file
	 */
	public static function legacyFile(ModelWithContent $model): string
	{
		$root = match ($model::CLASS_ALIAS) {
			'file'  => dirname($model->root()),
			default => $model->root()
		};
		return $root . '/.lock';
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
			'isLegacy' => $this->isLegacy(),
			'isLocked' => $this->isLocked(),
			'modified' => $this->modified('c', 'date'),
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
