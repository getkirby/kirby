<?php

namespace Kirby\Collection\Traits;

use Kirby\Pagination\Pagination;

/**
 * Paginator
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
trait Paginator
{

    /**
     * Pagination object
     * @var Pagination
     */
    protected $pagination;

    /**
     * Returns a slice of the object
     *
     * @param  int        $offset  The optional index to start the slice from
     * @param  int        $limit   The optional number of elements to return
     * @return Collection
     */
    public function slice(int $offset = 0, int $limit = null): self
    {
        if ($offset === 0 && $limit === null) {
            return $this;
        }

        return $this->clone()->data(array_slice($this->data, $offset, $limit));
    }

    /**
     * Returns a new object with a limited number of elements
     *
     * @param  int        $limit  The number of elements to return
     * @return Collection
     */
    public function limit(int $limit): self
    {
        return $this->slice(0, $limit);
    }

    /**
     * Returns a new object starting from the given offset
     *
     * @param  int        $offset  The index to start from
     * @return Collection
     */
    public function offset(int $offset): self
    {
        return $this->slice($offset);
    }

    /**
     * Add pagination
     *
     * @param  int        $limit  number of items per page
     * @param  int        $page   optional page number to return
     * @return Collection         a sliced set of data
     */
    public function paginate(...$arguments)
    {

        if (is_array($arguments[0])) {
            $options = $arguments[0];
        } else {
            $options = [
                'limit' => $arguments[0],
                'page'  => $arguments[1] ?? 1,
            ];
        }

        $pagination = new Pagination([
            'total' => $this->count(),
            'limit' => $options['limit'] ?? 10,
            'page'  => $options['page'] ?? 1
        ]);

        // add the pagination object before
        // the collection gets cloned to keep it protected
        $this->pagination = $pagination;

        // slice and clone the collection according to the pagination
        return $this->slice($pagination->offset(), $pagination->limit());
    }

    /**
     * Get the previously added pagination object
     *
     * @return Pagination
     */
    public function pagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     * Creates chunks of the same size
     * The last chunk may be smaller
     *
     * @param  int        $size  Number of items per chunk
     * @return Collection        A new collection with an item for each chunk and
     *                           a sub collection in each chunk
     */
    public function chunk(int $size): self
    {
        // create a multidimensional array that is chunked with the given
        // chunk size keep keys of the items
        $chunks = array_chunk($this->data, $size, true);

        // convert each chunk to a subcollection
        $collection = [];

        foreach ($chunks as $items) {
            // we clone $this instead of creating a new object because
            // different objects may have different constructors
            $collection[]  = $this->clone()->data($items);
        }

        // convert the array of chunks to a collection
        return $this->clone()->data($collection);
    }

}
