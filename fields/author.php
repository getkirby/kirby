<?php

return [
    'validate' => function ($input) {

        if (kirby()->user($input) !== null) {
            return true;
        }

        return false;

    }
];
