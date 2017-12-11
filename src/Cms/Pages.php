<?php

namespace Kirby\Cms;

use Exception;

/**
 * The Pages collection contains
 * any number and mixture of page objects
 * They don't necessarily have to belong
 * to the same parent in comparison to
 * the Children collection. The Children
 * collection is based on the Pages
 * collection though.
 *
 * Pages collection can be constructed very
 * easily:
 *
 * ```php
 * $collection = new Pages([
 *   new Page(['id' => 'project-a']),
 *   new Page(['id' => 'project-b']),
 *   new Page(['id' => 'project-c']),
 * ]);
 * ```
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Pages extends Collection
{

    /**
     * Only accepts Page objects
     *
     * @var string
     */
    protected static $accept = Page::class;

    /**
     * Initialize the PagesFinder class,
     * which is handling findBy and find
     * methods
     *
     * @return PagesFinder
     */
    protected function finder()
    {
        return new PagesFinder($this);
    }

    /**
     * Returns all invisible pages in the collection
     *
     * @return self
     */
    public function invisible(): self
    {
        return $this->filterBy('isVisible', '==', false);
    }

    /**
     * Returns all visible pages in the collection
     *
     * @return self
     */
    public function visible(): self
    {
        return $this->filterBy('isVisible', '==', true);
    }

}
