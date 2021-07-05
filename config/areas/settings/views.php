<?php

return [
    [
        'pattern' => 'settings',
        'action'  => function () {
            $kirby   = kirby();
            $license = $kirby->system()->license();

            // @codeCoverageIgnoreStart
            if ($license === true) {
                // valid license, but user is not admin
                $license = 'Kirby 3';
            } elseif ($license === false) {
                // no valid license
                $license = null;
            }
            // @codeCoverageIgnoreEnd

            return [
                'component' => 'k-settings-view',
                'props'     => [
                    'languages' => $kirby->languages()->values(function ($language) {
                        // todo: probably escape info and text (output with `v-html`)
                        return [
                            'default' => $language->isDefault(),
                            'id'      => $language->code(),
                            'info'    => $language->code(),
                            'text'    => $language->name(),
                        ];
                    }),
                    'license' => $license,
                    'version' => $kirby->version(),
                ]
            ];
        }
    ],
];
