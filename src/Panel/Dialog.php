<?php

namespace Kirby\Panel;

/**
 * The Dialog response class handles Fiber
 * requests to render the JSON object for
 * Panel dialogs
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Dialog
{
    /**
     * Renders the error dialog response with provided message
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
     * Renders dialogs
     *
     * @param mixed $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function response($data, array $options = [])
    {
        // handle Kirby exceptions
        if (is_a($data, 'Kirby\Exception\Exception') === true) {
            $data = static::error($data->getMessage(), $data->getHttpCode());

        // handle exceptions
        } elseif (is_a($data, 'Throwable') === true) {
            $data = static::error($data->getMessage(), 500);

        // interpret true as success
        } elseif ($data === true) {
            $data = [
                'code' => 200
            ];

        // only expect arrays from here on
        } elseif (is_array($data) === false) {
            $data = static::error('Invalid dialog response', 500);
        }

        // always inject the response code
        $data['code']     = $data['code']    ?? 200;
        $data['path']     = $options['path'] ?? null;
        $data['referrer'] = Panel::referrer();

        return Panel::json(['$dialog' => $data], $data['code']);
    }
}
