<?php

namespace Kirby\Cms;

use Closure;

/**
 * Extension of the Pages collection, with
 * a modified findById method to search for
 * relative IDs starting from the current page.
 */
class Children extends Pages
{

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
        $startAt = $this->parent ? $this->parent->id(): '';
        $page    = $this->get(ltrim($startAt . '/' . $id, '/'));

        if (!$page) {
            $page = $this->findByIdRecursive($id, $startAt);
        }

        return $page;
    }
}
