<?php

namespace Kirby\Panel;

/**
 * The Dialog response class handles Fiber
 * requests to render the JSON object for
 * Panel dialogs
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Dialog extends Json
{
    protected static $key = '$dialog';

    /**
     * Renders dialogs
     *
     * @param mixed $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function response($data, array $options = [])
    {
        // interpret true as success
        if ($data === true) {
            $data = [
                'code' => 200
            ];
        }

        return parent::response($data, $options);
    }
}
