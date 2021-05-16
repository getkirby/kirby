<?php

namespace Kirby\Cms;

/**
 * A collection of layouts
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Layouts extends Items
{
    const ITEM_CLASS = '\Kirby\Cms\Layout';

    public static function factory(array $items = null, array $params = [])
    {
        $first = $items[0] ?? [];

        // if there are no wrapping layouts for blocks yet …
        if (array_key_exists('content', $first) === true || array_key_exists('type', $first) === true) {
            $items = [
                [
                    'id'      => uuid(),
                    'columns' => [
                        [
                            'width'  => '1/1',
                            'blocks' => $items
                        ]
                    ]
                ]
            ];
        }

        return parent::factory($items, $params);
    }
}
