<?php

namespace Kirby\Query;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Helpers;
use Kirby\Toolkit\I18n;

/**
 * The Query class can be used to
 * query arrays and objects, including their
 * methods with a very simple string-based syntax.
 *
 * @package   Kirby Query
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
	/**
	 * Default data entries
	 */
	public static array $entries = [];

	/**
	 * Creates a new Query object
	 */
	public function __construct(
		public string|null $query = null,
		// TODO: remove $data prop in 3.9
		protected array|object|null $data = null
	) {
		if ($query !== null) {
			$this->query = trim($query);
		}

		/** @codeCoverageIgnoreStart */
		if ($data !== null) {
			Helpers::deprecated('The $data prop has been deprecated for initiating the Query class and will be removed in Kirby 3.9. Instead, pass data as parameter to the resolve method: Query::factory($query)->resolve($data)');
		}
		/** @codeCoverageIgnoreEnd */
	}

	/**
	 * Creates a new Query object
	 */
	public static function factory(string $query): static
	{
		return new static(query: $query);
	}

	/**
	 * Returns the query result if anything
	 * can be found, otherwise returns null
	 *
	 * @throws \Kirby\Exception\BadMethodCallException If an invalid method is accessed by the query
	 */
	public function resolve(array|object $data = []): mixed
	{
		if (empty($this->query) === true) {
			return $data;
		}

		// merge data with default entries
		if (is_array($data) === true) {
			$data = array_merge(static::$entries, $data);
		}

		// direct data array access via key
		if (
			is_array($data) === true &&
			array_key_exists($this->query, $data) === true
		) {
			$value = $data[$this->query];

			if ($value instanceof Closure) {
				$value = $value();
			}

			return $value;
		}

		// loop through all segments to resolve query
		$segments = Segments::factory($this->query);
		return $segments->resolve($data);
	}

	/**
	 * @deprecated 3.8.0 Use `Query:factory($query)->resolve($data)` instead
	 * TODO: remove in 3.9
	 * @codeCoverageIgnore
	 */
	public function result()
	{
		Helpers::deprecated('$query->result() has been deprecated and will be removed in Kirby 3.9. Use instead: Query::factory($query)->resolve($data)');
		return $this->resolve($this->data);
	}
}

/**
 * Default entries/functions
 */
Query::$entries['kirby'] = fn () => App::instance();

Query::$entries['t'] = function (
	string $key,
	string|array $fallback = null,
	string $locale = null
): string|null {
	return I18n::translate($key, $fallback, $locale);
};

Query::$entries['cache'] = function (
	string $key,
	Closure $set,
	int $minutes = 1
): string|null {
	return App::instance()->cache('queries')->getOrSet($key, $set, $minutes);
};
