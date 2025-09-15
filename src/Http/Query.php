<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;
use Stringable;

/**
 * A wrapper around a URL query string
 * that converts it into a Kirby Obj for easier
 * access of each query attribute.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query extends Obj implements Stringable
{
	public function __construct(string|array|null $query)
	{
		if (is_string($query) === true) {
			parse_str(ltrim($query, '?'), $query);
		}

		parent::__construct($query ?? []);
	}

	public function isEmpty(): bool
	{
		return (array)$this === [];
	}

	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Merges the current query with the given query
	 * @since 5.1.0
	 *
	 * @return $this
	 */
	public function merge(string|array|null $query): static
	{
		$query = new static($query);

		foreach ($query as $key => $value) {
			$this->$key = $value;
		}

		return $this;
	}

	public function toString(bool $questionMark = false): string
	{
		$query = http_build_query($this, '', '&', PHP_QUERY_RFC3986);

		if ($query === '') {
			return '';
		}

		if ($questionMark === true) {
			$query = '?' . $query;
		}

		return $query;
	}


	public function __toString(): string
	{
		return $this->toString();
	}
}
