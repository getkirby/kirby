<?php

return function ($page) {

    $gallery = $page->images()->filterBy("template", "image")->sortBy("sort");

    return [
        'gallery' => $gallery
    ];

};
