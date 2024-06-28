<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
use Kirby\Exception\Exception;
use Kirby\Form\Form;

class Changes
{
	public static function discard(ModelWithContent $model)
	{
		$model->version(VersionId::changes())->delete();

		return [
			'status' => 'ok'
		];
	}

	public static function publish(ModelWithContent $model, array $input)
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

		return [
			'status' => 'ok'
		];
	}

	public static function save(ModelWithContent $model, array $input)
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

		return [
			'status' => 'ok'
		];
	}

	public static function unlock(ModelWithContent $model)
	{
		throw new Exception('Not yet implemented');
	}
}
