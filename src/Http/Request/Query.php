<?php

namespace Kirby\Http\Request;

/**
 * The Query class helps to
 * parse and inspect URL queries
 * as part of the Request object
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
	use Data;

	/**
	 * The Query data array
	 *
	 * @var array|null
	 */
	protected $data;

	/**
	 * Creates a new Query object.
	 * The passed data can be an array
	 * or a parsable query string. If
	 * null is passed, the current Query
	 * will be taken from $_GET
	 *
	 * @param array|string|null $data
	 */
	public function __construct($data = null)
	{
		if ($data === null) {
			$this->data = $_GET;
		} elseif (is_array($data)) {
			$this->data = $data;
		} else {
			parse_str($data, $parsed);
			$this->data = $parsed;
		}
	}

	/**
	 * Returns the Query data as array
	 *
	 * @return array
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Returns `true` if the request doesn't contain query variables
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->data) === true;
	}

	/**
	 * Returns `true` if the request contains query variables
	 *
	 * @return bool
	 */
	public function isNotEmpty(): bool
	{
		return empty($this->data) === false;
	}

	/**
	 * Converts the query data array
	 * back to a query string
	 *
	 * @return string
	 */
	public function toString(): string
	{
		return http_build_query($this->data());
	}

	/**
	 * Magic string converter
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}
}
