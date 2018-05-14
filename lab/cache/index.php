<?php

require '../../kirby/bootstrap.php';

$kirby = new Kirby([
    'options' => [
        'debug' => true,
        'cache.example' => [
            'active' => true,
            'type'   => 'file'
        ],
    ],
    'roots' => [
        'index' => __DIR__,
        'cache' => __DIR__ . '/cache'
    ]
]);

// get our cache setup
$cache = $kirby->cache('example');

// try to fetch posts from cache first
// and otherwise get them from the API
if (!$posts = $cache->get('posts')) {
    $posts = file_get_contents('https://jsonplaceholder.typicode.com/posts');
    $cache->set('posts', $posts);
}

var_dump(json_decode($posts));

