<?php

require '../vendor/autoload.php';

use Kirby\Toolkit\View;

echo new View('template.php', [
    'title'    => 'Kirby View Example',
    'headline' => 'Kirby View',
    'text'     => '
        Kirby View is a very simple template system
        implemented in plain PHP. View can be dropped
        into any project with minimal effort to render
        HTML or any other form of template with
        injected data. Views can be nested for more
        flexibility.
    '
]);
