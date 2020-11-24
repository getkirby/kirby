<?php

namespace Kirby\Cms;

/**
 * Represents a single Layout with
 * multiple columns
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Layout extends Item
{
    const ITEMS_CLASS = '\Kirby\Cms\Layouts';

    /**
     * @var \Kirby\Cms\Content
     */
    protected $attrs;

    /**
     * @var \Kirby\Cms\LayoutColumns
     */
    protected $columns;

    /**
     * Proxy for attrs
     *
     * @param string $method
     * @param array $args
     * @return \Kirby\Cms\Field
     */
    public function __call(string $method, array $args = [])
    {
        return $this->attrs()->get($method);
    }

    /**
     * Creates a new Layout object
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);

        $this->columns = LayoutColumns::factory($params['columns'] ?? [], [
            'parent' => $this->parent
        ]);

        // create the attrs object
        $this->attrs = new Content($params['attrs'] ?? [], $this->parent);
    }

    /**
     * Returns the attrs object
     *
     * @return \Kirby\Cms\Content
     */
    public function attrs()
    {
        return $this->attrs;
    }

    /**
     * Returns the columns in this layout
     *
     * @return \Kirby\Cms\LayoutColumns
     */
    public function columns()
    {
        return $this->columns;
    }

    /**
     * The result is being sent to the editor
     * via the API in the panel
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'attrs'   => $this->attrs()->toArray(),
            'columns' => $this->columns()->toArray(),
            'id'      => $this->id(),
        ];
    }
}
