<?php

namespace Kirby\Toolkit;

use Exception;
use Throwable;

/**
 * Low level file handling utilities
 */
class F
{
    public static $types = [
        'archive' => [
            'gz',
            'gzip',
            'tar',
            'tgz',
            'zip',
        ],
        'audio' => [
            'aif',
            'aiff',
            'm4a',
            'midi',
            'mp3',
            'wav',
        ],
        'code' => [
            'css',
            'js',
            'json',
            'java',
            'htm',
            'html',
            'php',
            'rb',
            'py',
            'scss',
            'xml',
            'yml',
        ],
        'document' => [
            'csv',
            'doc',
            'docx',
            'dotx',
            'md',
            'mdown',
            'pdf',
            'ppt',
            'pptx',
            'rtf',
            'txt',
            'xl',
            'xls',
            'xlsx',
            'xltx',
        ],
        'image' => [
            'ai',
            'bmp',
            'gif',
            'eps',
            'ico',
            'jpeg',
            'jpg',
            'jpe',
            'png',
            'ps',
            'psd',
            'svg',
            'tif',
            'tiff',
            'webp'
        ],
        'video' => [
            'avi',
            'flv',
            'm4v',
            'mov',
            'movie',
            'mpe',
            'mpg',
            'mp4',
            'ogg',
            'ogv',
            'swf',
            'webm',
        ],
    ];

    public static $units = ['B','kB','MB','GB','TB','PB', 'EB', 'ZB', 'YB'];


