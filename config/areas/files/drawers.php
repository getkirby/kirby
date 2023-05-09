<?php

use Kirby\Cms\Find;
use Kirby\Form\Form;

/**
 * Shared file drawers
 * They are included in the site and
 * users area to create drawers there.
 * The array keys are replaced by
 * the appropriate routes in the areas.
 */
return [
	'file' => [
		'load' => function (string $path, string $filename) {
			$file = Find::file($path, $filename);
			$form = Form::for($file);

			return [
				'component' => 'k-file-drawer',
				'props' => [
					'icon'  => 'image',
					'options' => [
						[
							'icon'     => 'cog',
							'dropdown' => $file->panel()->url(true)
						]
					],
					'model' => [
						'extension' => $file->extensions(),
						'id'        => $file->panel()->url(true),
						'mime'      => $file->mime(),
					],
					'preview' => $file->panel()->preview(),
					'title' => $file->filename(),
					'tabs'  => [
						'form' => [
							'fields' => $form->fields()->toArray(),
						]
					],
					'value' => $form->values(),
				]
			];
		},
		'submit' => function (string $path, string $filename) {
			$file = Find::file($path, $filename);
			$file->update(get(), null, true);

			return true;
		}
	],
];
