<?php

namespace Kirby\Http;

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
class Query extends Identifiers
{
	public function __construct(string|array|null $query)
	{
		if (is_string($query) === true) {
			parse_str(ltrim($query, '?'), $query);
		}

		parent::__construct($query);
	}

	public function toString(bool $questionMark = false): string
	{
		$query = http_build_query($this, '', '&', PHP_QUERY_RFC3986);

		if (empty($query) === true) {
			return '';
		}

		if ($questionMark === true) {
			$query = '?' . $query;
		}

		return $query;
	}
}
