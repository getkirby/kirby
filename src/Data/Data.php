<?php

namespace Kirby\Data;

use Exception;
use Kirby\Toolkit\F;

/**
 * The `Data` class provides readers and
 * writers for data. The class comes with
 * four handlers for `json`, `php`, `txt`
 * and `yaml` encoded data, but can be
 * extended and customized.
 *
 * The read and write methods automatically
 * detect which data handler to use in order
 * to correctly encode and decode passed data.
 *
 * @package   Kirby
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Data
{
    /**
     * Handler Type Aliases
     *
     * @var array
     */
    public static $aliases = [
        'md'    => 'txt',
        'mdown' => 'txt',
        'yml'   => 'yaml',
    ];

    /**
     * All registered handlers
     *
     * @var array
     */
    public static $handlers = [
        'json' => 'Kirby\Data\Json',
        'php'  => 'Kirby\Data\PHP',
        'txt'  => 'Kirby\Data\Txt',
        'yaml' => 'Kirby\Data\Yaml',
    ];

    /**
     * Handler getter
     *
     * @param string $type
     * @return \Kirby\Data\Handler
     */
    public static function handler(string $type)
    {
        // normalize the type
        $type = strtolower($type);

        // find a handler or alias
        $handler = static::$handlers[$type] ??
                   static::$handlers[static::$aliases[$type] ?? null] ??
                   null;

        if (class_exists($handler)) {
            return new $handler();
        }

        throw new Exception('Missing handler for type: "' . $type . '"');
    }

    /**
     * Decodes data with the specified handler
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
     * Encodes data with the specified handler
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
     * Reads data from a file;
     * the data handler is automatically chosen by
     * the extension if not specified
     *
     * @param string $file
     * @param string $type
     * @return array
     */
    public static function read(string $file, string $type = null): array
    {
        return static::handler($type ?? F::extension($file))->read($file);
    }

    /**
     * Writes data to a file;
     * the data handler is automatically chosen by
     * the extension if not specified
     *
     * @param string $file
     * @param array $data
     * @param string $type
     * @return bool
     */
    public static function write(string $file = null, array $data = [], string $type = null): bool
    {
        return static::handler($type ?? F::extension($file))->write($file, $data);
    }
}
