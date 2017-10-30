<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/extensions/helpers.php';
require __DIR__ . '/extensions/methods.php';

/**
 * Sentry Error Logging
 */
// $client = new Raven_Client('https://633c43e3b08940f087e618bd19e30722:5a9a7260a4164e56b2a8eacbb7a91e46@sentry.io/230268');

// $errorHandler = new Raven_ErrorHandler($client);
// $errorHandler->registerExceptionHandler();
// $errorHandler->registerErrorHandler();
// $errorHandler->registerShutdownFunction();

return new Kirby\Cms\App();


