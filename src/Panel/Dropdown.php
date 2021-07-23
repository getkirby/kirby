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
class Dropdown extends Json
{
    protected static $key = '$dropdown';

    /**
     * Renders dropdowns
     *
     * @param mixed $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function response($data, array $options = [])
    {
        if (is_array($data) === true) {
            $data = [
                'options' => $data
            ];
        }

        return parent::response($data, $options);
    }
}
