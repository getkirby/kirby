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

	}

	public static function delete(
		Version $version,
		Language $language
	): void {

	}

	public static function move(
		Version $version,
		Language $fromLanguage,
		VersionId $toVersionId,
		Language $toLanguage
	): void {

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

	}

	public static function replace(
		Version $version,
		array $fields,
		Language $language
	): void {

	}

	public static function update(
		Version $version,
		array $fields,
		Language $language
	): void {

	}
}
