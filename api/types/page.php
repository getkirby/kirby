<?php

return function ($page) {

    return [
        'id'       => $page->id(),
        'slug'     => $page->slug(),
        'url'      => $page->url(),
        'num'      => $page->num(),
        'template' => $page->template(),
        'content'  => $page->content()->toArray(),
    ];

};
