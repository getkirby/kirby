<?php

namespace Kirby\Panel;

/**
 * The Search response class handles Fiber
 * requests to render the JSON object for
 * search queries
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Search extends Json
{
    protected static $key = '$search';

    /**
     * @param mixed $data
     * @param array $options
     * @return \Kirby\Http\Response
     */
    public static function response($data, array $options = [])
    {
        if (is_array($data) === true) {
            $data = [
                'results' => $data
            ];
        }

        return parent::response($data, $options);
    }
}
