<?php

require '../vendor/autoload.php';

use Kirby\Html\Element\Img;

echo new Img('https://raw.githubusercontent.com/getkirby/starterkit/master/content/1-projects/1-project-a/closeup.jpg', [
    'alt'   => 'Fancy Photo',
    'width' => 200
]);
