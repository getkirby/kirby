<?php

return [
    'setup' => function ($model, $params): array {

        if (is_string($params['options']) === true) {
            return ['options' => $params['options']];
        }

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
