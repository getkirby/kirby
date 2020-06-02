<?php

return [
    'props' => [
        /**
         * Deprecated! To be removed in 3.6
         */
        'image' => function ($image = null) {
            return $image;
        },
        /**
         * Image/icon options to control the source and look of previews
         */
        'preview' => function ($preview = null) {
            return $preview;
        }
    ],
    'computed' => [
        'preview' => function () {
            return $this->preview ?? $this->image ?? [];
        }
    ]
];
