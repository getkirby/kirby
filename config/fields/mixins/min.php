<?php

return [
    'computed' => [
        'min' => function () {
            // set min to at least 1, if required
            if ($this->required === true) {
                return $this->min ?? 1;
            }

            return $this->min;
        },
        'required' => function () {
            // set required to true if min is set
            if ($this->min) {
                return true;
            }

            return $this->required;
        }
    ]
];
