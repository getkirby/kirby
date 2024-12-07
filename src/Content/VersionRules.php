<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;

/**
 * The VersionRules class handles the validation for all
 * modification actions on a single version
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
class VersionRules
{
	public static function create(
		Version $version,
		array $fields,
		Language $language
	): void {
		if ($version->exists($language) === true) {
			throw new LogicException(
				message: 'The version already exists'
			);
		}

		if ($version->isLatest() === true) {
			return;
		}

		if ($version->model()->version(VersionId::latest())->exists($language) === false) {
			throw new LogicException(
				message: 'A matching latest version for the changes does not exist'
			);
		}
	}

	/**
	 * Checks if a version/language combination exists and otherwise
	 * will throw a `NotFoundException`
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public static function ensure(Version $version, Language $language): void
	{
		if ($version->exists($language) === true) {
			return;
		}

		$message = match($version->model()->kirby()->multilang()) {
			true  => 'Version "' . $version->id() . ' (' . $language->code() . ')" does not already exist',
			false => 'Version "' . $version->id() . '" does not already exist',
		};

		throw new NotFoundException($message);
	}

	public static function delete(
		Version $version,
		Language $language
	): void {
		if ($version->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $version->lock('*'),
				key: 'content.lock.delete'
			);
		}
	}

	public static function move(
		Version $fromVersion,
		Language $fromLanguage,
		Version $toVersion,
		Language $toLanguage
	): void {
		// make sure that the source version exists
		static::ensure($fromVersion, $fromLanguage);

		// check if the source version is locked in any language
		if ($fromVersion->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $fromVersion->lock('*'),
				key: 'content.lock.move'
			);
		}

		// check if the target version is locked in any language
		if ($toVersion->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $toVersion->lock('*'),
				key: 'content.lock.update'
			);
		}
	}

	public static function publish(
		Version $version,
		Language $language
	): void {
		// the latest version is already published
		if ($version->isLatest() === true) {
			throw new LogicException(
				message: 'This version is already published'
			);
		}

		// make sure that the version exists
		static::ensure($version, $language);

		// check if the version is locked in any language
		if ($version->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $version->lock('*'),
				key: 'content.lock.publish'
			);
		}
	}

	public static function read(
		Version $version,
		Language $language
	): void {
		static::ensure($version, $language);
	}

	public static function replace(
		Version $version,
		array $fields,
		Language $language
	): void {
		// make sure that the version exists
		static::ensure($version, $language);

		// check if the version is locked in any language
		if ($version->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $version->lock('*'),
				key: 'content.lock.replace'
			);
		}
	}

	public static function touch(
		Version $version,
		Language $language
	): void {
		static::ensure($version, $language);
	}

	public static function update(
		Version $version,
		array $fields,
		Language $language
	): void {
		static::ensure($version, $language);

		if ($version->isLocked('*') === true) {
			throw new LockedContentException(
				lock: $version->lock('*'),
				key: 'content.lock.update'
			);
		}
	}
}
