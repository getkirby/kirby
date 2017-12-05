<?php

// TODO: enable/disable via options
if (false) {

    /**
     * Sentry Error Logging
     */
    $client = new Raven_Client('https://633c43e3b08940f087e618bd19e30722:5a9a7260a4164e56b2a8eacbb7a91e46@sentry.io/230268');
    $client->tags_context([
        'php_version' => phpversion(),
        'kirby' => json_decode(file_get_contents(__DIR__ . '/../composer.json'), true)['version']
    ]);

    $errorHandler = new Raven_ErrorHandler($client);
    $errorHandler->registerExceptionHandler();
    $errorHandler->registerErrorHandler();
    $errorHandler->registerShutdownFunction();

}

