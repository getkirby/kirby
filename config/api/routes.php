<?php

/**
 * Api Routes Definitions
 */
return array_merge(
    include __DIR__ . '/routes/auth.php',
    include __DIR__ . '/routes/pages.php',
    include __DIR__ . '/routes/roles.php',
    include __DIR__ . '/routes/site.php',
    include __DIR__ . '/routes/users.php',
    include __DIR__ . '/routes/system.php',
    include __DIR__ . '/routes/translations.php'
);
