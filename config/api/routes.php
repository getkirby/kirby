<?php

/**
 * Api Routes Definitions
 */
return array_merge(
    include __DIR__ . '/routes/locales.php',
    include __DIR__ . '/routes/pages.php',
    include __DIR__ . '/routes/site.php',
    include __DIR__ . '/routes/users.php',
    include __DIR__ . '/routes/session.php',
    include __DIR__ . '/routes/system.php'
);
