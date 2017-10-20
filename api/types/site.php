<?php

return function ($site) {

    return [
        'url'     => $site->url(),
        'title'   => $site->title()->value(),
        'content' => $site->content()->toArray(),
    ];

};
