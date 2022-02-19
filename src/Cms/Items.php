<?php

namespace Kirby\Cms;

use Closure;
use Exception;

/**
 * A collection of items
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Items extends Collection
{
    public const ITEM_CLASS = '\Kirby\Cms\Item';

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Kirby\Cms\ModelWithContent
     */
    protected $parent;

    /**
     * Constructor
     *
     * @param array $objects
     * @param array $options
     */
    public function __construct($objects = [], array $options = [])
    {
        $this->options = $options;
        $this->parent  = $options['parent'] ?? site();

        parent::__construct($objects, $this->parent);
    }

    /**
     * Creates a new item collection from a
     * an array of item props
     *
     * @param array $items
     * @param array $params
     * @return \Kirby\Cms\Items
     */
    public static function factory(array $items = null, array $params = [])
    {
        $options = array_merge([
            'options' => [],
            'parent'  => site(),
        ], $params);

        if (empty($items) === true || is_array($items) === false) {
            return new static();
        }

        if (is_array($options) === false) {
            throw new Exception('Invalid item options');
        }

        // create a new collection of blocks
        $collection = new static([], $options);

        foreach ($items as $params) {
            if (is_array($params) === false) {
                continue;
            }

            $params['options']  = $options['options'];
            $params['parent']   = $options['parent'];
            $params['siblings'] = $collection;
            $class = static::ITEM_CLASS;
            $item  = $class::factory($params);
            $collection->append($item->id(), $item);
        }

        return $collection;
    }

    /**
     * Convert the items to an array
     *
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        return array_values(parent::toArray($map));
    }
}
