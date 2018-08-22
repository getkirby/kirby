<?php

use Kirby\Toolkit\Pagination;

return [
    'props' => [
        'limit' => function (int $limit = 20) {
            return $limit;
        },
        'page' => function (int $page = 1) {
            return $page;
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
