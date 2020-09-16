<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class BuilderBlocks extends Collection
{
    /**
     * Creates a new Collection with the given objects
     *
     * @param array $objects
     * @param object|null $parent
     */
    public function __construct($objects = [], $parent = null)
    {
        $this->parent = $parent;
        $this->set($objects);
    }

    /**
     * The internal setter for collection items.
     * This makes sure that nothing unexpected ends
     * up in the collection. You can pass arrays or
     * BuilderBlock objects
     *
     * @param string $id
     * @param array|BuilderBlock $props
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function __set(string $id, $props)
    {
        if (is_a($props, 'Kirby\Cms\BuilderBlock') === true) {
            $object = $props;
        } else {
            if (is_array($props) === false) {
                throw new InvalidArgumentException('Invalid builder block');
            }

            $object = new BuilderBlock([
                'content' => $props,
                'id'      => $props['_uid'] ?? $id,
                'parent'  => $this->parent,
                'blocks'  => $this
            ]);
        }

        return parent::__set($object->id(), $object);
    }

    /**
     * Creates the HTML for all blocks
     * in the collection
     *
     * @return string
     */
    public function __toString(): string
    {
        $html = '';
        foreach ($this->data as $block) {
            $html .= (string)$block;
        }
        return $html;
    }

}
