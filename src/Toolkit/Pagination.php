<?php

namespace Kirby\Toolkit;

use Kirby\Exception\ErrorPageException;
use Kirby\Exception\Exception;

/**
 * Basic pagination handling
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Pagination
{
	/**
	 * The current page
	 */
	protected int $page = 1;

	/**
	 * Total number of items
	 */
	protected int $total = 0;

	/**
	 * The number of items per page
	 */
	protected int $limit = 20;

	/**
	 * Whether validation of the pagination page
	 * is enabled; will throw Exceptions if true
	 */
	public static bool $validate = true;

	/**
	 * Creates a new pagination object
	 * with the given parameters
	 */
	public function __construct(array $props = [])
	{
		$this->setLimit($props['limit'] ?? 20);
		$this->setPage($props['page'] ?? null);
		$this->setTotal($props['total'] ?? 0);

		// ensure that page is set to something, otherwise
		// generate "default page" based on other params
		$this->page ??= $this->firstPage();

		// allow a page value of 1 even if there are no pages;
		// otherwise the exception will get thrown for this pretty common case
		$min = $this->firstPage();
		$max = $this->pages();
		if ($this->page === 1 && $max === 0) {
			$this->page = 0;
		}

		// validate page based on all params if validation is enabled,
		// otherwise limit the page number to the bounds
		if ($this->page < $min || $this->page > $max) {
			if (static::$validate === true) {
				throw new ErrorPageException('Pagination page ' . $this->page . ' does not exist, expected ' . $min . '-' . $max);
			}

			$this->page = max(min($this->page, $max), $min);
		}
	}

	/**
	 * Creates a new instance while
	 * merging initial and new properties
	 */
	public function clone(array $props = []): static
	{
		return new static(array_replace_recursive([
			'page'  => $this->page,
			'limit' => $this->limit,
			'total' => $this->total
		], $props));
	}

	/**
	 * Creates a pagination instance for the given
	 * collection with a flexible argument api
	 */
	public static function for(Collection $collection, ...$arguments): static
	{
		$a = $arguments[0] ?? null;
		$b = $arguments[1] ?? null;

		$params = [];

		// First argument is a pagination object
		if ($a instanceof static) {
			return $a;
		}

		if (is_array($a) === true) {
			// First argument is an option array
			// $collection->paginate([...])
			$params = $a;
		} elseif (is_int($a) === true && $b === null) {
			// First argument is the limit
			// $collection->paginate(10)
			$params['limit'] = $a;
		} elseif (is_int($a) === true && is_int($b) === true) {
			// First argument is the limit, second argument is the page
			// $collection->paginate(10, 2)
			$params['limit'] = $a;
			$params['page']  = $b;
		} elseif (is_int($a) === true && is_array($b) === true) {
			// First argument is the limit, second argument are options
			// $collection->paginate(10, [...])
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
	 * Getter for the current page
	 */
	public function page(): int
	{
		return $this->page;
	}

	/**
	 * Getter for the total number of items
	 */
	public function total(): int
	{
		return $this->total;
	}

	/**
	 * Getter for the number of items per page
	 */
	public function limit(): int
	{
		return $this->limit;
	}

	/**
	 * Returns the index of the first item on the page
	 */
	public function start(): int
	{
		$index = max(0, $this->page() - 1);
		return $index * $this->limit() + 1;
	}

	/**
	 * Returns the index of the last item on the page
	 */
	public function end(): int
	{
		$value = min($this->total(), ($this->start() - 1) + $this->limit());
		return $value;
	}

	/**
	 * Returns the total number of pages
	 */
	public function pages(): int
	{
		if ($this->total() === 0) {
			return 0;
		}

		return (int)ceil($this->total() / $this->limit());
	}

	/**
	 * Returns the first page
	 */
	public function firstPage(): int
	{
		return $this->total() === 0 ? 0 : 1;
	}

	/**
	 * Returns the last page
	 */
	public function lastPage(): int
	{
		return $this->pages();
	}

	/**
	 * Returns the offset (i.e. for db queries)
	 */
	public function offset(): int
	{
		return $this->start() - 1;
	}

	/**
	 * Checks if the given page exists
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
	 */
	public function hasPages(): bool
	{
		return $this->total() > $this->limit();
	}

	/**
	 * Checks if there's a previous page
	 */
	public function hasPrevPage(): bool
	{
		return $this->page() > 1;
	}

	/**
	 * Returns the previous page
	 */
	public function prevPage(): int|null
	{
		return $this->hasPrevPage() ? $this->page() - 1 : null;
	}

	/**
	 * Checks if there's a next page
	 */
	public function hasNextPage(): bool
	{
		return $this->end() < $this->total();
	}

	/**
	 * Returns the next page
	 */
	public function nextPage(): int|null
	{
		return $this->hasNextPage() ? $this->page() + 1 : null;
	}

	/**
	 * Checks if the current page is the first page
	 */
	public function isFirstPage(): bool
	{
		return $this->page() === $this->firstPage();
	}

	/**
	 * Checks if the current page is the last page
	 */
	public function isLastPage(): bool
	{
		return $this->page() === $this->lastPage();
	}

	/**
	 * Creates a range of page numbers for Google-like pagination
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

		$middle = (int)floor($range / 2);
		$start  = $page - $middle + ($range % 2 === 0);
		$end    = $start + $range - 1;

		if ($start <= 0) {
			$end   = $range;
			$start = 1;
		}

		if ($end > $pages) {
			$start = $pages - $range + 1;
			$end   = $pages;
		}

		return range($start, $end);
	}

	/**
	 * Returns the first page of the created range
	 */
	public function rangeStart(int $range = 5): int
	{
		return $this->range($range)[0];
	}

	/**
	 * Returns the last page of the created range
	 */
	public function rangeEnd(int $range = 5): int
	{
		$range = $this->range($range);
		return array_pop($range);
	}

	/**
	 * Sets the number of items per page
	 *
	 * @return $this
	 */
	protected function setLimit(int $limit = 20): static
	{
		if ($limit < 1) {
			throw new Exception('Invalid pagination limit: ' . $limit);
		}

		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the total number of items
	 *
	 * @return $this
	 */
	protected function setTotal(int $total = 0): static
	{
		if ($total < 0) {
			throw new Exception('Invalid total number of items: ' . $total);
		}

		$this->total = $total;
		return $this;
	}

	/**
	 * Sets the current page
	 *
	 * @param int|string|null $page Int or int in string form;
	 *                              automatically determined if null
	 * @return $this
	 */
	protected function setPage(int|string|null $page = null): static
	{
		// if $page is null, it is set to a default in the setProperties() method
		if ($page !== null) {
			if (is_numeric($page) !== true || $page < 0) {
				throw new Exception('Invalid page number: ' . $page);
			}

			$this->page = (int)$page;
		}

		return $this;
	}

	/**
	 * Returns an array with all properties
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
