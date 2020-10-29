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
    /**
     * @var \Kirby\Cms\LayoutColumns
     */
    protected $columns;

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
            'columns' => $this->columns()->toArray(),
            'id'      => $this->id(),
        ];
    }
}
