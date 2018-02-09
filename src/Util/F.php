<?php

namespace Kirby\Util;

use Exception;

class F
{

    /**
     * Copy a file to a new location.
     *
     * @param  string  $file
     * @param  string  $target
     * @return boolean
     */
    public static function copy(string $source, string $target): bool
    {
        if (file_exists($source) === false || file_exists($target) === true) {
            return false;
        }

        $directory = dirname($target);

        // create the parent directory if it does not exist
        if (is_dir($directory) === false) {
            Dir::make($directory, true);
        }

        return copy($source, $target);
    }

    public static function exists(string $file, string $in = null): bool
    {
        try {
            static::realpath($file, $in);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the extension of a file
     *
     * @param  string  $file The filename or path
     * @param  string  $extension Set an optional extension to overwrite the current one
     * @return string
     */
    public static function extension(string $file, string $extension = null): string
    {

        // overwrite the current extension
        if($extension !== null) {
            return static::name($file) . '.' . $extension;
        }

        // return the current extension
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));

    }

    /**
     * Moves a file to a new location
     *
     * <code>
     *
     * $move = F::move('test.txt', 'super.txt');
     *
     * if($move) echo 'The file has been moved';
     *
     * </code>
     *
     * @param  string $old The current path for the file
     * @param  string $new The path to the new location
     * @return boolean
     */
    public static function move($old, $new): bool
    {
        if ($old === $new) {
            return true;
        }

        if (file_exists($old) === false || file_exists($new) === true) {
            return false;
        }

        return rename($old, $new);
    }

    /**
     * Extracts the name from a file path or filename without extension
     *
     * @param  string  $name The path or filename
     * @return string
     */
    public static function name(string $name): string
    {
        return pathinfo($name, PATHINFO_FILENAME);
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

    /**
     * Deletes a file
     *
     * <code>
     *
     * $remove = F::remove('test.txt');
     * if($remove) echo 'The file has been removed';
     *
     * </code>
     *
     * @param  string  $file The path for the file
     * @return boolean
     */
    public static function remove($file): bool
    {
        $file = realpath($file);

        if (file_exists($file) === false) {
            return true;
        }

        return unlink($file);
    }

}
