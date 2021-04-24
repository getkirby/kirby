<?php

namespace Kirby\Sane;

use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\F;

/**
 * The `Sane` class validates that files
 * don't contain potentially harmful contents.
 * The class comes with handlers for `svg`, `svgz` and `xml`
 * files for now, but can be extended and customized.
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Sane
{
    /**
     * Handler Type Aliases
     *
     * @var array
     */
    public static $aliases = [
        'image/svg+xml'   => 'svg',
        'application/xml' => 'xml',
        'text/xml'        => 'xml',
    ];

    /**
     * All registered handlers
     *
     * @var array
     */
    public static $handlers = [
        'svg'  => 'Kirby\Sane\Svg',
        'svgz' => 'Kirby\Sane\Svgz',
        'xml'  => 'Kirby\Sane\Xml',
    ];

    /**
     * Handler getter
     *
     * @param string $type
     * @param bool $lazy If set to `true`, `null` is returned for undefined handlers
     * @return \Kirby\Sane\Handler|null
     *
     * @throws \Kirby\Exception\NotFoundException If no handler was found and `$lazy` was set to `false`
     */
    public static function handler(string $type, bool $lazy = false)
    {
        // normalize the type
        $type = mb_strtolower($type);

        // find a handler or alias
        $handler = static::$handlers[$type] ??
                   static::$handlers[static::$aliases[$type] ?? null] ??
                   null;

        if (empty($handler) === false && class_exists($handler) === true) {
            return new $handler();
        }

        if ($lazy === true) {
            return null;
        }

        throw new NotFoundException('Missing handler for type: "' . $type . '"');
    }

    /**
     * Validates file contents with the specified handler
     *
     * @param mixed $string
     * @param string $type
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\NotFoundException If the handler was not found
     * @throws \Kirby\Exception\Exception On other errors
     */
    public static function validate(string $string, string $type): void
    {
        static::handler($type)->validate($string);
    }

    /**
     * Validates the contents of a file;
     * the sane handlers are automatically chosen by
     * the extension and MIME type if not specified
     *
     * @param string $file
     * @param string|bool $typeLazy Explicit handler type string,
     *                              `true` for lazy autodetection or
     *                              `false` for normal autodetection
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\NotFoundException If the handler was not found
     * @throws \Kirby\Exception\Exception On other errors
     */
    public static function validateFile(string $file, $typeLazy = false): void
    {
        if (is_string($typeLazy) === true) {
            static::handler($typeLazy)->validateFile($file);
            return;
        }

        $options = [F::extension($file), F::mime($file)];

        // execute all handlers, but each class only once for performance;
        // filter out all empty options
        $usedHandlers = [];
        foreach (array_filter($options) as $option) {
            $handler      = static::handler($option, $typeLazy === true);
            $handlerClass = $handler ? get_class($handler) : null;

            if ($handler && in_array($handlerClass, $usedHandlers) === false) {
                $handler->validateFile($file);

                $usedHandlers[] = $handlerClass;
            }
        }
    }
}
