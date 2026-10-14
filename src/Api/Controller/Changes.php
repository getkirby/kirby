<?php

namespace Kirby\Api\Controller;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\PermissionException;
use Kirby\Form\Fields;
use Kirby\Form\Form;

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
	 * Saves the lastest state of changes first and then publishes them
	 */
	public static function publish(ModelWithContent $model, array $input): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.publish.permission',
			);
		}

		// save the given changes first
		static::save(
			model: $model,
			input: $input
		);

		// get the changes version
		$changes = $model->version('changes');

		// if the changes version does not exist, we need to return early
		if ($changes->exists('current') === false) {
			return [
				'status' => 'ok',
			];
		}

		// publish the changes
		$changes->publish(
			language: 'current'
		);

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves form input in a new or existing `changes` version
	 */
	public static function save(ModelWithContent $model, array $input): array
	{
		if ($model->guards()->isAvailable('update') === false) {
			throw new PermissionException(
				key: 'version.save.permission',
			);
		}

		// get the current language
		$language = Language::ensure('current');

		// create the fields instance for the model
		$fields = Fields::for($model, $language);

		// get the changes and latest version for the model
		$changes = $model->version('changes');
		$latest  = $model->version('latest');

		// get the source version for the existing content
		$source  = $changes->exists($language) === true ? $changes : $latest;
		$content = $source->content($language)->toArray();

		// fill in the form values and pass through any values that are not
		// defined as fields, such as uuid, title or similar.
		$fields->fill(input: $content);

		// submit the new values from the request input
		$fields->submit(input: $input);

		// save the changes
		$changes->save(
			fields:   $fields->toStoredValues(),
			language: $language
		);

		// if the changes are identical to the latest version,
		// we can delete the changes version already at this point
		if ($changes->isIdentical(version: $latest, language: $language)) {
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
