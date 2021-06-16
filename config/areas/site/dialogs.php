<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;

return [

    // duplicate page
    'pages/(:any)/duplicate' => [
        'load' => function (string $id) {
            $page        = Find::page($id);
            $hasChildren = $page->hasChildren();
            $hasFiles    = $page->hasFiles();

            $fields = [
                'slug' => Field::slug([
                    'required' => true
                ])
            ];

            if ($hasFiles) {
                $fields['files'] = [
                    'label'    => t('page.duplicate.files'),
                    'type'     => 'toggle',
                    'required' => true,
                    'width'    => $hasChildren ? '1/2' : '1/1'
                ];
            }

            if ($hasChildren) {
                $fields['children'] = [
                    'label'    => t('page.duplicate.pages'),
                    'type'     => 'toggle',
                    'required' => true,
                    'width'    => $hasFiles ? '1/2' : '1/1'
                ];
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields'       => $fields,
                    'submitButton' => t('duplicate'),
                    'value' => [
                        'children' => false,
                        'files'    => false,
                        'slug'     => $page->slug() . '-' . Str::slug(t('page.duplicate.appendix'))
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            $newPage = Find::page($id)->duplicate(get('slug'), [
                'children' => get('children'),
                'files'    => get('files'),
            ]);

            return [
                'event'    => 'page.duplicate',
                'redirect' => $newPage->panel()->url(true)
            ];
        }
    ],

    // change site title
    'site/changeTitle' => [
        'load' => function () {
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'title' => Field::title()
                    ],
                    'submitButton' => t('rename'),
                    'value' => [
                        'title' => site()->title()->value()
                    ]
                ]
            ];
        },
        'submit' => function () {
            site()->changeTitle(get('title'));
            return [
                'event' => 'site.changeTitle',
            ];
        }
    ],
];
