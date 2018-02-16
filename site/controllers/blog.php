<?php

return function($kirby, $page) {

    $perpage  = $page->perpage()->or(5)->toInt();
    $articles = $kirby->collection('articles')->paginate($perpage);

    return [
        'articles'   => $articles,
        'pagination' => $articles->pagination()
    ];

};
