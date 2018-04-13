<?php

namespace Kirby\Cms;

use Kirby\Collection\Finder;

/**
 * The FilesFinder extends
 * the Collection Finder to enable starting file searches
 * by id at a deeper level and still return valid
 * file objects. The parent page must be passed as second
 * argument to the Files Collection constructor to get
 * this right. Afterwards files will be found
 * starting from the id of the parent page.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class FilesFinder extends Finder
{

    /**
     * The id of the parent page to start at
     *
     * @var string
     */
    protected $startAt;

    /**
     * Creates a new FilesFinder instance
     *
     * @param Files $collection
     * @param string $startAt
     */
    public function __construct($collection, $startAt)
    {
        $this->startAt    = $startAt;
        $this->collection = $collection;
    }

    /**
     * Tries to find a file by id/filename
     *
     * @param string $id
     * @return File|null
     */
    public function findById($id)
    {
        return $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));
    }

    /**
     * Alias for FilesFinder::findById() which is
     * used internally in the Files collection to
     * map the get method correctly.
     *
     * @param string $key
     * @return File|null
     */
    public function findByKey($key)
    {
        return $this->findById($key);
    }
}
