<?php

return function ($site) {

    return [
        'url'     => $site->url(),
        'content' => $site->content()->toArray(),
    ];

};
