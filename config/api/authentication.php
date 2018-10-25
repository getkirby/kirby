<?php

return function () {

    // get all api options
    $kirby     = $this->kirby();
    $options   = $kirby->option('api', []);
    $basicAuth = $options['basicAuth'] ?? false;

    // check for a valid csrf
    // when basic auth is disabled
    if ($basicAuth === false) {

        // get the csrf from the header
        $fromHeader = $this->requestHeaders('x-csrf');

        // check for a predefined csrf or use the one from session
        $fromSession = $options['csrf'] ?? csrf();

        // compare both tokens
        if (hash_equals((string)$fromHeader, (string)$fromSession) !== true) {
            throw new Exception('Invalid csrf token', 403);
        }

    }

    if ($user = $kirby->user()) {
        return $user;
    }

    throw new Exception('Unauthenticated', 403);

};
