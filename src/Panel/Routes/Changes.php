<?php

namespace Kirby\Panel\Routes;

use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
use Kirby\Form\Form;

class Changes
{
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

}

