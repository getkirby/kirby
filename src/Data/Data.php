<?php

namespace Kirby\Data;

use Exception;
use Kirby\Toolkit\F;

/**
 * The `Data` class provides readers and
 * writers for data. The class comes with
 * three handlers for `json`, `yaml` and
 * `txt` encoded data, but can be extended
 * and customized.
 *
 * The read and write methods automatically
 * detect, which data handler to use in order
 * to correctly encode and decode passed data.
 *
 * @package   Kirby
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Data
{

    /**
     * Handler Type Aliases
     *
     * @var array
     */
    public static $aliases = [
        'yml'   => 'yaml',
        'md'    => 'txt',
        'mdown' => 'txt'
    ];

    /**
     * All registered handlers
     *
     * @var array
     */
    public static $handlers = [
        'json' => 'Kirby\Data\Json',
        'yaml' => 'Kirby\Data\Yaml',
        'txt'  => 'Kirby\Data\Txt'
    ];

    /**
     * Handler getter
     *
     * @param  string          $type
     * @return Handler|null
     */
    public static function handler(string $type)
    {
        // normalize the type
        $type    = strtolower($type);
        $handler = static::$handlers[$type] ?? null;

        if ($handler === null && isset(static::$aliases[$type]) === true) {
            $handler = static::$handlers[static::$aliases[$type]] ?? null;
        }

        if ($handler === null) {
            throw new Exception('Missing Handler for type: "' . $type . '"');
        }

        return new $handler;
    }

    /**
     * Decode data with the specified handler
     *
     * @param string $data
     * @param string $type
     * @return array
     */
    public static function decode(string $data = null, string $type): array
    {
        return static::handler($type)->decode($data);
    }

    /**
     * Encode data with the specified handler
     *
     * @param array $data
     * @param string $type
     * @return string
     */
    public static function encode(array $data = null, string $type): string
    {
        return static::handler($type)->encode($data);
    }

    /**
     * Reads data from a file
     * The data handler is automatically chosen by
     * the extension if not specified.
     *
     * @param  string $file
     * @param  string $type
     * @return array
     */
    public static function read(string $file, string $type = null): array
    {
        return static::handler($type ?? F::extension($file))->read($file);
    }

    /**
     * Writes data to a file.
     * The data handler is automatically chosen by
     * the extension if not specified.
     *
     * @param  string    $file
     * @param  array     $data
     * @param  string    $type
     * @return boolean
     */
    public static function write(string $file = null, array $data = [], string $type = null): bool
    {
        return static::handler($type ?? F::extension($file))->write($file, $data);
    }
}
