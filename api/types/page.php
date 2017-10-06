<?php

return function ($page, $arguments) {

    return [
        'id'       => $page->id(),
        'slug'     => $page->slug(),
        'url'      => $page->url(),
        'num'      => $page->num(),
        'template' => $page->template(),
        'content'  => $page->content()->toArray(),
    ];

};
