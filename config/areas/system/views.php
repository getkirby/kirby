<?php


return [
    'system' => [
        'pattern' => 'system',
        'action'  => function () {
            $kirby   = kirby();
            $system  = $kirby->system();
            $license = $system->license();

            // @codeCoverageIgnoreStart
            if ($license === true) {
                // valid license, but user is not admin
                $license = 'Kirby 3';
            } elseif ($license === false) {
                // no valid license
                $license = null;
            }
            // @codeCoverageIgnoreEnd

            $plugins = $system->plugins()->values(function ($plugin) {
                return [
                    'author'  => $plugin->authorsNames(),
                    'license' => $plugin->license(),
                    'link'    => $plugin->link(),
                    'name'    => $plugin->name(),
                    'version' => $plugin->version(),
                ];
            });

            return [
                'component' => 'k-system-view',
                'props'     => [
                    'debug'   => $kirby->option('debug', false),
                    'license' => $license,
                    'plugins' => $plugins,
                    'php'     => phpversion(),
                    'server'  => $system->serverSoftware(),
                    'ssl'     => Server::https(),
                    'version' => $kirby->version(),
                ]
            ];
        }
    ],
];
