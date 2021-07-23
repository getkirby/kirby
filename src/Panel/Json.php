<?php

namespace Kirby\Panel;

/**
 * The Json abstract response class provides
 * acommon framework for Fiber requests
 * to render the JSON object for, e.g.
 * Panel dialogs, dropdowns etc.
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
abstract class Json
{
    protected static $key = '$response';

    /**
     * Renders the error response with the provided message
     *
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function error(string $message, int $code = 404)
    {
        return [
            'code'  => $code,
            'error' => $message
        ];
    }

    /**
     * Prepares the JSON response for the Panel
     *
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public static function response($data, array $options = [])
    {
        // handle Kirby exceptions
        if (is_a($data, 'Kirby\Exception\Exception') === true) {
            $data = static::error($data->getMessage(), $data->getHttpCode());

        // handle exceptions
        } elseif (is_a($data, 'Throwable') === true) {
            $data = static::error($data->getMessage(), 500);

        // only expect arrays from here on
        } elseif (is_array($data) === false) {
            $data = static::error('Invalid response', 500);
        }

        // always inject the response code
        $data['code']     = $data['code']    ?? 200;
        $data['path']     = $options['path'] ?? null;
        $data['referrer'] = Panel::referrer();

        return Panel::json([static::$key => $data], $data['code']);
    }
}
