<?php

use Kirby\Cms\Find;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Str;

$files = require __DIR__ . '/../files/dialogs.php';

return [

    // change page position
    'page.changeSort' => [
        'pattern' => 'pages/(:any)/changeSort',
        'load' => function (string $id) {
            $page     = Find::page($id);
            $position = null;

            if ($page->blueprint()->num() !== 'default') {
                throw new PermissionException([
                    'key'  => 'page.sort.permission',
                    'data' => [
                        'slug' => $page->slug()
                    ]
                ]);
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'position' => Field::pagePosition($page),
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'position' => $page->panel()->position()
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
    'page.changeStatus' => [
        'pattern' => 'pages/(:any)/changeStatus',
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

            if ($status === 'draft') {
                $errors = $page->errors();

                // switch to the error dialog if there are
                // errors and the draft cannot be published
                if (count($errors) > 0) {
                    return [
                        'component' => 'k-error-dialog',
                        'props'     => [
                            'message' => t('error.page.changeStatus.incomplete'),
                            'details' => $errors,
                        ]
                    ];
                }
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
                $fields['position'] = Field::pagePosition($page, [
                    'when' => [
                        'status' => 'listed'
                    ]
                ]);

                $position = $page->panel()->position();
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
    'page.changeTemplate' => [
        'pattern' => 'pages/(:any)/changeTemplate',
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
                        'template' => Field::template($blueprints, [
                            'required' => true
                        ])
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
    'page.changeTitle' => [
        'pattern' => 'pages/(:any)/changeTitle',
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
                            'disabled'  => $permissions->can('changeSlug') === false,
                            'wizard'    => [
                                'text'  => t('page.changeSlug.fromTitle'),
                                'field' => 'title'
                            ]
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
            $title = trim(get('title', ''));
            $slug  = trim(get('slug', ''));

            // basic input validation before we move on
            if (Str::length($title) === 0) {
                throw new InvalidArgumentException([
                    'key' => 'page.changeTitle.empty'
                ]);
            }

            if (Str::length($slug) === 0) {
                throw new InvalidArgumentException([
                    'key' => 'page.slug.invalid'
                ]);
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
                        $oldUrl = $page->panel()->url(true),
                        $newUrl = $newPage->panel()->url(true)
                    ]
                ];

                // check for a necessary redirect after the slug has changed
                if (Panel::referrer() === $oldUrl && $oldUrl !== $newUrl) {
                    $response['redirect'] = $newUrl;
                }
            }

            return $response;
        }
    ],

    // create a new page
    'page.create' => [
        'pattern' => 'pages/create',
        'load' => function () {
            // the parent model for the new page
            $parent = get('parent', 'site');

            // the view on which the add button is located
            // this is important to find the right section
            // and provide the correct templates for the new page
            $view = get('view', $parent);

            // templates will be fetched depending on the
            // section settings in the blueprint
            $section = get('section');

            // this is the parent model
            $model = Find::parent($parent);

            // this is the view model
            // i.e. site if the add button is on
            // the dashboard
            $view = Find::parent($view);

            // available blueprints/templates for the new page
            // are always loaded depending on the matching section
            // in the view model blueprint
            $blueprints = $view->blueprints($section);

            // the pre-selected template
            $template = $blueprints[0]['name'] ?? $blueprints[0]['value'] ?? null;

            $fields = [
                'parent' => Field::hidden(),
                'title'  => Field::title([
                    'required'  => true,
                    'preselect' => true
                ]),
                'slug'   => Field::slug([
                    'required' => true,
                    'sync'     => 'title',
                    'path'     => empty($model->id()) === false ? '/' . $model->id() . '/' : '/'
                ]),
                'template' => Field::hidden()
            ];

            // only show template field if > 1 templates available
            // or when in debug mode
            if (count($blueprints) > 1 || option('debug') === true) {
                $fields['template'] = Field::template($blueprints, [
                    'required' => true
                ]);
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
            $title = trim(get('title', ''));

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
    'page.delete' => [
        'pattern' => 'pages/(:any)/delete',
        'load' => function (string $id) {
            $page = Find::page($id);
            $text = tt('page.delete.confirm', [
                'title' => Escape::html($page->title()->value())
            ]);

            if ($page->childrenAndDrafts()->count() > 0) {
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
            }

            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => $text
                ]
            ];
        },
        'submit' => function (string $id) {
            $page     = Find::page($id);
            $redirect = false;
            $referrer = Panel::referrer();
            $url      = $page->panel()->url(true);

            if ($page->childrenAndDrafts()->count() > 0 && get('check') !== $page->title()->value()) {
                throw new InvalidArgumentException(['key' => 'page.delete.confirm']);
            }

            $page->delete(true);

            // redirect to the parent model URL
            // if the dialog has been opened in the page view
            if ($referrer === $url) {
                $redirect = $page->parentModel()->panel()->url(true);
            }

            return [
                'event'    => 'page.delete',
                'dispatch' => ['content/remove' => [$url]],
                'redirect' => $redirect
            ];
        }
    ],

    // duplicate page
    'page.duplicate' => [
        'pattern' => 'pages/(:any)/duplicate',
        'load' => function (string $id) {
            $page            = Find::page($id);
            $hasChildren     = $page->hasChildren();
            $hasFiles        = $page->hasFiles();
            $toggleWidth     = '1/' . count(array_filter([$hasChildren, $hasFiles]));

            $fields = [
                'title' => Field::title([
                    'required' => true
                ]),
                'slug' => Field::slug([
                    'required' => true,
                    'path'     => $page->parent() ? '/' . $page->parent()->id() . '/' : '/',
                    'wizard'   => [
                        'text'  => t('page.changeSlug.fromTitle'),
                        'field' => 'title'
                    ]
                ])
            ];

            if ($hasFiles === true) {
                $fields['files'] = [
                    'label'    => t('page.duplicate.files'),
                    'type'     => 'toggle',
                    'required' => true,
                    'width'    => $toggleWidth
                ];
            }

            if ($hasChildren === true) {
                $fields['children'] = [
                    'label'    => t('page.duplicate.pages'),
                    'type'     => 'toggle',
                    'required' => true,
                    'width'    => $toggleWidth
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
                        'slug'     => $page->slug() . '-' . Str::slug(t('page.duplicate.appendix')),
                        'title'    => $page->title() . ' ' . t('page.duplicate.appendix')
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            $newPage = Find::page($id)->duplicate(get('slug'), [
                'children' => (bool)get('children'),
                'files'    => (bool)get('files'),
                'title'    => (string)get('title'),
            ]);

            return [
                'event'    => 'page.duplicate',
                'redirect' => $newPage->panel()->url(true)
            ];
        }
    ],

    // change filename
    'page.file.changeName' => [
        'pattern' => '(pages/.*?)/files/(:any)/changeName',
        'load'    => $files['changeName']['load'],
        'submit'  => $files['changeName']['submit'],
    ],

    // change sort
    'page.file.changeSort' => [
        'pattern' => '(pages/.*?)/files/(:any)/changeSort',
        'load'    => $files['changeSort']['load'],
        'submit'  => $files['changeSort']['submit'],
    ],

    // delete
    'page.file.delete' => [
        'pattern' => '(pages/.*?)/files/(:any)/delete',
        'load'    => $files['delete']['load'],
        'submit'  => $files['delete']['submit'],
    ],

    // change site title
    'site.changeTitle' => [
        'pattern' => 'site/changeTitle',
        'load' => function () {
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'title' => Field::title([
                            'required'  => true,
                            'preselect' => true
                        ])
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

    // change filename
    'site.file.changeName' => [
        'pattern' => '(site)/files/(:any)/changeName',
        'load'    => $files['changeName']['load'],
        'submit'  => $files['changeName']['submit'],
    ],

    // change sort
    'site.file.changeSort' => [
        'pattern' => '(site)/files/(:any)/changeSort',
        'load'    => $files['changeSort']['load'],
        'submit'  => $files['changeSort']['submit'],
    ],

    // delete
    'site.file.delete' => [
        'pattern' => '(site)/files/(:any)/delete',
        'load'    => $files['delete']['load'],
        'submit'  => $files['delete']['submit'],
    ],

];
