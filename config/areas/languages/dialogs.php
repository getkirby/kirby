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
				throw new NotFoundException(
					key: 'language.variable.notFound'
				);
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
				throw new NotFoundException(
					key: 'language.variable.notFound'
				);
			}

			$fields = $translationDialogFields;
			$fields['key']['disabled'] = true;
			$fields['value']['autofocus'] = true;

			// shows info text when variable is an array
			// TODO: 5.0: use entries field instead showing info text
			$isVariableArray = is_array($variable->value()) === true;

			if ($isVariableArray === true) {
				$fields['value'] = [
					'label' => I18n::translate('info'),
					'type'  => 'info',
					'text'  => 'You are using an array variable for this key. Please modify it in the language file in /site/languages',
				];
			}

			return [
				'component' => 'k-form-dialog',
				'props'     => [
					'cancelButton' => $isVariableArray === false,
					'fields'       => $fields,
					'size'         => 'large',
					'submitButton' => $isVariableArray === false,
					'value'        => [
						'key'   => $variable->key(),
						'value' => $variable->value()
					]
				],
			];
		},
		'submit' => function (string $languageCode, string $translationKey) {
			Find::language($languageCode)->variable($translationKey, true)->update(
				App::instance()->request()->get('value', '')
			);

			return true;
		}
	]

];
