<?php

namespace Kirby\Api\Controller;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\Lock;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Form\Fields;
use Kirby\Form\Form;

/**
 * The Changes controller takes care of the request logic
 * to save, discard and publish changes.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{
	/**
	 * Cleans up legacy lock files. The `discard`, `publish` and `save` actions
	 * are perfect for this cleanup job. They will be stopped early if
	 * the lock is still active and otherwise, we can use them to clean
	 * up outdated .lock files to keep the content folders clean. This
	 * can be removed as soon as old .lock files should no longer be around.
	 *
	 * @todo Remove in 6.0.0
	 */
	protected static function cleanup(ModelWithContent $model): void
	{
		F::remove(Lock::legacyFile($model));
	}

	/**
	 * Discards unsaved changes by deleting the changes version
	 */
	public static function discard(ModelWithContent $model): array
	{
		if ($model->permissions()->can('update') === false) {
			throw new PermissionException(
				key: 'version.discard.permission',
			);
		}

		$model->version('changes')->delete('current');

		// Removes the old .lock file when it is no longer needed
		// @todo Remove in 6.0.0
		static::cleanup($model);

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves the lastest state of changes first and then publishes them
	 */
	public static function publish(ModelWithContent $model, array $input): array
	{
		if ($model->permissions()->can('update') === false) {
			throw new PermissionException(
				key: 'version.publish.permission',
			);
		}

		// save the given changes first
		static::save(
			model: $model,
			input: $input
		);

		// Removes the old .lock file when it is no longer needed
		// @todo Remove in 6.0.0
		static::cleanup($model);

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
		if ($model->permissions()->can('update') === false) {
			throw new PermissionException(
				key: 'version.save.permission',
			);
		}

		// Removes the old .lock file when it is no longer needed
		// @todo Remove in 6.0.0
		static::cleanup($model);

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
}
