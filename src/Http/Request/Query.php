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
	 */
	protected array|null $data;

	/**
	 * Creates a new Query object.
	 * The passed data can be an array
	 * or a parsable query string. If
	 * null is passed, the current Query
	 * will be taken from $_GET
	 */
	public function __construct(array|string|null $data = null)
	{
		if ($data === null) {
			$this->data = $_GET;
		} elseif (is_array($data) === true) {
			$this->data = $data;
		} else {
			parse_str($data, $parsed);
			$this->data = $parsed;
		}
	}

	/**
	 * Returns the Query data as array
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Returns `true` if the request doesn't contain query variables
	 */
	public function isEmpty(): bool
	{
		return empty($this->data) === true;
	}

	/**
	 * Returns `true` if the request contains query variables
	 */
	public function isNotEmpty(): bool
	{
		return empty($this->data) === false;
	}

	/**
	 * Converts the query data array
	 * back to a query string
	 */
	public function toString(): string
	{
		return http_build_query($this->data());
	}

	/**
	 * Magic string converter
	 */
	public function __toString(): string
	{
		return $this->toString();
	}
}
