<?php

/**
 * System Routes
 */
return [

    [
        'pattern' => 'system',
        'method'  => 'GET',
        'auth'    => false,
        'action'  => function () {

            $system = $this->kirby()->system();

            return [
                'isOk'        => $system->isOk(),
                'isInstalled' => $system->isInstalled(),
                'details'     => $system->toArray()
            ];

        }
    ],
    [
        'pattern' => 'system/install',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $system = $this->kirby()->system();

            if ($system->isOk() === false) {
                throw new Exception('The server is not setup correctly');
            }

            if ($system->isInstalled() === true) {
                throw new Exception('The panel is already installed');
            }

            // create the first user
            return $this->users()->create($this->requestBody());
        }
    ]

];
