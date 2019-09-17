<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Basic pagination handling
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Pagination
{
    /**
     * The current page
     *
     * @var integer
     */
    protected $page;

    /**
     * Total number of items
     *
     * @var integer
     */
    protected $total;

    /**
     * The number of items per page
     *
     * @var integer
     */
    protected $limit;

    /**
     * Creates a new pagination object
     * with the given parameters
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->page($params['page'] ?? 1);
        $this->limit($params['limit'] ?? 20);
        $this->total($params['total'] ?? 0);
    }

    /**
     * Creates a pagination instance for the given
     * collection with a flexible argument api
     *
     * @param \Kirby\Toolkit\Collection $collection
     * @param mixed ...$arguments
     * @return self
     */
    public static function for(Collection $collection, ...$arguments)
    {
        $a = $arguments[0] ?? null;
        $b = $arguments[1] ?? null;

        $params = [];

        if (is_array($a) === true) {

            /**
             * First argument is an option array
             *
             * $collection->paginate([...])
             */
            $params = $a;
        } elseif (is_int($a) === true && $b === null) {

            /**
             * First argument is the limit
             *
             * $collection->paginate(10)
             */
            $params['limit'] = $a;
        } elseif (is_int($a) === true && is_int($b) === true) {

            /**
             * First argument is the limit,
             * second argument is the page
             *
             * $collection->paginate(10, 2)
             */
            $params['limit'] = $a;
            $params['page']  = $b;
        } elseif (is_int($a) === true && is_array($b) === true) {

            /**
             * First argument is the limit,
             * second argument are options
             *
             * $collection->paginate(10, [...])
             */
            $params = $b;
            $params['limit'] = $a;
        }

        // add the total count from the collection
        $params['total'] = $collection->count();

        // remove null values to make later merges work properly
        $params = array_filter($params);

        // create the pagination instance
        return new static($params);
    }

    /**
     * Getter and setter for the current page
     *
     * @param int|null $page
     * @return int|\Kirby\Toolkit\Pagination
     */
    public function page(int $page = null)
    {
        if ($page === null) {
            if ($this->page > $this->pages()) {
                $this->page = $this->lastPage();
            }

            if ($this->page < 1) {
                $this->page = $this->firstPage();
            }

            return $this->page;
        }

        $this->page = $page;
        return $this;
    }

    /**
     * Getter and setter for the total number of items
     *
     * @param int|null $total
     * @return int|\Kirby\Toolkit\Pagination
     */
    public function total(int $total = null)
    {
        if ($total === null) {
            return $this->total;
        }

        if ($total < 0) {
            throw new Exception('Invalid total number of items: ' . $total);
        }

        $this->total = $total;
        return $this;
    }

    /**
     * Getter and setter for the number of items per page
     *
     * @param int|null $limit
     * @return int|\Kirby\Toolkit\Pagination
     */
    public function limit(int $limit = null)
    {
        if ($limit === null) {
            return $this->limit;
        }

        if ($limit < 1) {
            throw new Exception('Invalid pagination limit: ' . $limit);
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * Returns the index of the first item on the page
     *
     * @return int
     */
    public function start(): int
    {
        $index = $this->page() - 1;

        if ($index < 0) {
            $index = 0;
        }

        return $index * $this->limit() + 1;
    }

    /**
     * Returns the index of the last item on the page
     *
     * @return int
     */
    public function end(): int
    {
        $value = ($this->start() - 1) + $this->limit();

        if ($value <= $this->total()) {
            return $value;
        }

        return $this->total();
    }

    /**
     * Returns the total number of pages
     *
     * @return int
     */
    public function pages(): int
    {
        if ($this->total() === 0) {
            return 0;
        }

        return ceil($this->total() / $this->limit());
    }

    /**
     * Returns the first page
     *
     * @return int
     */
    public function firstPage(): int
    {
        return $this->total() === 0 ? 0 : 1;
    }

    /**
     * Returns the last page
     *
     * @return int
     */
    public function lastPage(): int
    {
        return $this->pages();
    }

    /**
     * Returns the offset (i.e. for db queries)
     *
     * @return int
     */
    public function offset(): int
    {
        return $this->start() - 1;
    }

    /**
     * Checks if the given page exists
     *
     * @param int $page
     * @return boolean
     */
    public function hasPage(int $page): bool
    {
        if ($page <= 0) {
            return false;
        }

        if ($page > $this->pages()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if there are any pages at all
     *
     * @return boolean
     */
    public function hasPages(): bool
    {
        return $this->total() > $this->limit();
    }

    /**
     * Checks if there's a previous page
     *
     * @return boolean
     */
    public function hasPrevPage(): bool
    {
        return $this->page() > 1;
    }

    /**
     * Returns the previous page
     *
     * @return int|null
     */
    public function prevPage()
    {
        return $this->hasPrevPage() ? $this->page() - 1 : null;
    }

    /**
     * Checks if there's a next page
     *
     * @return boolean
     */
    public function hasNextPage(): bool
    {
        return $this->end() < $this->total();
    }

    /**
     * Returns the next page
     *
     * @return int|null
     */
    public function nextPage()
    {
        return $this->hasNextPage() ? $this->page() + 1 : null;
    }

    /**
     * Checks if the current page is the first page
     *
     * @return boolean
     */
    public function isFirstPage(): bool
    {
        return $this->page() === $this->firstPage();
    }

    /**
     * Checks if the current page is the last page
     *
     * @return boolean
     */
    public function isLastPage(): bool
    {
        return $this->page() === $this->lastPage();
    }

    /**
     * Creates a range of page numbers for Google-like pagination
     *
     * @param int $range
     * @return array
     */
    public function range(int $range = 5): array
    {
        $page  = $this->page();
        $pages = $this->pages();
        $start = 1;
        $end   = $pages;

        if ($pages <= $range) {
            return range($start, $end);
        }

        $start = $page - (int)floor($range/2);
        $end   = $page + (int)floor($range/2);

        if ($start <= 0) {
            $end   += abs($start);
            $start  = 1;
        }

        if ($end > $pages) {
            $start -= $end - $pages;
            $end    = $pages;
        }

        return range($start, $end);
    }

    /**
     * Returns the first page of the created range
     *
     * @param int $range
     * @return int
     */
    public function rangeStart(int $range = 5): int
    {
        return $this->range($range)[0];
    }

    /**
     * Returns the last page of the created range
     *
     * @param int $range
     * @return int
     */
    public function rangeEnd(int $range = 5): int
    {
        $range = $this->range($range);
        return array_pop($range);
    }

    /**
     * Returns an array with all properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'page'      => $this->page(),
            'firstPage' => $this->firstPage(),
            'lastPage'  => $this->lastPage(),
            'pages'     => $this->pages(),
            'offset'    => $this->offset(),
            'limit'     => $this->limit(),
            'total'     => $this->total(),
            'start'     => $this->start(),
            'end'       => $this->end(),
        ];
    }
}
