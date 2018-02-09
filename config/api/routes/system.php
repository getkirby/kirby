<?php

/**
 * System Routes
 */
return [

    [
        'pattern' => 'system',
        'method'  => 'GET',
        'action'  => function () {

            $system = $this->kirby()->system();

            return [
                'isOk'        => $system->isOk(),
                'isInstalled' => $system->isInstalled(),
                'details'     => $system->toArray()
            ];

        }
    ]

];
