<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;

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

	public static function delete(
		Version $version,
		Language $language
	): void {
		if ($version->isLocked('*') === true) {
			throw new LogicException(
				message: 'The version is locked and cannot be deleted'
			);
		}
	}

	public static function move(
		Version $fromVersion,
		Language $fromLanguage,
		Version $toVersion,
		Language $toLanguage
	): void {
		if ($fromVersion->isLocked('*') === true) {
			throw new LogicException(
				message: 'The source version is locked and cannot be moved'
			);
		}

		if ($toVersion->isLocked('*') === true) {
			throw new LogicException(
				message: 'The target version is locked and cannot be overwritten'
			);
		}
	}

	public static function publish(
		Version $version,
		Language $language
	): void {
		if ($version->isLatest() === true) {
			throw new LogicException(
				message: 'This version is already published'
			);
		}

		if ($version->isLocked('*') === true) {
			throw new LogicException(
				message: 'The version is locked and cannot be published'
			);
		}
	}

	public static function replace(
		Version $version,
		array $fields,
		Language $language
	): void {
		if ($version->isLocked('*') === true) {
			throw new LogicException(
				message: 'The version is locked and cannot be replaced'
			);
		}
	}

	public static function update(
		Version $version,
		array $fields,
		Language $language
	): void {
		if ($version->isLocked('*') === true) {
			throw new LogicException(
				message: 'The version is locked and cannot be updated'
			);
		}
	}
}
