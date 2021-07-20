<?php

namespace Kirby\Panel;

/**
 * The Dropdown response class handles Fiber
 * requests to render the JSON object for
 * dropdown menus
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Dropdown
{
    /**
     * Renders the error dropdown response with provided message
     *
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function error(string $message, int $code = 404)
    {
        return [
            '$dropdown' => [
                'code'  => $code,
                'error' => $message
            ]
        ];
    }

    /**
     * Renders dropdowns
     *
     * @param mixed $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function response($data, array $options = [])
    {
        // handle Kirby exceptions
        if (is_a($data, 'Kirby\Exception\Exception') === true) {
            return static::error($data->getMessage(), $data->getHttpCode());

        // handle exceptions
        } elseif (is_a($data, 'Throwable') === true) {
            return static::error($data->getMessage(), 500);

        // only expect arrays from here on
        } elseif (is_array($data) === false) {
            return static::error('Invalid dropdown response', 500);
        }

        $dropdown = [
            'code'     => 200,
            'path'     => $options['path'] ?? null,
            'referrer' => Panel::referrer(),
            'options'  => $data
        ];

        return Panel::json(['$dropdown' => $dropdown], 200);
    }
}
