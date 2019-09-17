<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

/**
 * NestObject
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class NestObject extends Obj
{
    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ((array)$this as $key => $value) {
            if (is_a($value, 'Kirby\Cms\Field') === true) {
                $result[$key] = $value->value();
                continue;
            }

            if (is_object($value) === true && method_exists($value, 'toArray')) {
                $result[$key] = $value->toArray();
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
