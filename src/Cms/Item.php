<?php

namespace Kirby\Cms;

/**
 * The Item class is the foundation
 * for every object in context with
 * other objects. I.e.
 *
 * - a Block in a collection of Blocks
 * - a Layout in a collection of Layouts
 * - a Column in a collection of Columns
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Item
{
    const ITEMS_CLASS = '\Kirby\Cms\Items';

    use HasSiblings;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var \Kirby\Cms\Items
     */
    protected $siblings;

    /**
     * Creates a new item
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $siblingsClass = static::ITEMS_CLASS;

        $this->id       = $params['id']       ?? uuid();
        $this->params   = $params;
        $this->parent   = $params['parent']   ?? site();
        $this->siblings = $params['siblings'] ?? new $siblingsClass();
    }

    /**
     * Static Item factory
     *
     * @param array $params
     * @return \Kirby\Cms\Item
     */
    public static function factory(array $params)
    {
        return new static($params);
    }

    /**
     * Returns the unique item id (UUID v4)
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Compares the item to another one
     *
     * @param \Kirby\Cms\Item $item
     * @return bool
     */
    public function is(Item $item): bool
    {
        return $this->id() === $item->id();
    }

    /**
     * Returns the Kirby instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->parent()->kirby();
    }

    /**
     * Returns the parent model
     *
     * @return \Kirby\Cms\Page | \Kirby\Cms\Site | \Kirby\Cms\File | \Kirby\Cms\User
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Returns the sibling collection
     * This is required by the HasSiblings trait
     *
     * @return \Kirby\Editor\Blocks
     */
    protected function siblingsCollection()
    {
        return $this->siblings;
    }

    /**
     * Converts the item to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
        ];
    }
}
