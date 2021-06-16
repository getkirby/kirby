<?php

use Kirby\Cms\Find;
use Kirby\Exception\Exception;
use Kirby\Panel\Field;

return [

    // change page position
    'pages/(:any)/changeSort' => [
        'load' => function (string $id) {
            $page     = Find::page($id);
            $position = null;

            if ($page->blueprint()->num() !== 'default') {
                // TODO: make translatable
                throw new Exception('You cannot change the position of this page manually');
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'position' => Field::position($page),
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'position' => $page->num() ?? $page->siblings(false)->count() + 1
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::page($id)->changeStatus('listed', get('position'));
            return [
                'event' => 'page.sort',
            ];
        }
    ],

    // change page status
    'pages/(:any)/changeStatus' => [
        'load' => function (string $id) {
            $page      = Find::page($id);
            $blueprint = $page->blueprint();
            $status    = $page->status();
            $states    = [];
            $position  = null;

            foreach ($blueprint->status() as $key => $state) {
                $states[] = [
                    'value' => $key,
                    'text'  => $state['label'],
                    'info'  => $state['text'],
                ];
            }

            $fields = [
                'status' => [
                    'label'    => t('page.changeStatus.select'),
                    'type'     => 'radio',
                    'required' => true,
                    'options'  => $states
                ]
            ];

            if ($blueprint->num() === 'default') {
                $fields['position'] = Field::position($page, [
                    'when' => [
                        'status' => 'listed'
                    ]
                ]);

                $position = $page->num() ?? $page->siblings(false)->count() + 1;
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields'       => $fields,
                    'submitButton' => t('change'),
                    'value' => [
                        'status'   => $status,
                        'position' => $position
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::page($id)->changeStatus(get('status'), get('position'));
            return [
                'event' => 'page.changeStatus',
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

];
