<?php

require '../../kirby/bootstrap.php';

$kirby = new Kirby([
    'options' => [
        'debug' => true,
    ],
    'roots' => [
        'index'  => __DIR__,
    ],
    'site' => [
        'content' => [
            'title' => 'Kirby Virtual Site'
        ],
        'children' => [
            [
                'slug'    => 'home',
                'content' => [
                    'title' => 'Home',
                    'text'  => 'The home page'
                ]
            ],
            [
                'slug'    => 'blog',
                'num'     => 1,
                'content' => [
                    'title' => 'Blog',
                    'text'  => 'The blog'
                ]
            ],
            [
                'slug'    => 'about',
                'num'     => 2,
                'content' => [
                    'title' => 'About us',
                    'text'  => 'The about page'
                ]
            ],
            [
                'slug'    => 'contact',
                'num'     => 3,
                'content' => [
                    'title' => 'Contact',
                    'text'  => 'The contact page'
                ]
            ],
            [
                'slug'    => 'error',
                'content' => [
                    'title' => 'Error',
                    'text'  => 'Something went wrong'
                ]
            ]
        ],
    ],
    'routes' => [
        'something' => [
            'pattern' => 'something',
            'action'  => function () {
                return new Page([
                    'slug'    => 'something',
                    'content' => [
                        'title' => 'Yay Yay Yay'
                    ]
                ]);
            }
        ]
    ],
]);

echo $kirby->render();
