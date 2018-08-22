<?php

use Kirby\Toolkit\Pagination;

return [
    'props' => [
        'limit' => function (int $limit = 20) {
            return $limit;
        },
        'page' => function (int $page = null) {
            return $page ?? get('page', 1);
        },
    ],
    'methods' => [
        'pagination' => function () {
            $pagination = new Pagination([
                'limit' => $this->limit,
                'page'  => $this->page,
                'total' => $this->total
            ]);

            return [
                'limit'  => $pagination->limit(),
                'offset' => $pagination->offset(),
                'page'   => $pagination->page(),
                'total'  => $pagination->total(),
            ];
        },
    ]
];
