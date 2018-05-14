<?php

require '../../kirby/bootstrap.php';

$kirby = new Kirby([
    'roots' => [
        'index'  => __DIR__,
        'static' => __DIR__ . '/static'
    ],
    'urls' => [
        'index' => '/'
    ]
]);


foreach ($kirby->site()->index() as $page) {

    $html = $page->render();

    if ($page->isHomePage()) {
        $file = $kirby->root('static') . '/index.html';
    } else {
        $file = $kirby->root('static') . '/' . $page->id() . '/index.html';
    }

    F::write($file, $html);

}

echo 'Your static site has been generated in ' . $kirby->root('static');
