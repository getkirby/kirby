<?php

namespace Kirby\Cms;

use Closure;

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
