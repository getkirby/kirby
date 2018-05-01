<?php

return [
    'props' => [
        'homer' => function ($homer = 'simpson') {
            return $homer;
        },
        'peter' => function ($peter = 'pan') {
            return $peter;
        }
    ]
];
