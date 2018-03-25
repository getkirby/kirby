<?php

namespace Kirby\Data;

use Exception;

use Kirby\FileSystem\File;

/**
 * Universal Data writer and reader class.
 *
 * The read and write methods automatically
 * detect, which data handler to use in order
 * to correctly encode and decode passed data.
 *
 * Data Handlers for the class can be
 * extended and customized.
 *
 * @package   Kirby
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright 2012 Bastian Allgeier
 * @license   MIT
 */
class Data
{

    /**
     * Handler Type Aliases
     *
     * @var array
     */
    protected static $handlerAliases = [
        'yml'   => 'yaml',
        'md'    => 'txt',
        'mdown' => 'txt'
    ];

    /**
     * All registered handlers
     *
     * @var array
     */
    protected static $handlers = [
        'json' => 'Kirby\Data\Handler\Json',
        'yaml' => 'Kirby\Data\Handler\Yaml',
        'txt'  => 'Kirby\Data\Handler\Txt',
        'php'  => 'Kirby\Data\Handler\Php'
    ];

    /**
     * Setter and getter for handlers
     *
     * @param  string          $type
     * @param  string|null     $className
     * @return Handler|string
     */
    public static function handler(string $type, string $className = null)
    {
        // normalize the type
        $type = strtolower($type);

        if ($className === null) {
            return static::getHandler($type);
        }

        return static::setHandler($type, $className);
    }

    /**
     * Helper for getting handlers
     *
     * @param  string   $type
     * @return Handler
     */
    protected static function getHandler(string $type): Handler
    {
        $handler = static::$handlers[$type] ?? null;

        if ($handler === null && isset(static::$handlerAliases[$type]) === true) {
            $handler = static::$handlers[static::$handlerAliases[$type]] ?? null;
        }

        if ($handler === null) {
            throw new Exception('Missing Handler for type: "' . $type . '"');
        }

        return new $handler;
    }

    /**
     * Helper for setting handlers
     *
     * @param  string   $type
     * @param  string   $className
     * @return string
     */
    protected static function setHandler(string $type, string $className): string
    {
        if (is_subclass_of($className, Handler::class) === false) {
            throw new Exception($className . ' must extend Kirby\Data\Handler');
        }

        return static::$handlers[$type] = $className;
    }

    /**
     * Reads data from a file
     * The data handler is automatically chosen by
     * the extension if not specified.
     *
     * @param  string     $file
     * @param  string     $type
     * @return array/null
     */
    public static function read(string $file, string $type = null): ?array
    {
        $file = new File($file);
        $type = $type ?? $file->extension();

        if ($file->exists() === false) {
            return [];
        }

        return static::handler($type)->decode($file->read());
    }

    /**
     * Writes data to a file.
     * The data handler is automatically chosen by
     * the extension if not specified.
     *
     * @param  array    $data
     * @return boolean
     */
    public static function write(string $file, array $data = [], string $type = null): bool
    {
        $file = new File($file);
        $type = $type ?? $file->extension();

        return $file->write(static::handler($type)->encode($data));
    }
}
