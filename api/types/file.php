<?php

return function ($file) {

    $output = [
        'id'         => $file->id(),
        'name'       => $file->name(),
        'filename'   => $file->filename(),
        'extension'  => $file->extension(),
        'url'        => $file->url(),
        'mime'       => $file->mime(),
        'type'       => $file->type(),
        'content'    => $this->output('file/content', $file),
        'parent'     => $file->page() ? $file->page()->id(): null,
        'niceSize'   => $file->niceSize(),
        'dimensions' => (string)$file->dimensions(),
        'created'    => date('d.m.Y - H:i:s', filectime($file->root())),
        'modified'   => $file->modified(),
        'next'       => null,
        'prev'       => null
    ];

    if ($prev = $file->prev()) {
        $output['prev'] = $prev->filename();
    }

    if ($next = $file->next()) {
        $output['next'] = $next->filename();
    }

    return $output;

};
