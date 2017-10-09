<?php

return function ($file) {

    return [
        'id'       => $file->id(),
        'filename' => $file->filename(),
        'url'      => $file->url(),
        'meta'     => $file->meta()->toArray(),
    ];

};
