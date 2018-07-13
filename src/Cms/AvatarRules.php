<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The AvatarRules class contains validators for all avatar actions.
 */
class AvatarRules
{
    public static function create(Avatar $avatar, Upload $source): bool
    {
        static::validMime($avatar, $source->mime());
        return true;
    }

    public static function delete(Avatar $avatar): bool
    {
        return true;
    }

    public static function replace(Avatar $avatar, Upload $source): bool
    {
        return static::create($avatar, $source);
    }

    public static function validMime(Avatar $avatar, string $mime): bool
    {
        // TODO: also allow PNG files
        if ($mime !== 'image/jpeg') {
            throw new InvalidArgumentException([
                'key' => 'avatar.mime.invalid',
            ]);
        }

        return true;
    }
}
