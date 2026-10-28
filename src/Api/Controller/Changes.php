<?php

namespace Kirby\Api\Controller;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\PermissionException;
use Kirby\Form\Fields;

/**
 * The Changes controller takes care of the request logic
 * to save, discard and publish changes.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{
	/**
	 * Discards unsaved changes by deleting the changes version
	 *
	 * @return array<string, mixed>
	 */
	public static function discard(ModelWithContent $model): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.discard.permission',
			);
		}

		$model->version('changes')->delete('current');

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves the latest state of changes first and then publishes them
	 *
	 * @return array<string, mixed>
	 */
	public static function publish(ModelWithContent $model, array $input): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.publish.permission',
			);
		}

		static::save(
			model: $model,
			input: $input
		);

		$changes = $model->version('changes');

		// saving identical content already deleted the changes version
		if ($changes->exists('current') === true) {
			$changes->publish(
				language: 'current'
			);
		}

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves form input in a new or existing `changes` version
	 *
	 * @return array<string, mixed>
	 */
	public static function save(ModelWithContent $model, array $input): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.save.permission',
			);
		}

		$language = Language::ensure('current');
		$fields   = Fields::for($model, $language);
		$changes  = $model->version('changes');
		$latest   = $model->version('latest');

		// read the raw fields to find out whether the changes version really
		// holds content. `Version::content()` would not tell us: for a
		// secondary language it quietly falls back to the default language,
		// which would then be stored as the translation.
		$changed = $changes->read($language) ?? [];

		// the lock is not content and is dropped by `Version::content()`
		// as well, so a version that only holds it has nothing to offer
		unset($changed['lock']);

		// get the source version for the existing content
		$source  = $changed === [] ? $latest : $changes;
		$content = $source->content($language)->toArray();

		// fill in the form values and pass through any values that are not
		// defined as fields, such as uuid, title or similar.
		$fields->fill(input: $content);

		$fields->submit(input: $input);

		$changes->save(
			fields:   $fields->toStoredValues(),
			language: $language
		);

		// if the changes are identical to the latest version,
		// we can delete the changes version already at this point
		if ($changes->isIdentical(version: $latest, language: $language) === true) {
			$changes->delete(
				language: $language
			);
		}

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Releases the content lock without discarding changes
	 *
	 * @return array<string, mixed>
	 */
	public static function unlock(ModelWithContent $model): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.unlock.permission',
			);
		}

		$model->version('changes')->unlock('current');

		return [
			'status' => 'ok'
		];
	}
}
