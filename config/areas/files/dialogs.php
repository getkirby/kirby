<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;

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
                            'label'     => t('name'),
                            'type'      => 'slug',
                            'required'  => true,
                            'icon'      => 'title',
                            'allow'     => '@._-',
                            'after'     => '.' . $file->extension(),
                            'preselect' => true
                        ]
                    ],
                    'submitButton' => t('rename'),
                    'value' => [
                        'name' => $file->name(),
                    ]
                ]
            ];
        },
        'submit' => function (string $path, string $filename) {
            $file    = Find::file($path, $filename);
            $renamed = $file->changeName(get('name'));
            return [
                'event' => 'file.changeName',
                'dispatch' => [
                    'content/move' => [
                        $file->panel()->url(true),
                        $renamed->panel()->url(true)
                    ]
                ]
            ];
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
                    'submitButton' => t('change'),
                    'value' => [
                        'position' => $file->sort()->isEmpty() ? $file->siblings(false)->count() + 1 : $file->sort()->toInt(),
                    ]
                ]
            ];
        },
        'submit' => function (string $path, string $filename) {
            $file     = Find::file($path, $filename);
            $files    = $file->siblings();
            $ids      = $files->keys();
            $oldIndex = $files->indexOf($file);
            $newIndex = (int)(get('position')) - 1;

            array_splice($ids, $oldIndex, 1);
            array_splice($ids, $newIndex, 0, $file->id());

            $files->changeSort($ids);

            return [
                'event' => 'file.sort',
            ];
        }
    ],

    'delete' => [
        'load' => function (string $path, string $filename) {
            $file = Find::file($path, $filename);
            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => tt('file.delete.confirm', [
                        'filename' => $file->filename()
                    ]),
                ]
            ];
        },
        'submit' => function (string $path, string $filename) {
            $file = Find::file($path, $filename);
            $file->delete();
            return [
                'event'    => 'file.delete',
                'dispatch' => [
                    'content/remove' => [$file->panel()->url(true)]
                ]
            ];
        }
    ],
];
