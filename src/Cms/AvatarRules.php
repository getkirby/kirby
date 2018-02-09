<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

class AvatarRules
{

    public static function create(Avatar $avatar, Image $source): bool
    {
        static::validMime($avatar, $source->mime());
        static::validDimensions($avatar, $source->width(), $source->height());

        return true;
    }

    public static function delete(Avatar $avatar): bool
    {
        return true;
    }

    public static function replace(Avatar $avatar, Image $source): bool
    {
        return static::create($avatar, $source);
    }

    public static function validMime(Avatar $avatar, string $mime): bool
    {
        if ($mime !== 'image/jpeg') {
            throw new Exception('User profile images must be a JPEG file');
        }

        return true;
    }

    public static function validDimensions(Avatar $avatar, int $width, int $height): bool
    {
        if ($width > 3000 || $height > 3000) {
            throw new Exception('Please keep the width and height of the profile image below 3000 pixel');
        }

        return true;
    }

}
