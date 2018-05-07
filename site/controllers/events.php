<?php

return function ($page) {

    $events = $page->children()->listed();

    $past = $events->filter(function ($item) {
        return $item->date(null, 'to') < time();
    });

    $future = $events->filter(function ($item) {
        return $item->date(null, 'from') > time();
    });

    $current = $events->filter(function ($item) {
        return $item->date(null, 'to') >= time() && $item->date(null, 'from') <= time();
    });

    return [
        'past' => $past,
        'current' => $current,
        'future' => $future
    ];

};
