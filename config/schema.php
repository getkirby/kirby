<?php

$root = __DIR__ . '/../fields';

return [
    'checkbox'   => require $root . '/checkbox.php',
    'checkboxes' => require $root . '/checkboxes.php',
    'number'     => require $root . '/number.php',
    'radio'      => require $root . '/radio.php',
    'select'     => require $root . '/select.php',
    'structure'  => require $root . '/structure.php',
    'table'      => require $root . '/table.php',
    'tags'       => require $root . '/tags.php',
    'toggle'     => require $root . '/toggle.php',
];
