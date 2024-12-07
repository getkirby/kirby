<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Shared file dialogs
 * They are included in the site and
 * users area to create dialogs there.
 * The array keys are replaced by
 * the appropriate routes in the areas.
 */
return [
	'changeName' => [
		'load' => function (string $path, string $filename) {
			$file = Find::file($path, $filename);
			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'name' => [
							'label'     => I18n::translate('name'),
							'type'      => 'slug',
							'required'  => true,
							'icon'      => 'title',
							'allow'     => 'a-z0-9@._-',
							'after'     => '.' . $file->extension(),
							'preselect' => true
						]
					],
					'submitButton' => I18n::translate('rename'),
					'value' => [
						'name' => $file->name(),
					]
				]
			];
		},
		'submit' => function (string $path, string $filename) {
			$file     = Find::file($path, $filename);
			$name     = $file->kirby()->request()->get('name');
			$renamed  = $file->changeName($name);
			$oldUrl   = $file->panel()->url(true);
			$newUrl   = $renamed->panel()->url(true);
			$response = [
				'event' => 'file.changeName'
			];

			// check for a necessary redirect after the filename has changed
			if (Panel::referrer() === $oldUrl && $oldUrl !== $newUrl) {
				$response['redirect'] = $newUrl;
			}

			return $response;
		}
	],

	'changeSort' => [
		'load' => function (string $path, string $filename) {
			$file = Find::file($path, $filename);
			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'position' => Field::filePosition($file)
					],
					'submitButton' => I18n::translate('change'),
					'value' => [
						'position' => $file->sort()->isEmpty() ? $file->siblings(false)->count() + 1 : $file->sort()->toInt(),
					]
				]
			];
		},
		'submit' => function (string $path, string $filename) {
			$file     = Find::file($path, $filename);
			$files    = $file->siblings()->sorted();
			$ids      = $files->keys();
			$newIndex = (int)($file->kirby()->request()->get('position')) - 1;
			$oldIndex = $files->indexOf($file);

			array_splice($ids, $oldIndex, 1);
			array_splice($ids, $newIndex, 0, $file->id());

			$files->changeSort($ids);

			return [
				'event' => 'file.sort',
			];
		}
	],

	'changeTemplate' => [
		'load' => function (string $path, string $filename) {
			$file 		= Find::file($path, $filename);
			$blueprints = $file->blueprints();

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'warning' => [
							'type'  => 'info',
							'theme' => 'notice',
							'text'  => I18n::translate('file.changeTemplate.notice')
						],
						'template' => Field::template($blueprints, [
							'required' => true
						])
					],
					'theme' => 'notice',
					'submitButton' => I18n::translate('change'),
					'value' => [
						'template' => $file->template()
					]
				]
			];
		},
		'submit' => function (string $path, string $filename) {
			$file     = Find::file($path, $filename);
			$template = $file->kirby()->request()->get('template');

			$file->changeTemplate($template);

			return [
				'event' => 'file.changeTemplate',
			];
		}
	],

	'delete' => [
		'load' => function (string $path, string $filename) {
			$file = Find::file($path, $filename);
			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::template('file.delete.confirm', [
						'filename' => Escape::html($file->filename())
					]),
				]
			];
		},
		'submit' => function (string $path, string $filename) {
			$file     = Find::file($path, $filename);
			$redirect = false;
			$referrer = Panel::referrer();
			$url      = $file->panel()->url(true);

			$file->delete();

			// redirect to the parent model URL
			// if the dialog has been opened in the file view
			if ($referrer === $url) {
				$redirect = $file->parent()->panel()->url(true);
			}

			return [
				'event'    => 'file.delete',
				'redirect' => $redirect
			];
		}
	],

];
