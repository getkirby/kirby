<?php

use Kirby\Cms\User;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
    'avatar.valid.mime' => function (string $mime) {
        if ($mime !== 'image/jpeg') {
            throw new Exception('User profile images must be JPEG files');
        }
    },
    'avatar.valid.dimensions' => function (int $width, int $height) {
        if ($width > 3000 || $height > 3000) {
            throw new Exception('Please keep the width and height of the profile image below 3000 pixel');
        }
    },
];
