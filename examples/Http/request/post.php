<?php

require '../../vendor/autoload.php';

use Kirby\Http\Request;

$request = new Request;

if ($request->is('post')) {
    $user = $request->get([
        'username',
        'password',
        'email'
    ]);

    // do something with the submitted data here …
}
