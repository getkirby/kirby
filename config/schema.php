<?php

return function () {

    $root = __DIR__ . '/../fields';

    return [
        'checkboxes' => require $root . '/checkboxes.php',
        'radio'      => require $root . '/radio.php',
        'select'     => require $root . '/select.php',
        'structure'  => require $root . '/structure.php',
        'table'      => require $root . '/table.php',
        'tags'       => require $root . '/tags.php',
    ];

};
