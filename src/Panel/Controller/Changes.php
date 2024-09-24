<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
use Kirby\Exception\Exception;
use Kirby\Form\Form;

/**
 * The Changes controller takes care of the request logic
 * to save, discard and publish changes, as well as unlocking.
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{
	/**
	 * Discards unpublished changes by deleting the version
	 */
	public static function discard(ModelWithContent $model): array
	{
		$model->version(VersionId::changes())->delete();

		// remove the model from the user's list of unsaved changes
		App::instance()->site()->changes()->untrack($model);

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves the lastest state of changes first and then publishs them
	 */
	public static function publish(ModelWithContent $model, array $input): array
	{
		// save the given changes first
		static::save(
			model: $model,
			input: $input
		);

		// get the changes version
		$changes = $model->version(VersionId::changes());

		// publish the changes
		$changes->publish(
			language: 'current'
		);

		// remove the model from the user's list of unsaved changes
		App::instance()->site()->changes()->untrack($model);

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Saves form input in a new or existing `changes` version
	 */
	public static function save(ModelWithContent $model, array $input): array
	{
		// we need to run the input through the form
		// class to get a set of storable field values
		// that we can send to the content storage handler
		$form = Form::for($model, [
			'ignoreDisabled' => true,
			'input'          => $input,
		]);

		// combine the new field changes with the
		// last published state
		$model->version(VersionId::changes())->save(
			fields: [
				...$model->version(VersionId::published())->read(),
				...$form->strings(),
			],
			language: 'current'
		);

		// add the model to the user's list of unsaved changes
		App::instance()->site()->changes()->track($model);

		return [
			'status' => 'ok'
		];
	}

	/**
	 * Removes the user lock from a `changes` version
	 */
	public static function unlock(ModelWithContent $model): array
	{
		throw new Exception(message: 'Not yet implemented');

		return [
			'status' => 'ok'
		];
	}
}
