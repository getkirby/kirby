<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Escape;

$languageDialogFields = [
    'name' => [
        'label'    => t('language.name'),
        'type'     => 'text',
        'required' => true,
        'icon'     => 'title'
    ],
    'code' => [
        'label'    => t('language.code'),
        'type'     => 'text',
        'required' => true,
        'counter'  => false,
        'icon'     => 'globe',
        'width'    => '1/2'
    ],
    'direction' => [
        'label'    => t('language.direction'),
        'type'     => 'select',
        'required' => true,
        'empty'    => false,
        'options'  => [
            ['value' => 'ltr', 'text' => t('language.direction.ltr')],
            ['value' => 'rtl', 'text' => t('language.direction.rtl')]
        ],
        'width'    => '1/2'
    ],
    'locale' => [
        'label' => t('language.locale'),
        'type'  => 'text',
    ],
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
                    'submitButton' => t('language.create'),
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
            kirby()->languages()->create([
                'code'      => get('code'),
                'direction' => get('direction'),
                'locale'    => get('locale'),
                'name'      => get('name'),
            ]);
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
                    'text' => tt('language.delete.confirm', [
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
                    'text'  => t('language.locale.warning')
                ];
            }

            return [
                'component' => 'k-language-dialog',
                'props' => [
                    'fields'       => $fields,
                    'submitButton' => t('save'),
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
            $language = Find::language($id)->update([
                'direction' => get('direction'),
                'locale'    => get('locale'),
                'name'      => get('name'),
            ]);
            return [
                'event' => 'language.update'
            ];
        }
    ],
];
