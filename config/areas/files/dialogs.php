<?php

use Kirby\Cms\Find;

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
