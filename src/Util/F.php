<?php

namespace Kirby\Util;

use Exception;

class F
{

    public static function exists(string $file, string $in = null): bool
    {
        try {
            static::realpath($file, $in);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function realpath(string $file, string $in = null)
    {
        $realpath = realpath($file);

        if ($realpath === false || is_file($realpath) === false) {
            throw new Exception(sprintf('The file does not exist at the given path: "%s"', $file));
        }

        if ($in !== null) {
            $parent = realpath($in);

            if ($parent === false || is_dir($parent) === false) {
                throw new Exception(sprintf('The parent directory does not exist: "%s"', $parent));
            }

            if (substr($realpath, 0, strlen($parent)) !== $parent) {
                throw new Exception('The file is not within the parent directory');
            }
        }

        return $realpath;
    }

}
