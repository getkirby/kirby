<?php

return [
    'mixins' => ['options'],
    'props' => [
        'columns' => function (int $columns = 1) {
            return $columns;
        },
    ],
    'computed' => [
        'options' => function (): array {
            return $this->getOptions();
        },
        'default' => function () {
            return $this->sanitizeOption($this->default);
        },
        'value' => function () {
            return $this->sanitizeOption($this->value);
        }
    ]
];
