<?php

use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Escape;

return [
    'system' => [
        'pattern' => 'system',
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

            $plugins = [];
            $pluginsCollection = new Collection($kirby->plugins());

            foreach ($pluginsCollection->sortBy('name', 'asc') as $plugin) {
                $plugins[] = [
                    'license' => $plugin->license(),
                    'author'  => $plugin->authorsNames(),
                    'name'    => $plugin->name(),
                    'version' => $plugin->version(),
                ];
            }

            return [
                'component' => 'k-system-view',
                'props'     => [
                    'debug'   => $kirby->option('debug'),
                    'license' => $license,
                    'plugins' => $plugins,
                    'php'     => phpversion(),
                    'server'  => $kirby->system()->serverSoftware(),
                    'ssl'     => Server::https(),
                    'version' => $kirby->version(),
                ]
            ];
        }
    ],
];
