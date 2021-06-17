<?php

use Kirby\Cms\Find;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
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

    // change template
    'pages/(:any)/changeTemplate' => [
        'load' => function (string $id) {
            $page       = Find::page($id);
            $blueprints = $page->blueprints();

            if (count($blueprints) <= 1) {
                throw new Exception([
                    'key'  => 'page.changeTemplate.invalid',
                    'data' => [
                        'slug' => $id
                    ]
                ]);
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'template' => Field::template($blueprints)
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'template' => $page->intendedTemplate()->name()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::page($id)->changeTemplate(get('template'));
            return [
                'event' => 'page.changeTemplate',
            ];
        }
    ],

    // change title
    'pages/(:any)/changeTitle' => [
        'load' => function (string $id) {
            $page        = Find::page($id);
            $permissions = $page->permissions();
            $select      = get('select', 'title');

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'title' => Field::title([
                            'required'  => true,
                            'preselect' => $select === 'title',
                            'disabled'  => $permissions->can('changeTitle') === false
                        ]),
                        'slug' => Field::slug([
                            'required'  => true,
                            'preselect' => $select === 'slug',
                            'path'      => $page->parent() ? '/' . $page->parent()->id() . '/' : '/',
                            'disabled'  => $permissions->can('changeSlug') === false
                        ])
                    ],
                    'autofocus' => false,
                    'submitButton' => t('change'),
                    'value' => [
                        'title' => $page->title()->value(),
                        'slug'  => $page->slug(),
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            $page  = Find::page($id);
            $title = trim(get('title'));
            $slug  = trim(get('slug'));

            // basic input validation before we move on
            if (Str::length($title) === 0) {
                throw new InvalidArgumentException(['key' => 'page.changeTitle.empty']);
            }

            if (Str::length($slug) === 0) {
                throw new InvalidArgumentException(['key' => 'page.slug.invalid']);
            }

            // nothing changed
            if ($page->title()->value() === $title && $page->slug() === $slug) {
                return true;
            }

            // prepare the response
            $response = [
                'event' => []
            ];

            // the page title changed
            if ($page->title()->value() !== $title) {
                $page->changeTitle($title);
                $response['event'][] = 'page.changeTitle';
            }

            // the slug changed
            if ($page->slug() !== $slug) {
                $newPage = $page->changeSlug($slug);
                $response['event'][] = 'page.changeSlug';
                $response['dispatch'] = [
                    'content/move' => [
                        $page->panel()->url(true),
                        $newPage->panel()->url(true)
                    ]
                ];

                // TODO: redirect if in page view
            }

            return $response;
        }
    ],

    // change create
    'pages/create' => [
        'load' => function () {
            $parent     = get('parent', 'site');
            $section    = get('section');
            $model      = Find::parent($parent);
            $blueprints = $model->blueprints($section);
            $template   = null;

            $fields = [
                'parent' => Field::hidden(),
                'title'  => Field::title(),
                'slug'   => Field::slug([
                    'required' => true,
                    'sync'     => 'title',
                    'path'     => empty($model->id()) === false ? '/' . $model->id() . '/' : '/'
                ])
            ];

            if (count($blueprints) > 1 || option('debug') === true) {
                $fields['template'] = Field::template($blueprints);

                // preselect the first available template
                $template = $fields['template']['options'][0]['value'];
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => $fields,
                    'submitButton' => t('page.draft.create'),
                    'value' => [
                        'parent'   => $parent,
                        'slug'     => '',
                        'template' => $template,
                        'title'    => '',
                    ]
                ]
            ];
        },
        'submit' => function () {
            $title = trim(get('title'));

            if (Str::length($title) === 0) {
                throw new InvalidArgumentException([
                    'key' => 'page.changeTitle.empty'
                ]);
            }

            $page = Find::parent(get('parent', 'site'))->createChild([
                'content'  => ['title' => $title],
                'slug'     => get('slug'),
                'template' => get('template'),
            ]);

            return [
                'event'    => 'page.create',
                'redirect' => $page->panel()->url(true)
            ];
        }
    ],

    // delete page
    'pages/(:any)/delete' => [
        'load' => function (string $id) {
            $page        = Find::page($id);
            $hasSubpages = $page->childrenAndDrafts()->count();
            $text        = tt('page.delete.confirm', ['title' => $page->title()->value()]);

            if ($hasSubpages) {
                return [
                    'component' => 'k-form-dialog',
                    'props' => [
                        'fields' => [
                            'info' => [
                                'type'  => 'info',
                                'theme' => 'negative',
                                'text'  => t('page.delete.confirm.subpages')
                            ],
                            'check' => [
                                'label'   => t('page.delete.confirm.title'),
                                'type'    => 'text',
                                'counter' => false
                            ]
                        ],
                        'size'         => 'medium',
                        'submitButton' => t('delete'),
                        'text'         => $text,
                        'theme'        => 'negative',
                    ]
                ];
            } else {
                return [
                    'component' => 'k-remove-dialog',
                    'props' => [
                        'text' => $text
                    ]
                ];
            }
        },
        'submit' => function (string $id) {
            $page = Find::page($id);

            if ($page->childrenAndDrafts()->count() > 0 && get('check') !== $page->title()->value()) {
                throw new InvalidArgumentException(['key' => 'page.delete.confirm']);
            }

            $page->delete(true);

            return [
                'event' => 'page.delete',
                'dispatch' => [
                    'content/remove' => ['pages/' . $page->panel()->id()]
                ]
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
                    'required' => true,
                    'path'     => $page->parent() ? '/' . $page->parent()->id() . '/' : '/'
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
