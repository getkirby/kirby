<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

$languageDialogFields = [
	'name' => [
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
		'icon'     => 'globe',
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
		'label' => I18n::translate('language.locale'),
		'type'  => 'text',
	],
];

$translationDialogFields = [
	'key' => [
		'label' => 'Key',
		'type' => 'slug',
		'width' => '1/3',
		'icon' => null,
	],
	'value' => [
		'label' => 'Value',
		'type' => 'text',
		'width' => '2/3',
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
				'event' => 'language.delete',
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
			$language = Find::language($languageCode);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => $translationDialogFields,
					'size'   => 'large',
				],
			];
		},
		'submit' => function (string $languageCode) {
			$language     = Find::language($languageCode);
			$translations = $language->translations();
			$key          = Str::slug(get('key'));
			$value        = get('value');

			$translations[$key] = $value;

			$language->update([
				'translations' => $translations
			]);

			return true;
		}
	],
	'language.translation.delete' => [
		'pattern' => 'languages/(:any)/translations/(:any)/delete',
		'load'    => function (string $languageCode, string $translationKey) {
			$language = Find::language($languageCode);
			$translations = $language->translations();

			if (isset($translations[$translationKey]) === false) {
				throw new NotFoundException('The translation could not be found');
			}

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => 'Do you really want to delete the translation for "' . $translationKey . '"?'
				],
			];
		},
		'submit' => function (string $languageCode, string $translationKey) {
			$language     = Find::language($languageCode);
			$translations = $language->translations();

			unset($translations[$translationKey]);

			$language->update([
				'translations' => $translations
			]);

			return true;
		}
	],
	'language.translation.update' => [
		'pattern' => 'languages/(:any)/translations/(:any)/update',
		'load'    => function (string $languageCode, string $translationKey) use ($translationDialogFields) {
			$language     = Find::language($languageCode);
			$translations = $language->translations();

			$fields = $translationDialogFields;
			$fields['key']['disabled'] = true;
			$fields['value']['autofocus'] = true;

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => $fields,
					'size'   => 'large',
					'value'  => [
						'key'   => $translationKey,
						'value' => $translations[$translationKey] ?? ''
					]
				],
			];
		},
		'submit' => function (string $languageCode, string $translationKey) {
			$language     = Find::language($languageCode);
			$translations = $language->translations();

			$key   = Str::slug(get('key'));
			$value = get('value');

			$translations[$key] = $value;

			$language->update([
				'translations' => $translations
			]);

			return true;
		}
	]

];
