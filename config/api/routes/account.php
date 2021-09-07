<?php

/**
 * Account
 */
return [
    [
        'pattern' => 'account',
        'action'  => function () {
            return $this->user();
        }
    ],
    [
        'pattern' => 'account',
        'method'  => 'PATCH',
        'action'  => function () {
            return $this->user()->update($this->requestBody(), $this->language(), true);
        }
    ],
];
