<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

$languageDialogFields = [
	'name' => [
		'counter'  => false,
		'label'    => I18n::translate('language.name'),
		'type'     => 'text',
		'required' => true,
		'icon'     => 'title'
	],
	'code' => [
		'label'    => I18n::translate('language.code'),
		'type'     => 'text',
		'required' => true,
		'counter'  => false,
		'icon'     => 'translate',
		'width'    => '1/2'
	],
	'direction' => [
		'label'    => I18n::translate('language.direction'),
		'type'     => 'select',
		'required' => true,
		'empty'    => false,
		'options'  => [
			['value' => 'ltr', 'text' => I18n::translate('language.direction.ltr')],
			['value' => 'rtl', 'text' => I18n::translate('language.direction.rtl')]
		],
		'width'    => '1/2'
	],
	'locale' => [
		'counter' => false,
		'label'   => I18n::translate('language.locale'),
		'type'    => 'text',
	],
];

$translationDialogFields = [
	'key' => [
		'counter' => false,
		'icon'    => null,
		'label'   => I18n::translate('language.variable.key'),
		'type'    => 'text'
	],
	'value' => [
		'buttons' => false,
		'counter' => false,
		'label'   => I18n::translate('language.variable.value'),
		'type'    => 'textarea'
	]
];

return [

	// create language
	'language.create' => [
		'pattern' => 'languages/create',
		'load' => function () use ($languageDialogFields) {
			return [
				'component' => 'k-language-dialog',
				'props' => [
					'fields' => $languageDialogFields,
					'submitButton' => I18n::translate('language.create'),
					'value' => [
						'code'      => '',
						'direction' => 'ltr',
						'locale'    => '',
						'name'      => '',
					]
				]
			];
		},
		'submit' => function () {
			$kirby = App::instance();

			$data = $kirby->request()->get([
				'code',
				'direction',
				'locale',
				'name'
			]);
			$kirby->languages()->create($data);

			return [
				'event' => 'language.create'
			];
		}
	],

	// delete language
	'language.delete' => [
		'pattern' => 'languages/(:any)/delete',
		'load' => function (string $id) {
			$language = Find::language($id);
			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::template('language.delete.confirm', [
						'name' => Escape::html($language->name())
					])
				]
			];
		},
		'submit' => function (string $id) {
			Find::language($id)->delete();

			return [
				'event'    => 'language.delete',
				'redirect' => 'languages'
			];
		}
	],

	// update language
	'language.update' => [
		'pattern' => 'languages/(:any)/update',
		'load' => function (string $id) use ($languageDialogFields) {
			$language = Find::language($id);
			$fields   = $languageDialogFields;
			$locale   = $language->locale();

			// use the first locale key if there's only one
			if (count($locale) === 1) {
				$locale = A::first($locale);
			}

			// the code of an existing language cannot be changed
			$fields['code']['disabled'] = true;

			// if the locale settings is more complex than just a
			// single string, the text field won't do it anymore.
			// Changes can only be made in the language file and
			// we display a warning box instead.
			if (is_array($locale) === true) {
				$fields['locale'] = [
					'label' => $fields['locale']['label'],
					'type'  => 'info',
					'text'  => I18n::translate('language.locale.warning')
				];
			}

			return [
				'component' => 'k-language-dialog',
				'props' => [
					'fields'       => $fields,
					'submitButton' => I18n::translate('save'),
					'value'        => [
						'code'      => $language->code(),
						'direction' => $language->direction(),
						'locale'    => $locale,
						'name'      => $language->name(),
						'rules'     => $language->rules(),
					]
				]
			];
		},
		'submit' => function (string $id) {
			$kirby = App::instance();

			$data = $kirby->request()->get(['direction', 'locale', 'name']);
			$language = Find::language($id)->update($data);

			return [
				'event' => 'language.update'
			];
		}
	],

	'language.translation.create' => [
		'pattern' => 'languages/(:any)/translations/create',
		'load'    => function (string $languageCode) use ($translationDialogFields) {
			// find the language to make sure it exists
			Find::language($languageCode);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => $translationDialogFields,
					'size'   => 'large',
				],
			];
		},
		'submit' => function (string $languageCode) {
			$request  = App::instance()->request();
			$language = Find::language($languageCode);

			$key   = $request->get('key', '');
			$value = $request->get('value', '');

			LanguageVariable::create($key, $value);

			if ($language->isDefault() === false) {
				$language->variable($key)->update($value);
			}

			return true;
		}
	],
	'language.translation.delete' => [
		'pattern' => 'languages/(:any)/translations/(:any)/delete',
		'load'    => function (string $languageCode, string $translationKey) {
			$variable = Find::language($languageCode)->variable($translationKey, true);

			if ($variable->exists() === false) {
				throw new NotFoundException([
					'key' => 'language.variable.notFound'
				]);
			}

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::template('language.variable.delete.confirm', [
						'key' => Escape::html($variable->key())
					])
				],
			];
		},
		'submit' => function (string $languageCode, string $translationKey) {
			return Find::language($languageCode)->variable($translationKey, true)->delete();
		}
	],
	'language.translation.update' => [
		'pattern' => 'languages/(:any)/translations/(:any)/update',
		'load'    => function (string $languageCode, string $translationKey) use ($translationDialogFields) {
			$variable = Find::language($languageCode)->variable($translationKey, true);

			if ($variable->exists() === false) {
				throw new NotFoundException([
					'key' => 'language.variable.notFound'
				]);
			}

			$fields = $translationDialogFields;
			$fields['key']['disabled'] = true;

			$value = ['key' => $variable->key()];

			// if language variable is array,
			// set text fields as per array size
			// otherwise it will show only one textarea
			if (is_array($variable->value()) === true) {
				unset($fields['value']);

				foreach ($variable->value() as $index => $val) {
					$fields['value_' . $index] = [
						'autofocus' => $index === 0,
						'counter'   => false,
						'label'     => I18n::translate('language.variable.value') . ' ' . ($index + 1),
						'type'      => 'text',
					];
					$value['value_' . $index]  = $val;
				}
			} else {
				$fields['value']['autofocus'] = true;
				$value['value'] = $variable->value();
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => $fields,
					'size'   => 'large',
					'value'  => $value
				],
			];
		},
		'submit' => function (string $languageCode, string $translationKey) {
			$kirby    = App::instance();
			$variable = Find::language($languageCode)->variable($translationKey, true);

			// if the language variable is array
			// it reads the text field values as many as the number of arrays
			if (is_array($variable->value()) === true) {
				$value = array_map(
					fn ($val, $index) => $kirby->request()->get('value_' . $index, ''),
					$variable->value(),
					array_keys($variable->value())
				);
			} else {
				$value = $kirby->request()->get('value', '');
			}

			$variable->update($value);

			return true;
		}
	]

];
