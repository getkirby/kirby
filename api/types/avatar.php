<?php

return function ($avatar) {

    $output = [
        'name'       => $avatar->name(),
        'filename'   => $avatar->filename(),
        'extension'  => $avatar->extension(),
        'url'        => $avatar->url(),
        'exists'     => $avatar->exists(),
        'mime'       => 'image/jpeg',
        'niceSize'   => 0,
        'dimensions' => null,
        'created'    => null,
        'modified'   => null,
    ];

    if ($avatar->exists()) {
        $output = array_merge($output,[
            'niceSize'   => $avatar->niceSize(),
            'dimensions' => (string)$avatar->dimensions(),
            'created'    => date('d.m.Y - H:i:s', filectime($avatar->root())),
            'modified'   => $avatar->modified()
        ]);
    }

    return $output;

};
