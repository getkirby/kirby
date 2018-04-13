<?php

namespace Kirby\Cms;

/**
 * The ChildrenFinder extends
 * the PagesFinder to enable starting page searches
 * by id at a deeper level and still return valid
 * pages. The parent page must be passed as second
 * argument to the Collection constructor to get
 * this right. Afterwards Children will be found
 * starting from the id of the parent page.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class ChildrenFinder extends PagesFinder
{

    /**
     * The id to start searches from
     *
     * @var string
     */
    protected $startAt;

    /**
     * Creates a new ChildrenFinder instance
     *
     * @param Pages $collection
     * @param string $startAt
     */
    public function __construct($collection, $startAt)
    {
        $this->startAt    = $startAt;
        $this->collection = $collection;
    }

    /**
     * Finds pages by the id starting at the parent id.
     * This will also search recursively to find pages
     * deep down the content structure
     *
     * @param string $id
     * @return Page|null
     */
    public function findById($id)
    {
        $page = $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));

        if (!$page) {
            $page = $this->findByIdRecursive($id, $this->startAt);
        }

        return $page;
    }
}
