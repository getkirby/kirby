<?php

return [
    'setup' => function ($model, $params): array {

        $options = [];

        foreach ($params['options'] as $value => $text) {
            $options[] = [
                'value' => $value,
                'text'  => $text
            ];
        }

        return [
            'options' => $options
        ];

    }
];
