<?php

namespace Kirby\Cms;

/**
 * The Nest class converts any array type
 * into a Kirby style collection/object. This
 * can be used make any type of array compatible
 * with Kirby queries.
 *
 * REFACTOR: move this to the toolkit
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Nest
{
    /**
     * @param $data
     * @param null $parent
     * @return mixed
     */
    public static function create($data, $parent = null)
    {
        if (is_scalar($data) === true) {
            return new Field($parent, $data, $data);
        }

        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value) === true) {
                $result[$key] = static::create($value, $parent);
            } elseif (is_scalar($value) === true) {
                $result[$key] = new Field($parent, $key, $value);
            }
        }

        if (is_int(key($data))) {
            return new NestCollection($result);
        } else {
            return new NestObject($result);
        }
    }
}
