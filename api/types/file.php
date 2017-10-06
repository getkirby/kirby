<?php

return function ($file, $arguments) {

    return [
        'id'       => $file->id(),
        'filename' => $file->filename(),
        'url'      => $file->url(),
        'meta'     => $file->meta()->toArray(),
    ];

};
