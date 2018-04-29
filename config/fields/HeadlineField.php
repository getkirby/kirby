<?php

return [
    'props' => [
        'numbered' => true
    ],
    'methods' => [
        'isNumbered' => function (): bool {
            return $this->numbered();
        }
    ]
];
