<?php

return [
    'save' => false,
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'autofocus'   => null,
        'before'      => null,
        'default'     => null,
        'disabled'    => null,
        'help'        => null,
        'icon'        => null,
        'label'       => null,
        'placeholder' => null,
        'required'    => null,
        'translate'   => null,
        'value'       => null,
        /**
         * Changes the size of the line. Available sizes: `small`, `medium`, `large`, `huge`
         */
        'size' => function (string $size = null) {
            return $size;
        }
    ]
];
