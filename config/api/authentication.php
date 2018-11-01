<?php

use Kirby\Toolkit\Str;

return function () {

    // get all api options
    $kirby         = $this->kirby();
    $options       = $kirby->option('api', []);
    $basicAuth     = $options['basicAuth'] ?? false;
    $authorization = $this->requestHeaders('Authorization');

    // check for a valid csrf when basic auth is disabled or authorization header is not sent
    if ($basicAuth === false || Str::startsWith($authorization, 'Basic ') !== true) {

        // get the csrf from the header
        $fromHeader = $this->requestHeaders('x-csrf');

        // check for a predefined csrf or use the one from session
        $fromSession = $options['csrf'] ?? csrf();

        // compare both tokens
        if (hash_equals((string)$fromSession, (string)$fromHeader) !== true) {
            throw new Exception('Invalid csrf token', 403);
        }

    }

    if ($user = $kirby->user()) {
        return $user;
    }

    throw new Exception('Unauthenticated', 403);

};