    /**
     * Returns the file content as base64 encoded string
     *
     * @param string $file The path for the file
     * @return string
     */
    public static function base64(string $file): string
    {
        return base64_encode(static::read($file));
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $file
     * @param  string  $target
     * @param  boolean $force
     * @return boolean
     */
    public static function copy(string $source, string $target, bool $force = false): bool
    {
        if (file_exists($source) === false || (file_exists($target) === true && $force === false)) {
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
        if ($extension !== null) {
            return static::name($file) . '.' . $extension;
        }

        // return the current extension
        return Str::lower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * Converts a file extension to a mime type
     *
     * @param string $extension
     * @return string|false
     */
    public static function extensionToMime(string $extension)
    {
        return Mime::fromExtension($extension);
    }

    /**
     * Returns the file type for a passed extension
     *
     * @param string $extension
     * @return string|false
     */
    public static function extensionToType(string $extension)
    {
        foreach (static::$types as $type => $extensions) {
            if (in_array($extension, $extensions) === true) {
                return $type;
            }
        }

        return false;
    }

    /**
     * Returns all extensions for a certain file type
     *
     * @param string $type
     * @return array
     */
    public static function extensions(string $type = null)
    {
        if ($type === null) {
            return array_keys(Mime::types());
        }

        return static::$types[$type] ?? [];
    }

    /**
     * Checks if a file is of a certain type
     *
     * @param string $file Full path to the file
     * @param string $value An extension or mime type
     * @return boolean
     */
    public static function is(string $file, string $value): bool
    {
        // check for the extension
        if (in_array($value, static::extensions()) === true) {
            return static::extension($file) === $value;
        }

        // check for the mime type
        if (strpos($value, '/') !== false) {
            return static::mime($file) === $value;
        }

        return false;
    }

    /**
     * Checks if the file is readable
     *
     * @param string $file
     * @return boolean
     */
    public static function isReadable(string $file): bool
    {
        return is_readable($file);
    }

    /**
     * Checks if the file is writable
     *
     * @param string $file
     * @return boolean
     */
    public static function isWritable(string $file): bool
    {
        if (file_exists($file) === false) {
            return is_writable(dirname($file));
        }

        return is_writable($file);
    }

    /**
     * Create a (symbolic) link to a file
     *
     * @param string $source
     * @param string $link
     * @param string $method
     * @return boolean
     */
    public static function link(string $source, string $link, string $method = 'link'): bool
    {
        Dir::make(dirname($link), true);

        if (is_file($link) === true) {
            return true;
        }

        if (is_file($source) === false) {
            throw new Exception(sprintf('The file "%s" does not exist and cannot be linked', $source));
        }

        return $method($source, $link);
    }

    /**
     * Loads a file and returns the result
     *
     * @param string $file
     * @return mixed
     */
    public static function load(string $file, $fallback = null)
    {
        if (file_exists($file) === false) {
            return $fallback;
        }

        return include $file;
    }

    /**
     * Returns the mime type of a file
     *
     * @param string $file
     * @return string|false
     */
    public static function mime(string $file)
    {
        return Mime::type($file);
    }

    /**
     * Converts a mime type to a file extension
     *
     * @param string $mime
     * @return string|false
     */
    public static function mimeToExtension(string $mime = null)
    {
        return Mime::toExtension($mime);
    }

    /**
     * Returns the type for a given mime
     *
     * @param string $mime
     * @return string|false
     */
    public static function mimeToType(string $mime)
    {
        return static::extensionToType(Mime::toExtension($mime));
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $file
     * @param  string $format
     * @param  string $handler date or strftime
     * @return mixed
     */
    public static function modified(string $file, string $format = null, string $handler = 'date')
    {
        if (file_exists($file) === true) {
            if (is_null($format) === true) {
                return filemtime($file);
            }
            return $handler($format, filemtime($file));
        }
        return false;
    }

    /**
     * Moves a file to a new location
     *
     * @param  string  $oldRoot The current path for the file
     * @param  string  $newRoot The path to the new location
     * @param  boolean $force Force move if the target file exists
     * @return boolean
     */
    public static function move(string $oldRoot, string $newRoot, bool $force = false): bool
    {
        // check if the file exists
        if (file_exists($oldRoot) === false) {
            return false;
        }

        if (file_exists($newRoot) === true) {
            if ($force === false) {
                return false;
            }

            // delete the existing file
            unlink($newRoot);
        }

        // actually move the file if it exists
        if (rename($oldRoot, $newRoot) !== true) {
            return false;
        }

        return true;
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

    /**
     * Converts an integer size into a human readable format
     *
     * @param  mixed $size The file size or a file path
     * @return string|int
     */
    public static function niceSize($size): string
    {
        // file mode
        if (is_string($size) === true && file_exists($size) === true) {
            $size = static::size($size);
        }

        // make sure it's an int
        $size = (int)$size;

        // avoid errors for invalid sizes
        if ($size <= 0) {
            return '0 kB';
        }

        // the math magic
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . static::$units[$i];
    }

    /**
     * Reads the content of a file
     *
     * @param  string $file The path for the file
     * @return string|false
     */
    public static function read(string $file)
    {
        return @file_get_contents($file);
    }

    /**
     * Changes the name of the file without
     * touching the extension
     *
     * @param  string $file
     * @param  string $newName
     * @param  bool   $overwrite Force overwrite existing files
     * @return string|false
     */
    public static function rename(string $file, string $newName, bool $overwrite = false)
    {
        // create the new name
        $name = static::safeName(basename($newName));

        // overwrite the root
        $newRoot = rtrim(dirname($file) . '/' . $name . '.' . F::extension($file), '.');

        // nothing has changed
        if ($newRoot === $file) {
            return $newRoot;
        }

        if (F::move($file, $newRoot) !== true) {
            return false;
        }

        return $newRoot;
    }

    /**
     * @param string $file
     * @param string $in
     * @return string|null
     */
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
    public static function remove(string $file): bool
    {
        if (strpos($file, '*') !== false) {
            foreach (glob($file) as $f) {
                static::remove($f);
            }

            return true;
        }

        $file = realpath($file);

        if (file_exists($file) === false) {
            return true;
        }

        return unlink($file);
    }

    /**
     * Sanitize a filename to strip unwanted special characters
     *
     * <code>
     *
     * $safe = f::safeName('über genious.txt');
     * // safe will be ueber-genious.txt
     *
     * </code>
     *
     * @param  string $string The file name
     * @return string
     */
    public static function safeName(string $string): string
    {
        $name      = static::name($string);
        $extension = static::extension($string);
        $end       = empty($extension) === false ? '.' . Str::slug($extension) : '';

        return Str::slug($name, '-', 'a-z0-9@._-') . $end;
    }

    /**
     * Tries to find similar or the same file by
     * building a glob based on the path
     *
     * @param string $path
     * @return array
     */
    public static function similar(string $path, string $pattern = '*'): array
    {
        $dir       = dirname($path);
        $name      = static::name($path);
        $extension = static::extension($path);
        $glob      = $dir . '/' . $name . $pattern . '.' . $extension;
        return glob($glob);
    }

    /**
     * Returns the size of a file.
     *
     * @param  mixed  $file The path
     * @return int
     */
    public static function size(string $file): int
    {
        try {
            return filesize($file);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Categorize the file
     *
     * @param string $file Either the file path or extension
     * @return string|null
     */
    public static function type(string $file)
    {
        $length = strlen($file);

        if ($length >= 2 && $length <= 4) {
            // use the file name as extension
            $extension = $file;
        } else {
            // get the extension from the filename
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        if (empty($extension) === true) {
            // detect the mime type first to get the most reliable extension
            $mime      = static::mime($file);
            $extension = static::mimeToExtension($mime);
        }

        // sanitize extension
        $extension = strtolower($extension);

        foreach (static::$types as $type => $extensions) {
            if (in_array($extension, $extensions) === true) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Unzips a zip file
     *
     * @param string $file
     * @param string $to
     * @return boolean
     */
    public static function unzip(string $file, string $to): bool
    {
        if (class_exists('ZipArchive') === false) {
            throw new Exception('The ZipArchive class is not available');
        }

        $zip = new ZipArchive;

        if ($zip->open($file) === true) {
            $zip->extractTo($to);
            $zip->close();
            return true;
        }

        return false;
    }

    /**
     * Returns the file as data uri
     *
     * @param string $file The path for the file
     * @return string|false
     */
    public static function uri(string $file)
    {
        if ($mime = static::mime($file)) {
            return 'data:' . $mime . ';base64,' . static::base64($file);
        }

        return false;
    }

    /**
     * Creates a new file
     *
     * @param  string  $file The path for the new file
     * @param  mixed   $content Either a string, an object or an array. Arrays and objects will be serialized.
     * @param  boolean $append true: append the content to an exisiting file if available. false: overwrite.
     * @return boolean
     */
    public static function write(string $file, $content, bool $append = false): bool
    {
        if (is_array($content) === true || is_object($content) === true) {
            $content = serialize($content);
        }

        $mode = $append === true ? FILE_APPEND | LOCK_EX : LOCK_EX;

        // if the parent directory does not exist, create it
        if (is_dir(dirname($file)) === false) {
            if (Dir::make(dirname($file)) === false) {
                return false;
            }
        }

        if (static::isWritable($file) === false) {
            throw new Exception('The file "' . $file . '" is not writable');
        }

        return file_put_contents($file, $content, $mode) !== false;
    }
}
