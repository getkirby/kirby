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
    // delete file
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
