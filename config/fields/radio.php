<?php

return [
    'mixins' => ['options'],
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
